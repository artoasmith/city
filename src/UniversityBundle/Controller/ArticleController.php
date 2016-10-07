<?php

namespace UniversityBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use UniversityBundle\Form\Type\ArticleType;
use UniversityBundle\Form\Type\ArticleFileType;
use UniversityBundle\Entity\ArticleSection;
use UniversityBundle\Entity\Article;
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

class ArticleController extends ApiController
{
    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Create article element",
     *  input="UniversityBundle\Form\Type\ArticleType"
     * )
     * @Annotations\Post("/api/uni_articles");
     * @Security("is_granted('ROLE_UNI_ARTICLE_CREATE')")
     */
    public function postArticle(Request $request)
    {
        $form = $this->createForm(ArticleType::class,new Article())
            ->handleRequest($request);
        /**
         * @var Article $Article
         */
        $Article = $form->getData();

        $Article->setSections($this->checkSectionArray($Article->getSections(),'UniversityBundle:ArticleSection'));

        $errors = $this->get('validator')->validate($Article);
        if (count($errors) > 0)
            return $this->view(['error'=>$errors],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $picFiles = false;
        if($Article->getPictureFile()){
            try{
                $picFiles = FileController::upload($Article->getPictureFile(),Article::DEF_PICTURE_FOLDER,File::PIC_TYPE);
            } catch (FileException $e){
                return $this->view(['error'=>[["property_path"=>'pictureFile','message'=>$e->getMessage()]]],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');
            }
        }


        $manager = $this->getDoctrine()->getManager();
        if($picFiles) {
            $Article->setPicture(array_shift($picFiles));
            $manager->persist($Article->getPicture());
        }

        $manager->persist($Article);
        $manager->flush();

        return $this->view([ArticleType::name=>$Article],Error::SUCCESS_POST_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Update article element",
     *  input="UniversityBundle\Form\Type\ArticleType"
     * )
     * @Annotations\Put("/api/uni_articles/{id}");
     * @Security("is_granted('ROLE_UNI_ARTICLE_UPDATE')")
     */
    public function putArticle(Request $request,$id=0)
    {
        /**
         * @var Article $Article
         */
        $Article = $this->getDoctrine()->getRepository('UniversityBundle:Article')->find($id);
        if(!$Article)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');


        $form = $this->createForm(ArticleType::class,$Article,array('method' => 'PUT'))
            ->handleRequest($request);
        $Article = $form->getData();
        $Article->setSections($this->checkSectionArray($Article->getSections(),'UniversityBundle:ArticleSection'));
        $errors = $this->get('validator')->validate($Article);
        if (count($errors) > 0)
            return $this->view(['error'=>$errors],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($Article);
        $manager->flush();

        return $this->view([ArticleType::name=>$Article],Error::SUCCESS_PUT_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Delete article element"
     * )
     * @Annotations\Delete("/api/uni_articles/{id}");
     * @Security("is_granted('ROLE_UNI_ARTICLE_DELETE')")
     */
    public function deleteArticle(Request $request,$id=0)
    {
        /**
         * @var Article $Article
         */
        $Article = $this->getDoctrine()->getRepository('UniversityBundle:Article')->find($id);
        if(!$Article)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $manager = $this->getDoctrine()->getManager();

        if($Article->getPicture()){
            $manager->remove($Article->getPicture()->deleteFile());
        }

        $manager->remove($Article);
        $manager->flush();

        return $this->view([ArticleType::name=>$Article],Error::SUCCESS_DELETE_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Get article elements"
     * )
     * @Annotations\Get("/api/uni_articles");
     * @Annotations\QueryParam(name="article[id]", description="element object")
     * @Annotations\QueryParam(name="article[title]", description="element title")
     * @Annotations\QueryParam(name="article[date]", description="element title")
     * @Annotations\QueryParam(name="article[author]", description="element date")
     * @Annotations\QueryParam(name="article[tags]", description="element tags")
     * @Annotations\QueryParam(name="article[sections]", description="element sections")
     * @Annotations\QueryParam(name="_sort", default={"id":"ASC"})
     * @Annotations\QueryParam(name="_limit",  requirements="\d+", nullable=true, strict=true)
     * @Annotations\QueryParam(name="_offset", requirements="\d+", nullable=true, strict=true)
     */
    public function getArticleList(Request $request)
    {
        $arr = $request->query->all();
        return $this->view([ArticleType::names=>$this->matching('article','UniversityBundle:Article', $arr)],Error::SUCCESS_GET_CODE)
            ->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Get article element"
     * )
     * @Annotations\Get("/api/uni_articles/{id}");
     */
    public function getArticle(Request $request,$id=0)
    {
        /**
         * @var Article $article
         */
        $article = $this->getDoctrine()->getRepository('UniversityBundle:Article')->find($id);
        if(!$article)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        return $this->view([ArticleType::name=>$article],Error::SUCCESS_GET_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Delete article's element picture"
     * )
     * @Annotations\Delete("/api/uni_articles/{id}/files/{file_id}");
     * @Security("is_granted('ROLE_UNI_ARTICLE_UPDATE')")
     */
    public function deleteArticlePicture(Request $request,$id=0,$file_id=0)
    {
        /**
         * @var Article $article
         */
        $article = $this->getDoctrine()->getRepository('UniversityBundle:Article')->find($id);
        if(!$article || !$article->getPicture() || $article->getPicture()->getId() != $file_id)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($article->getPicture()->deleteFile());
        $manager->flush();

        return $this->view([ArticleType::name=>$article],Error::SUCCESS_DELETE_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Update article element file",
     *  input="UniversityBundle\Form\Type\ArticleFileType"
     * )
     * @Annotations\Post("/api/uni_articles/{id}/files");
     * @Security("is_granted('ROLE_UNI_EVENT_UPDATE')")
     */
    public function postEventsPicture(Request $request,$id=0)
    {
        /**
         * @var Article $article
         */
        $article = $this->getDoctrine()->getRepository('UniversityBundle:Article')->find($id);
        if(!$article)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $form = $this->createForm(ArticleFileType::class,$article)
                     ->handleRequest($request);
        $article = $form->getData();
        if(!$article->getPictureFile())
            return $this->view(['error'=>[
                ["property_path"=>'pictureFile','message'=>'Значение не может быть пустым']
            ]],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        //check files
        $pics = false;
        if($article->getPictureFile()) {
            try {
                $pics = FileController::upload($article->getPictureFile(), Article::DEF_PICTURE_FOLDER, File::PIC_TYPE);
            } catch (FileException $e) {
                return $this->view(['error' => [["property_path" => 'pictureFile', 'message' => $e->getMessage()]]], Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');
            }
        }

        $manager = $this->getDoctrine()->getManager();

        //delete old file
        if($pics && $article->getPicture()){
            $manager->remove($article->getPicture()->deleteFile());
            $article->setPicture(array_shift($pics));
            $manager->persist($article->getPicture());
        }

        $manager->persist($article);
        $manager->flush();

        return $this->view([ArticleType::name=>$article],Error::SUCCESS_POST_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }
}