<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\FOSRestController;
use UserBundle\Entity\Test;

class DefaultController extends FOSRestController
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $u = $this->getUser();
        $t = new Test();
        $t->setInteg('fd')->setString('df');
        $view = $this->view(
            [
                'navigations'=>$t
            ],
            203
        );
        return $this->handleView($view);
        //return $this->render('UserBundle:Default:index.html.twig');
    }
}
