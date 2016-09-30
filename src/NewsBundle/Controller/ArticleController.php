<?php

namespace NewsBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use NewsBundle\Entity\Article;
use ApiErrorBundle\Entity\Error;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FileBundle\Controller\DefaultController as FileController;
use FileBundle\Entity\File;
use NewsBundle\Form\Type\ArticleType;
use NewsBundle\Form\Type\ArticlePictureType;
use FOS\RestBundle;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use ApiBundle\Controller\DefaultController as ApiController;

class ArticleController extends ApiController
{
    /**
     * @ApiDoc(
     *  section="News",
     *  resource=true,
     *  description="Delete article element picture",
     * )
     * @Annotations\Delete("/api/news_articles/{id}/files/{file_id}");
     * @Security("is_granted('ROLE_NEWS_ARTICLE_UPDATE')")
     */
    public function deleteArticlesPicture(Request $request,$id=0,$file_id=0)
    {
        /**
         * @var Article $article
         */
        $article = $this->getDoctrine()->getRepository('NewsBundle:Article')->find($id);
        if(!$article || !$article->getPicture() || $article->getPicture()->getId() != $file_id)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($article->getPicture()->deleteFile());
        $manager->flush();

        return $this->view(['article'=>$article],Error::SUCCESS_DELETE_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="News",
     *  resource=true,
     *  description="Update article element picture",
     *  input="NewsBundle\Form\Type\ArticlePictureType"
     * )
     * @Annotations\Post("/api/news_articles/{id}/files");
     * @Security("is_granted('ROLE_NEWS_ARTICLE_UPDATE')")
     */
    public function postArticlesPicture(Request $request,$id=0)
    {
        /**
         * @var Article $article
         */
        $article = $this->getDoctrine()->getRepository('NewsBundle:Article')->find($id);
        if(!$article)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $form = $this->createForm(new ArticlePictureType(),$article)
            ->handleRequest($request);
        $article = $form->getData();
        if(!$article->getPictureFile())
            return $this->view(['error'=>[["property_path"=>'pictureFile','message'=>'Значение не может быть пустым']]],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        //check file
        try{
            $files = FileController::upload($article->getPictureFile(),Article::DEF_PICTURE_FOLDER,File::PIC_TYPE);
        } catch (FileException $e){
            return $this->view(['error'=>[["property_path"=>'pictureFile','message'=>$e->getMessage()]]],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');
        }
        $manager = $this->getDoctrine()->getManager();

        //delete old file
        if($article->getPicture()){
            $article->getPicture()->deleteFile();
            $manager->remove($article->getPicture());
        }

        if($pic = array_shift($files))
            $manager->persist($pic);

        $article->setUser($this->get('security.context')->getToken()->getUser());
        $article->setPicture($pic);
        $manager->persist($article);
        $manager->flush();

        return $this->view(['article'=>$article],Error::SUCCESS_POST_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="News",
     *  resource=true,
     *  description="Create article element",
     *  input="NewsBundle\Form\Type\ArticleType"
     * )
     * @Annotations\Post("/api/news_articles");
     * @Security("is_granted('ROLE_NEWS_ARTICLE_CREATE')")
     */
    public function postArticles(Request $request)
    {
        $form = $this->createForm(new ArticleType(),new Article())
            ->handleRequest($request);
        /**
        * @var Article $article
        */
        $article = $form->getData();

        $article->setSections($this->checkSectionArray($article->getSections(),'NewsBundle:Section'));

        $errors = $this->get('validator')->validate($article);
        if (count($errors) > 0)
            return $this->view(['error'=>$errors],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        if($article->getPictureFile()){
            try{
                $files = FileController::upload($article->getPictureFile(),Article::DEF_PICTURE_FOLDER,File::PIC_TYPE);
                $article->setPicture(array_shift($files));
            } catch (FileException $e){
                return $this->view(['error'=>[["property_path"=>'pictureFile','message'=>$e->getMessage()]]],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');
            }
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($article->getPicture());
        $manager->persist($article);
        $manager->flush();

        return $this->view(['article'=>$article],Error::SUCCESS_POST_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="News",
     *  resource=true,
     *  description="Update article element",
     *  input="NewsBundle\Form\Type\ArticleType"
     * )
     * @Annotations\Put("/api/news_articles/{id}");
     * @Security("is_granted('ROLE_NEWS_ARTICLE_UPDATE')")
     */
    public function putArticles(Request $request,$id=0)
    {
        /**
         * @var Article $article
         */
        $article = $this->getDoctrine()->getRepository('NewsBundle:Article')->find($id);
        if(!$article)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');


        $form = $this->createForm(new ArticleType(),$article,array('method' => 'PUT'))
            ->handleRequest($request);
        $article = $form->getData();

        $article->setSections($this->checkSectionArray($article->getSections(),'NewsBundle:Section'));

        $errors = $this->get('validator')->validate($article);
        if (count($errors) > 0)
            return $this->view(['error'=>$errors],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($article);
        $manager->flush();

        return $this->view(['article'=>$article],Error::SUCCESS_PUT_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="News",
     *  resource=true,
     *  description="Delete article element"
     * )
     * @Annotations\Delete("/api/news_articles/{id}");
     * @Security("is_granted('ROLE_NEWS_ARTICLE_DELETE')")
     */
    public function deleteArticles(Request $request,$id=0)
    {
        /**
         * @var Article $article
         */
        $article = $this->getDoctrine()->getRepository('NewsBundle:Article')->find($id);
        if(!$article)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $manager = $this->getDoctrine()->getManager();

        if($article->getPicture()){
            $article->getPicture()->deleteFile();
            $manager->remove($article->getPicture());
        }

        $manager->remove($article);
        $manager->flush();

        return $this->view(['article'=>$article],Error::SUCCESS_DELETE_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }



    /**
     * @ApiDoc(
     *  section="News",
     *  resource=true,
     *  description="Get article elements"
     * )
     * @Annotations\Get("/api/news_articles");
     * @Annotations\QueryParam(name="article[id]", description="element object")
     * @Annotations\QueryParam(name="article[title]", description="element title")
     * @Annotations\QueryParam(name="article[date]", description="element date")
     * @Annotations\QueryParam(name="article[tags]", description="element tags")
     * @Annotations\QueryParam(name="article[text]", description="element text")
     * @Annotations\QueryParam(name="article[sections]", description="element sections")
     * @Annotations\QueryParam(name="_sort", default={"id":"ASC"})
     * @Annotations\QueryParam(name="_limit",  requirements="\d+", nullable=true, strict=true)
     * @Annotations\QueryParam(name="_offset", requirements="\d+", nullable=true, strict=true)
     */
    public function getArticlesList(Request $request)
    {
        $arr = $request->query->all();
        return $this->view(['articles'=>$this->matching('article','NewsBundle:Article', $arr)],Error::SUCCESS_GET_CODE)
                    ->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="News",
     *  resource=true,
     *  description="Get article element"
     * )
     * @Annotations\Get("/api/news_articles/{id}");
     */
    public function getArticles(Request $request,$id=0)
    {
        /**
         * @var Article $article
         */
        $article = $this->getDoctrine()->getRepository('NewsBundle:Article')->find($id);
        if(!$article)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        return $this->view(['article'=>$article],Error::SUCCESS_GET_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }
}