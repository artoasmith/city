<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 12.09.16
 * Time: 11:22
 */

namespace UserBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use ApiErrorBundle\Entity\Error;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\AccessToken;
use UserBundle\Entity\User;
use UserBundle\Entity\Client;

class BaseController extends FOSRestController
{

}