<?php

namespace CommentsBundle\Controller;

use CommentsBundle\Entity\Page;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use NewsBundle\Entity\Article;
use ApiErrorBundle\Entity\Error;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FileBundle\Controller\DefaultController as FileController;
use FileBundle\Entity\File;
use CommentsBundle\Form\Type\CommentType;
use FOS\RestBundle;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use CommentsBundle\Entity\Comment;
use UserBundle\Entity\User;
use ApiBundle\ApiBundle;

class DefaultController extends FOSRestController
{
    /**
     * @param $manager
     * @return Page
     */
    public function createPage(&$manager){
        $page = new Page();
        $page->setDate(new \DateTime());
        $manager->persist($page);
        return $page;
    }

    /**
     * @ApiDoc(
     *  section="Comments",
     *  resource=true,
     *  description="Create comment",
     *  input="CommentsBundle\Form\Type\CommentType"
     * )
     * @Annotations\Post("/api/comments");
     * @Security("is_granted('ROLE_COMMENTS_CREATE')")
     */
    public function postComments(Request $request)
    {
        $form = $this->createForm(new CommentType())
                     ->handleRequest($request);

        $data = $form->getData();

        $manager = $this->getDoctrine()->getManager();
        /**
         * @var Comment $page
         */
        //check sourse for find/create CommentsBundle:Page element
        $page = null;
        if($data['newsArticle']){
            $page = $data['newsArticle']->getCommentPage();
            if(!$page){
                $page = $this->createPage($manager);
                $data['newsArticle']->setCommentPage($page);
                $manager->persist($data['newsArticle']);
            }
        }elseif ($data['uniEvent']){
            $page = $data['uniEvent']->getCommentPage();
            if(!$page){
                $page = $this->createPage($manager);
                $data['uniEvent']->setCommentPage($page);
                $manager->persist($data['uniEvent']);
            }
        }elseif ($data['uniArticle']){
            $page = $data['uniArticle']->getCommentPage();
            if(!$page){
                $page = $this->createPage($manager);
                $data['uniArticle']->setCommentPage($page);
                $manager->persist($data['uniArticle']);
            }
        }elseif ($data['uniBook']){
            $page = $data['uniBook']->getCommentPage();
            if(!$page){
                $page = $this->createPage($manager);
                $data['uniBook']->setCommentPage($page);
                $manager->persist($data['uniBook']);
            }
        }

        if(!$page)
            return $this->view(['error'=>[["property_path"=>'source','message'=>'Недопустимое значение.']]],Error::FORM_ERROR_CODE)
                        ->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $comment = new Comment();
        $comment->setDate(new \DateTime())
                ->setPage($page)
                ->setParentComment($data['parentComment'])
                ->setText($data['text'])
                ->setUser($this->getUser());

        $errors = $this->get('validator')->validate($comment);
        if (count($errors) > 0)
            return $this->view(['error'=>$errors],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        if($comment->getParentComment())
            $manager->persist($comment->getParentComment()->setHasChild(true));

        $manager->persist($comment);
        $manager->flush();

        //position
        $comment->setPosition(($comment->getParentComment()?$comment->getParentComment()->getPosition():$comment->getId()));
        $manager->persist($comment);

        //page comment count
        $cnt = intval($page->getCommentsCount())+1;
        $page->setCommentsCount($cnt);
        $manager->persist($page);

        $manager->flush();

        return $this->view(['comment'=>$comment],Error::SUCCESS_POST_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="Comments",
     *  resource=true,
     *  description="Update comment"
     * )
     * @Annotations\Put("/api/comments/{id}");
     * @Annotations\QueryParam(name="comment[text]", description="Text");
     * @Security("is_granted('ROLE_COMMENTS_UPDATE')")
     */
    public function putComments(Request $request,$id=0)
    {
        /**
         * @var Comment $comment
         * @var User $user
         */
        $user = $this->getUser();

        $comment = $this->getDoctrine()->getRepository('CommentsBundle:Comment')->find($id);
        if(!$comment)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        if($user->getId() != $comment->getUser()->getId() || $comment->getDate()->getTimestamp() < time() - Comment::PUT_ELEMENT_TIME)
            return $this->view(['error'=>'Отказано в доспе'],Error::NOT_ALLOWED)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');

        $text = $request->request->get('comment');
        $text = (isset($text['text'])?$text['text']:'');
        $comment->setText($text);

        $errors = $this->get('validator')->validate($comment);
        if (count($errors) > 0)
            return $this->view(['error'=>$errors],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($comment);
        $manager->flush();

        return $this->view(['comment'=>$comment],Error::SUCCESS_PUT_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="Comments",
     *  resource=true,
     *  description="Delete comment"
     * )
     * @Annotations\Delete("/api/comments/{id}");
     * @Security("is_granted('ROLE_COMMENTS_DELETE')")
     */
    public function deleteComments(Request $request,$id=0)
    {
        /**
         * @var Comment $comment
         * @var User $user
         */
        $user = $this->getUser();

        $comment = $this->getDoctrine()->getRepository('CommentsBundle:Comment')->find($id);
        if(!$comment)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');


        if($user->getId() != $comment->getUser()->getId() || $comment->getDate()->getTimestamp() < time() - Comment::DELETE_ELEMENT_TIME)
            return $this->view(['error'=>'Отказано в доспе'],Error::NOT_ALLOWED)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');

        $manager = $this->getDoctrine()->getManager();
        //checking if parent left some children
        if($comment->getParentComment()){
            $children = $this->getDoctrine()->getRepository('CommentsBundle:Comment')->findBy(['parentComment'=>$comment->getParentComment()->getId()]);
            if(!$children || count($children)<=1){
                $manager->persist($comment->getParentComment()->setHasChild(false));
            }
        }


        //comments count
        $cnt = $comment->getPage()->getCommentsCount()-1;
        $cnt = ($cnt?intval($cnt):0);
        $manager->persist($comment->getPage()->setCommentsCount($cnt));

        $manager->remove($comment);
        $manager->flush();

        return $this->view(['comment'=>$comment],Error::SUCCESS_DELETE_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="Comments",
     *  resource=true,
     *  description="Get comment"
     * )
     * @Annotations\Get("/api/comments");
     *
     * @Annotations\QueryParam(name="page",  requirements="\d+", description="Page", default=null)
     * @Annotations\QueryParam(name="parentComment",  requirements="\d+", description="Parent comment id", default=null)
     * @Annotations\QueryParam(name="_lastElement",  requirements="\d+", description="Last element", default=null)
     * @Annotations\QueryParam(name="_limit", requirements="\d+", description="Limit elements", default=null)
     */
    public function getCommentsTree(Request $request)
    {
        $elementsLeft = 0;
        $comments = [];
        $lastElement = ($request->query->get('_lastElement')>0?intval($request->query->get('_lastElement')):0);
        $limit = ($request->query->get('_limit')>0 && $request->query->get('_limit')<=100?intval($request->query->get('_limit')):20);

        $page = ($request->query->get('page')>0?intval($request->query->get('page')):0);

        $parent = ($request->query->get('parentComment')>0?intval($request->query->get('parentComment')):0);
        $parent = ($parent?' c.parentComment='.$parent:' (c.parentComment IS NULL OR c.parentComment=0)');
        $query = sprintf('SELECT c.id as id FROM cm_comment as c WHERE %s AND c.page=%d ORDER BY c.date ASC',$parent,$page);

        $stmt = $this->getDoctrine()->getManager()
            ->getConnection()
            ->prepare(
                $query
            );
        $stmt->execute();
        $ideas = $stmt->fetchAll();
        if($ideas){
            $ideas = array_map(function($a){return $a['id'];},$ideas);
            //slice array
            $key = false;
            if($lastElement>0){
                $key = array_search($lastElement,$ideas);
                if($key !== false)
                    $key++;
            }

            $ideas = array_slice($ideas,intval($key));
            $elementsLeft = count($ideas)-$limit;
            if($elementsLeft>0){
                $ideas = array_slice($ideas,0,$limit);
            } else {
                $elementsLeft = 0;
            }

            $comments = $this->getDoctrine()->getRepository('CommentsBundle:Comment')->findBy(['id'=>$ideas],['date'=>'ASC']);
        }

        return $this->view([
            'comments'=>$comments,
            ApiBundle::META_VIEW_KEY=>[
                ApiBundle::META_ELEMENTS_LEFT_KEY=>$elementsLeft
            ]
        ],Error::SUCCESS_GET_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="Comments",
     *  resource=true,
     *  description="Get comment"
     * )
     * @Annotations\Get("/api/comments/{id}");
     */
    public function getComments(Request $request,$id=0)
    {
        /**
         * @var Comment $comment
         */
        $comment = $this->getDoctrine()->getRepository('CommentsBundle:Comment')->find($id);
        if(!$comment)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        return $this->view(['comment'=>$comment],Error::SUCCESS_GET_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }
}
