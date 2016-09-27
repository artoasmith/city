<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FileBundle\Controller\DefaultController as FileController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class DefaultController extends FOSRestController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @POST("/api")
     * @Security("is_granted('ROLE_USER')")
     */
    public function getDemosAction(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->get('security.context')->getToken()->getUser();

        $files = $request->files->get('key');
        try{
            $files = FileController::upload($files,'images');
        } catch (FileException $e){
            $view = $this->view(['error'=>$e->getMessage()],422);
            return $this->handleView($view);
        }

        $data = array("hello" => $files);exit();
        $view = $this->view($data);
        return $this->handleView($view);
    }
}
