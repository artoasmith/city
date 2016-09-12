<?php

namespace UserBundle\Controller;

use ApiErrorBundle\Entity\Error;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;
use UserBundle\Entity\Client;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use ApiErrorBundle\Controller\ErrorController;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Doctrine\Common\Cache\PhpFileCache;

class DefaultController extends BaseController
{
    /**
     * @ApiDoc(
     *     section="Auth methods",
     *     resource=true,
     *     description="Get new access token"
     * )
     * @QueryParam(name="clientId", description="Client id")
     * @QueryParam(name="clientSecret", description="Client secret string")
     * @param Request $request
     * @POST("/refresh_token")
     * @return mixed
     */
    public function refreshTokenAction(Request $request)
    {
        $client_id = $request->request->get('clientId');
        $client_secret = $request->request->get('clientSecret');
        /**
         * @var Client $client
         */
        $client = $this->getDoctrine()->getRepository('UserBundle:Client')->findOneBy(['id'=>$client_id,'secret'=>$client_secret]);
        if(!$client)
            return $this->buildErrorResponse('invalidData');

        foreach ($client->getAccessToken()->toArray() as $at)
            $client->removeAccessToken($at);

        $accessToken = $this->accessTokenGenerate($client);

        $view = $this->view(
            [
                'clientId'=>$client->getId(),
                'clientSecret'=>$client->getSecret(),
                'accessToken'=>$accessToken->getToken()
            ],
            200
        );
        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *     section="Auth methods",
     *     resource=true,
     *     description="Get auth client data"
     * )
     * @QueryParam(name="authClient", description="Auth client string")
     * @QueryParam(name="authToken", description="Auth token")
     * @POST("/auth_client")
     */
    public function authClientAction(Request $request)
    {
        $auth_client = $request->request->get('authClient');
        $auth_token = $request->request->get('authToken');

        /**
         * @var PhpFileCache $cache
         */
        $key = sprintf('auth_token_%s',$auth_client);
        $cache = $this->get('cache');
        $cache->setNamespace('auth.cache');

        if(false === ($response = $cache->fetch($key)) && $response != $auth_token)
            return $this->buildErrorResponse('invalidData');

        $pattern = '/^(\d\d)(\d+)(X)/is';
        \preg_match_all($pattern, trim($auth_client).' ', $matches, PREG_SET_ORDER);

        if(!isset($matches[0]) || !isset($matches[0][2]) || $matches[0][2]<=0)
            return $this->buildErrorResponse('invalidData');

        /**
         * @var User $user
         */
        $user = $this->getDoctrine()->getRepository('UserBundle:User')->find($matches[0][2]);
        if(!$user)
            return $this->buildErrorResponse('invalidData');

        $cache->delete($key);
        $client = $this->clientGenerate($user);
        $accessToken = $this->accessTokenGenerate($client);

        $view = $this->view(
            [
                'clientId'=>$client->getId(),
                'clientSecret'=>$client->getSecret(),
                'accessToken'=>$accessToken->getToken()
            ],
            200
        );
        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *     section="Auth methods",
     *     resource=true,
     *     description="Get auth data",
     * )
     * @QueryParam(name="authLogin", description="User login")
     * @QueryParam(name="authPassword", description="User password")
     * @POST("/auth")
     */
    public function authAction(Request $request)
    {
        $u = $this->getUser();
        if($u)
            return $this->redirect('/');

        $login = $request->request->get('authLogin');
        $pass = $request->request->get('authPassword');
        /**
         * @var User $user
         */
        $user = $this->getDoctrine()
                     ->getRepository('UserBundle:User')
                     ->findOneBy(['username'=>$login]);

        if(!$user)
            return $this->buildErrorResponse('invalidAuth');

        $encoder_service = $this->get('security.encoder_factory');
        $encoder = $encoder_service->getEncoder($user);
        $encoded_pass = $encoder->encodePassword($pass, $user->getSalt());
        if($encoded_pass != $user->getPassword())
            return $this->buildErrorResponse('invalidAuth');

        $authArray = $this->userAuth($user);

        $view = $this->view(
            $authArray,
            202
        );
        return $this->handleView($view);
    }
}
