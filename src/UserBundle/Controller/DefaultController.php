<?php

namespace UserBundle\Controller;

use ApiErrorBundle\Entity\Error;
use Doctrine\DBAL\Schema\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\AccessToken;
use UserBundle\Entity\User;
use UserBundle\Entity\Client;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use ApiErrorBundle\Controller\ErrorController;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Doctrine\Common\Cache\PhpFileCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\SecurityContext;


class DefaultController extends BaseController
{
    /**
     * @GET("/t")
     */
    public function getAngularAction(Request $request)
    {
        return $this->view(['url'=>'http://'.$request->server->get('SERVER_NAME').$request->server->get('REQUEST_URI')],200)
                    ->setFormat('html')
                    ->setTemplate('ApiBundle:Default:index.html.twig');
    }

    /**
     * @GET("/user/{id}")
     */
    public function getTAngularAction(Request $request,$id=0)
    {
        return $this->view(['url'=>'http://'.$request->server->get('SERVER_NAME').$request->server->get('REQUEST_URI')],200)
                    ->setFormat('html')
                    ->setTemplate('UserBundle:Default:index.html.twig');
    }
}
