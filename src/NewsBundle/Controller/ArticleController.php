<?php

namespace NewsBundle\Controller;

use ApiBundle\Classes\MatchPriorItem;
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
use FOS\RestBundle\Context\Context;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

class ArticleController extends ApiController
{
    const DEF_ROUTE = 'newsArticles';

    /**
     * @ApiDoc(
     *  section="News",
     *  resource=true,
     *  description="Delete article element picture",
     * )
     * @Annotations\Delete("/api/newsArticles/{id}/files/{file_id}");
     * @Security("is_granted('ROLE_NEWS_ARTICLE_UPDATE')")
     */
    public function deleteArticlesPicture(Request $request,$id=0,$file_id=0)
    {
        /**
         * @var Article $article
         */
        $article = $this->getDoctrine()->getRepository('NewsBundle:Article')->find($id);
        if(!$article)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $ids = $article->getFilesArray();

        if($article->getPicture()) {
            $ids[] = $article->getPicture()->getId();
        }

        if(!in_array($file_id,$ids))
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $manager = $this->getDoctrine()->getManager();
        if($article->getPicture() && $article->getPicture()->getId() == $file_id){
            $manager->remove($article->getPicture()->deleteFile());
        } else {
            $file = $this->getDoctrine()->getRepository('FileBundle:File')->find($file_id);
            if($file)
                $manager->remove($file->deleteFile());
        }

        $files = [];
        if($article->getFilesArray()){
            $files = $this->getDoctrine()->getRepository('FileBundle:File')->findBy(['id'=>$article->getFilesArray()]);
            $ids=[];
            if($files)
                $ids = array_map(function($a){return $a->getId();},$files);

            $article->setFilesArray($ids);
            $manager->persist($article);
        }
        if($article->getPicture())
            $files[] = $article->getPicture();

        $manager->flush();

        return $this->view([Article::ONE=>$article,File::MANY=>$files],Error::SUCCESS_DELETE_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="News",
     *  resource=true,
     *  description="Update article element picture",
     *  input="NewsBundle\Form\Type\ArticlePictureType"
     * )
     * @Annotations\Post("/api/newsArticles/{id}/files");
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
        if(!$article->getPictureFile() && !$article->getFiles()) {
            return $this->view(['error' => [["property_path" => (!$article->getPictureFile()?'pictureFile':'files'), 'message' => 'Значение не может быть пустым']]], Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');
        }

        //check file
        $files = null;
        if($article->getPictureFile()) {
            try {
                $files = FileController::upload($article->getPictureFile(), Article::DEF_PICTURE_FOLDER, File::PIC_TYPE);
            } catch (FileException $e) {
                return $this->view(['error' => [["property_path" => 'pictureFile', 'message' => $e->getMessage()]]], Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');
            }
        }

        $filesArray = [];
        if($article->getFiles()){
            $k = 0;
            try{
                foreach ($article->getFiles() as $k=>$file){
                    $a = FileController::upload($file,Article::DEF_FILE_FOLDER,File::FILE_TYPE);
                    $filesArray[] = array_shift($a);
                }
            } catch (FileException $e){
                //clear downloaded files
                if($filesArray){
                    foreach ($filesArray as $a){
                        $a->deleteFile();
                    }
                }
                if($files) {
                    foreach ($files as $f) {
                        $f->deleteFile();
                    }
                }
                return $this->view(['error'=>[["property_path"=>'files','id'=>$k,'message'=>$e->getMessage()]]],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');
            }
        }

        $manager = $this->getDoctrine()->getManager();

        if($files) {
            //delete old file
            if ($article->getPicture()) {
                $article->getPicture()->deleteFile();
                $manager->remove($article->getPicture());
            }

            if ($pic = array_shift($files))
                $manager->persist($pic);

            $article->setPicture($pic);
        }

        if($filesArray){
            $oldArray = $article->getFilesArray();
            if(!$oldArray)
                $oldArray = [];

            foreach ($filesArray as &$item){
                $manager->persist($item);
                $manager->flush();

                $oldArray[] = $item->getId();
            }
            $article->setFilesArray($oldArray);
        }

        $files = [];
        if($article->getFilesArray()){
            $files = $this->getDoctrine()->getRepository('FileBundle:File')->findBy(['id'=>$article->getFilesArray()]);
        }

        if($article->getPicture())
            $files[] = $article->getPicture();

        $article->setUser($this->getUser());
        $manager->persist($article);
        $manager->flush();

        $context = new Context();
        $context->addGroup('details');

        return $this->view([Article::ONE=>$article,File::MANY=>$files],Error::SUCCESS_POST_CODE)
                    ->setTemplate('ApiErrorBundle:Default:unformat.html.twig')
                    ->setContext($context);
    }

    /**
     * @ApiDoc(
     *  section="News",
     *  resource=true,
     *  description="Create article element",
     *  input="NewsBundle\Form\Type\ArticleType"
     * )
     * @Annotations\Post("/api/newsArticles");
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

        $filesArray = [];
        if($article->getFiles()){
            $k = 0;
            try{
                foreach ($article->getFiles() as $k=>$file){
                    $a = FileController::upload($file,Article::DEF_FILE_FOLDER,File::FILE_TYPE);
                    $filesArray[] = array_shift($a);
                }
            } catch (FileException $e){
                //clear downloaded files
                if($filesArray){
                    foreach ($filesArray as $a){
                        $a->deleteFile();
                    }
                }
                if($article->getPicture())
                    $article->getPicture()->deleteFile();

                return $this->view(['error'=>[["property_path"=>'files','id'=>$k,'message'=>$e->getMessage()]]],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');
            }
        }

        $manager = $this->getDoctrine()->getManager();
        if($filesArray){
            $idArray=[];
            foreach ($filesArray as &$item){
                $manager->persist($item);
                $manager->flush();

                $idArray[] = $item->getId();
            }
            $article->setFilesArray($idArray);
        }
        if($article->getPicture()) {
            $manager->persist($article->getPicture());
            $filesArray[] = $article->getPicture();
        }

        $manager->persist($article);
        $manager->flush();

        if($article->getTags()){
            $this->tagsManipulator('add','NewsBundle:Article',$article->getTags());
        }

        $context = new Context();
        $context->addGroup('details');

        return $this->view([Article::ONE=>$article,File::MANY=>$filesArray],Error::SUCCESS_POST_CODE)
                    ->setTemplate('ApiErrorBundle:Default:unformat.html.twig')
                    ->setContext($context);
    }

    /**
     * @ApiDoc(
     *  section="News",
     *  resource=true,
     *  description="Update article element",
     *  input="NewsBundle\Form\Type\ArticleType"
     * )
     * @Annotations\Put("/api/newsArticles/{id}");
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

        $oldTags = $article->getTags();
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

        $needRem = array_diff($oldTags,$article->getTags());
        if($needRem)
            $this->tagsManipulator('remove','NewsBundle:Article',$needRem);

        $needAdd = array_diff($article->getTags(),$oldTags);
        if($needAdd)
            $this->tagsManipulator('add','NewsBundle:Article',$needAdd);

        $context = new Context();
        $context->addGroup('details');

        $files = [];
        if($article->getFilesArray()){
            $files = $this->getDoctrine()->getRepository('FileBundle:File')->findBy(['id'=>$article->getFilesArray()]);
        }
        if($article->getPicture())
            $files[] = $article->getPicture();

        return $this->view([Article::ONE=>$article,File::MANY=>$files],Error::SUCCESS_PUT_CODE)
                    ->setTemplate('ApiErrorBundle:Default:unformat.html.twig')
                    ->setContext($context);
    }

    /**
     * @ApiDoc(
     *  section="News",
     *  resource=true,
     *  description="Delete article element"
     * )
     * @Annotations\Delete("/api/newsArticles/{id}");
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

        if($article->getFilesArray()){
            $files = $this->getDoctrine()->getRepository('FileBundle:File')->findBy(['id'=>$article->getFilesArray()]);
            foreach ($files as $file){
                $manager->remove($file->deleteFile());
            }
        }

        $manager->remove($article);
        $manager->flush();

        $this->tagsManipulator('remove','NewsBundle:Article',$article->getTags());

        return $this->view([Article::ONE=>$article],Error::SUCCESS_DELETE_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="News",
     *  resource=true,
     *  description="Get article elements"
     * )
     * @Annotations\Get("/api/newsArticles");
     * @Annotations\QueryParam(name="article[id]", description="element object")
     * @Annotations\QueryParam(name="article[title]", description="element title")
     * @Annotations\QueryParam(name="article[author]", description="element author")
     * @Annotations\QueryParam(name="article[date]", description="element date")
     * @Annotations\QueryParam(name="article[tags]", description="element tags")
     * @Annotations\QueryParam(name="article[text]", description="element text")
     * @Annotations\QueryParam(name="article[sections]", description="element sections")
     *
     * @Annotations\QueryParam(name="_laxParameters", description="Lax parameters in search (if they have search price value)('sections','tags','title','author')")
     * @Annotations\QueryParam(name="_sort", default={"id":"ASC"})
     * @Annotations\QueryParam(name="_limit",  requirements="\d+", nullable=true, strict=true)
     * @Annotations\QueryParam(name="_offset", requirements="\d+", nullable=true, strict=true)
     */
    public function getArticlesList(Request $request)
    {
        $context = new Context();
        $context->addGroup('list');
        $arr = $request->query->all();

        $elem = new MatchPriorItem();
        $elem->setField('sections')
             ->setPrice(1)
             ->setTotalVariable(true);

        $priorItems = ['sections'=>$elem];


        $priorItems['tags'] = $elem->setField('tags')->setPrice(2)->setTotalVariable(false);
        $priorItems['title'] = $elem->setField('title')->setPrice(3)->setTotalVariable(false);
        $priorItems['author'] = $elem->setField('author')->setPrice(2)->setTotalVariable(false);


        $resp = $this->priorityMatching('article','NewsBundle:Article', $arr, $priorItems);
        //var_dump($this->priorityMatching('article','NewsBundle:Article', $arr)); exit();
        return $this->view([Article::MANY=>$resp['items'],'meta'=>$resp['meta']],Error::SUCCESS_GET_CODE)
                    ->setTemplate('ApiErrorBundle:Default:unformat.html.twig')
                    ->setContext($context);
    }


    /**
     * @ApiDoc(
     *  section="News",
     *  resource=true,
     *  description="Get article elements autocomplete"
     * )
     * @Annotations\QueryParam(name="sections", description="element sections")
     * @Annotations\QueryParam(name="field", description="searchable field ('title','author','tags')")
     * @Annotations\QueryParam(name="q", description="query string")
     * @Annotations\QueryParam(name="_limit",  requirements="\d+", nullable=true, strict=true)
     *
     * @Annotations\Get("/api/newsArticles/autocomplete");
     */
    public function autocomplete(Request $request){
        $field = $request->query->get('field');
        $limit = $request->query->get('_limit');
        $limit = ($limit>=1 && $limit<=100?intval($limit):15);

        $allowFields = [
            'title','author','tags'
        ];

        $section = $request->query->get('sections');
        if(!is_array($section))
            $section = [$section];
        $sections = array_map(function($a){return intval($a);},$section);

        $q = $request->query->get('q');
        $q = strval((is_array($q)?'':$q));
        $q = str_replace(['\'','"','`','%','_',';'],['_','_','_','\%','\_'],$q);

        if(strlen($q)<2 || !in_array($field,$allowFields))
            return $this->view(['items'=>[],Error::SUCCESS_GET_CODE])
                ->setTemplate('ApiErrorBundle:Default:unformat.html.twig');

        $params = [
            'q'=>[
                $field=>$q,
                'sections'=>$sections
            ],
            '_limit'=>$limit*2 //not so good but faster
        ];

        $items = $this->matching('q','NewsBundle:Article',$params,[],['tags'=>'Y']);
        if(!is_array($items))
            return $items;

        $resp = [];
        switch ($field){
            case 'title':
                $resp = array_map(function($a){return $a->getTitle();},$items);
                break;
            case 'author':
                $resp = array_map(function($a){return $a->getAuthor();},$items);
                break;
            case 'tags':
                $tagsFilter = function ($a) use ($q){
                    return (strpos($a,$q) !== false);
                };
                foreach ($items as $item){
                    if($t = $item->getTags())
                        $resp = array_merge($resp,array_filter($t,$tagsFilter));
                }
                break;
        }

        if($resp)
            $resp = array_slice(array_unique($resp),0,$limit);

        return $this->view(['items'=>$resp],Error::SUCCESS_GET_CODE)
            ->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="News",
     *  resource=true,
     *  description="Get article element"
     * )
     * @Annotations\Get("/api/newsArticles/{id}");
     */
    public function getArticles(Request $request,$id=0)
    {
        /**
         * @var Article $article
         */
        $article = $this->getDoctrine()->getRepository('NewsBundle:Article')->find($id);
        if(!$article)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $context = new Context();
        $context->addGroup('details');
        $files = [];
        if($article->getFilesArray()){
            $files = $this->getDoctrine()->getRepository('FileBundle:File')->findBy(['id'=>$article->getFilesArray()]);
        }
        if($article->getPicture())
            $files[] = $article->getPicture();
        $formater = '%s_%s';
        if($this->getUser()){
            $cacheKey = $this->getUser()->getId();
        } else {
            $cacheKey = implode('_',[$request->server->get('HTTP_USER_AGENT'),$request->server->get('HTTP_COOKIE'),date('Ymd')]);
        }
        $cacheKey = sprintf($formater,$article->getId(),$cacheKey);

        $cache = $this->get('cache');
        $cache->setNamespace('viewsNewsArticle.cache');

        if(false === ($response = $cache->fetch($cacheKey))){
            $manager = $this->getDoctrine()->getManager();
            $article->setViews(intval($article->getViews()+1));
            $manager->persist($article);
            $manager->flush();

            $cache->save($cacheKey,1,600);
        }

        return $this->view([Article::ONE=>$article,File::MANY=>$files],Error::SUCCESS_GET_CODE)
            ->setTemplate('ApiErrorBundle:Default:unformat.html.twig')
            ->setContext($context);
    }
}