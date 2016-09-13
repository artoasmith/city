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
    /**
     * @param Request $request
     * @return bool|Client
     */
    public function checkAuth(Request $request){
        /**
         * @var AccessToken $accessToken
         */
        $accessToken = $request->headers->get('accessToken');
        $accessToken = $this->getDoctrine()
                            ->getRepository('UserBundle:AccessToken')
                            ->findOneBy(['token'=>$accessToken]);
        if(!$accessToken || $accessToken->getDate()->getTimestamp()<time() || $accessToken->getUserAgent() != $request->server->get('HTTP_USER_AGENT')) {
            $u = $this->getUser();
            if($u){
                $client = new Client();
                $client->setUser($u);
                return $client;
            }
            return false;
        }
        return $accessToken->getClient();
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function buildErrorResponse($name){
        /**
         * @var Error $error
         */
        $error = $this->getDoctrine()
            ->getRepository('ApiErrorBundle:Error')
            ->findOneBy(['name'=>$name]);

        if(!$error){
            $error = new Error();
            $error->setName($name)
                ->setCode(400)
                ->setDescription('Undefined error');

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($error);
            $manager->flush();
        }

        $view = $this->view(
            [
                'error'=>$error->getCode(),
                'message'=>$error->getDescription()
            ],
            $error->getCode()
        )->setTemplate('ApiErrorBundle:Default:index.html.twig');
        return $this->handleView($view);
    }

    /**
     * @param User $user
     * @return array
     */
    public function userAuth(User $user){
        $auth_token = md5('authtoken'.time().$this->getParameter('secret').rand(1,100));
        $auth_client = rand(10,99).$user->getId().'X'.time();
        $key = sprintf('auth_token_%s',$auth_client);
        $cache = $this->get('cache');
        $cache->setNamespace('auth.cache');

        $cache->save($key,$auth_token,$this->getParameter('auth_token_lifetime'));

        return ['authClient'=>$auth_client, 'authToken'=>$auth_token];
    }

    /**
     * @param User $user
     * @param object $manager
     * @return Client
     */
    public function clientGenerate(User $user, $manager = null){
        $secret = md5(time().$this->getParameter('secret').rand(1,100));
        $time = new \DateTime();
        $time->modify(sprintf('+%d sec',$this->getParameter('client_token_lifetime')));

        $client = new Client();
        $client->setUser($user)
            ->setDate($time)
            ->setSecret($secret);

        if(!$manager)
            $manager = $this->getDoctrine()->getManager();

        $manager->persist($client);
        $manager->flush();

        return $client;
    }

    /**
     * @param Client $client
     * @param object $manager
     * @return AccessToken
     */
    public function accessTokenGenerate(Client $client, $manager = null,Request $request=null){
        $token = md5('accessToken'.time().$this->getParameter('secret').rand(1,100));
        $time = new \DateTime();
        $time->modify(sprintf('+%d sec',$this->getParameter('access_token_lifetime')));

        $accessToken = new AccessToken();
        $accessToken->setClient($client)
                    ->setDate($time)
                    ->setToken($token)
                    ->setUserAgent($request?$request->server->get('HTTP_USER_AGENT'):'');

        if(!$manager)
            $manager = $this->getDoctrine()->getManager();

        $manager->persist($accessToken);
        $client->addAccessToken($accessToken);
        $manager->persist($client);
        $manager->flush();

        return $accessToken;
    }
}