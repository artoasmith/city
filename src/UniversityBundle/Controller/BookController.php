<?php

namespace UniversityBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use UniversityBundle\Form\Type\BookType;
use UniversityBundle\Form\Type\BookFileType;
use UniversityBundle\Entity\BookSection;
use UniversityBundle\Entity\Book;
use ApiErrorBundle\Entity\Error;
use Symfony\Component\HttpFoundation\Request;
use FileBundle\Controller\DefaultController as FileController;
use FileBundle\Entity\File;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle;
use ApiBundle\Controller\DefaultController as ApiController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class BookController extends ApiController
{
    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Create book element",
     *  input="UniversityBundle\Form\Type\BookType"
     * )
     * @Annotations\Post("/api/uniBooks");
     * @Security("is_granted('ROLE_UNI_BOOK_CREATE')")
     */
    public function postBook(Request $request)
    {
        $form = $this->createForm(BookType::class,new Book())
                     ->handleRequest($request);
        /**
         * @var Book $Book
         */
        $Book = $form->getData();

        $Book->setSections($this->checkSectionArray($Book->getSections(),'UniversityBundle:BookSection'));

        $errors = $this->get('validator')->validate($Book);
        if (count($errors) > 0)
            return $this->view(['error'=>$errors],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $picFiles = false;
        if($Book->getPictureFile()){
            try{
                $picFiles = FileController::upload($Book->getPictureFile(),Book::DEF_PICTURE_FOLDER,File::PIC_TYPE);
            } catch (FileException $e){
                return $this->view(['error'=>[["property_path"=>'pictureFile','message'=>$e->getMessage()]]],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');
            }
        }
        $docFile = false;
        if($Book->getDocumentFile()){
            try{
                $docFile = FileController::upload($Book->getDocumentFile(),Book::DEF_FILE_FOLDER,File::PDF_TYPE);
            } catch (FileException $e){
                if($picFiles){
                    $picFiles = array_shift($picFiles);
                    $picFiles->deleteFile();
                }
                return $this->view(['error'=>[["property_path"=>'documentFile','message'=>$e->getMessage()]]],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');
            }
        }

        $manager = $this->getDoctrine()->getManager();
        if($picFiles) {
            $Book->setPicture(array_shift($picFiles));
            $manager->persist($Book->getPicture());
        }

        if($docFile) {
            $Book->setDocument(array_shift($docFile));
            $manager->persist($Book->getDocument());
        }

        $manager->persist($Book);
        $manager->flush();

        if($Book->getTags())
            $this->tagsManipulator('add','UniversityBundle:Book',$Book->getTags());

        return $this->view([BookType::name=>$Book],Error::SUCCESS_POST_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Update book element",
     *  input="UniversityBundle\Form\Type\BookType"
     * )
     * @Annotations\Put("/api/uniBooks/{id}");
     * @Security("is_granted('ROLE_UNI_BOOK_UPDATE')")
     */
    public function putBook(Request $request,$id=0)
    {
        /**
         * @var Book $book
         */
        $book = $this->getDoctrine()->getRepository('UniversityBundle:Book')->find($id);
        if(!$book)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $oldTags = $book->getTags();
        $form = $this->createForm(BookType::class,$book,array('method' => 'PUT'))
            ->handleRequest($request);
        $book = $form->getData();
        $book->setSections($this->checkSectionArray($book->getSections(),'UniversityBundle:BookSection'));
        $errors = $this->get('validator')->validate($book);
        if (count($errors) > 0)
            return $this->view(['error'=>$errors],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($book);
        $manager->flush();

        $needRem = array_diff($oldTags,$book->getTags());
        if($needRem)
            $this->tagsManipulator('remove','UniversityBundle:Book',$needRem);

        $needAdd = array_diff($book->getTags(),$oldTags);
        if($needAdd)
            $this->tagsManipulator('add','UniversityBundle:Book',$needAdd);

        return $this->view([BookType::name=>$book],Error::SUCCESS_PUT_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Delete book element"
     * )
     * @Annotations\Delete("/api/uniBooks/{id}");
     * @Security("is_granted('ROLE_UNI_BOOK_DELETE')")
     */
    public function deleteBook(Request $request,$id=0)
    {
        /**
         * @var Book $book
         */
        $book = $this->getDoctrine()->getRepository('UniversityBundle:Book')->find($id);
        if(!$book)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $manager = $this->getDoctrine()->getManager();

        if($book->getPicture()){
            $manager->remove($book->getPicture()->deleteFile());
        }

        $manager->remove($book);
        $manager->flush();
        $this->tagsManipulator('remove','UniversityBundle:Book',$book->getTags());
        return $this->view([BookType::name=>$book],Error::SUCCESS_DELETE_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Get book elements"
     * )
     * @Annotations\Get("/api/uniBooks");
     * @Annotations\QueryParam(name="book[id]", description="element object")
     * @Annotations\QueryParam(name="book[title]", description="element title")
     * @Annotations\QueryParam(name="book[author]", description="element date")
     * @Annotations\QueryParam(name="book[description]", description="element description")
     * @Annotations\QueryParam(name="book[tags]", description="element tags")
     * @Annotations\QueryParam(name="book[sections]", description="element sections")
     * @Annotations\QueryParam(name="_sort", default={"id":"ASC"})
     * @Annotations\QueryParam(name="_limit",  requirements="\d+", nullable=true, strict=true)
     * @Annotations\QueryParam(name="_offset", requirements="\d+", nullable=true, strict=true)
     */
    public function getBookList(Request $request)
    {
        $arr = $request->query->all();
        return $this->view([BookType::names=>$this->matching('book','UniversityBundle:Book', $arr)],Error::SUCCESS_GET_CODE)
            ->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Get book element"
     * )
     * @Annotations\Get("/api/uniBooks/{id}");
     */
    public function getBooks(Request $request,$id=0)
    {
        /**
         * @var Book $book
         */
        $book = $this->getDoctrine()->getRepository('UniversityBundle:Book')->find($id);
        if(!$book)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        return $this->view([BookType::name=>$book],Error::SUCCESS_GET_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Delete book's element picture"
     * )
     * @Annotations\Delete("/api/uniBooks/{id}/files/{file_id}");
     * @Security("is_granted('ROLE_UNI_BOOK_UPDATE')")
     */
    public function deleteBooksPicture(Request $request,$id=0,$file_id=0)
    {
        /**
         * @var Book $Book
         */
        $Book = $this->getDoctrine()->getRepository('UniversityBundle:Book')->find($id);
        if(!$Book)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $file = false;
        if($Book->getPicture() && $Book->getPicture()->getId() == $file_id)
            $file = $Book->getPicture();

        if($Book->getDocument() && $Book->getDocument()->getId() == $file_id)
            $file = $Book->getDocument();

        if(!$file)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($file->deleteFile());
        $manager->flush();

        return $this->view([BookType::name=>$Book],Error::SUCCESS_DELETE_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Update book element file",
     *  input="UniversityBundle\Form\Type\EventPictureType"
     * )
     * @Annotations\Post("/api/uniBooks/{id}/files");
     * @Security("is_granted('ROLE_UNI_EVENT_UPDATE')")
     */
    public function postEventsPicture(Request $request,$id=0)
    {
        /**
         * @var Book $Book
         */
        $Book = $this->getDoctrine()->getRepository('UniversityBundle:Book')->find($id);
        if(!$Book)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $form = $this->createForm(BookFileType::class,$Book)
                     ->handleRequest($request);
        $Book = $form->getData();
        if(!$Book->getPictureFile() && !$Book->getDocumentFile())
            return $this->view(['error'=>[
                ["property_path"=>'pictureFile','message'=>'Значение не может быть пустым'],
                ["property_path"=>'documentFile','message'=>'Значение не может быть пустым']
            ]],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        //check files
        $pics = false;
        if($Book->getPictureFile()) {
            try {
                $pics = FileController::upload($Book->getPictureFile(), Book::DEF_PICTURE_FOLDER, File::PIC_TYPE);
            } catch (FileException $e) {
                return $this->view(['error' => [["property_path" => 'pictureFile', 'message' => $e->getMessage()]]], Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');
            }
        }

        $docFile = false;
        if($Book->getDocumentFile()){
            try{
                $docFile = FileController::upload($Book->getDocumentFile(),Book::DEF_FILE_FOLDER,File::PDF_TYPE);
            } catch (FileException $e){
                if($pics){
                    $picFiles = array_shift($pics);
                    $picFiles->deleteFile();
                }
                return $this->view(['error'=>[["property_path"=>'documentFile','message'=>$e->getMessage()]]],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');
            }
        }

        $manager = $this->getDoctrine()->getManager();

        //delete old file
        if($pics && $Book->getPicture()){
            $manager->remove($Book->getPicture()->deleteFile());
            $Book->setPicture(array_shift($pics));
            $manager->persist($Book->getPicture());
        }

        if($docFile && $Book->getDocument()){
            $manager->remove($Book->getDocument()->deleteFile());
            $Book->setDocument(array_shift($docFile));
            $manager->persist($Book->getDocument());
        }


        $manager->persist($Book);
        $manager->flush();

        return $this->view([BookType::name=>$Book],Error::SUCCESS_POST_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }
}