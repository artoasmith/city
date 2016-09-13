<?php

namespace UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public $userLogin = 'unittest';
    public $userPassWord = 'unittest1488';

    public function testAuth()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/auth',
            [
                'authLogin'=>$this->userLogin,
                'authPassword'=>$this->userPassWord
            ]
        );

        $resp = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals(['authClient', 'authToken'],array_keys($resp));

        $client->request(
            'POST',
            '/authClient',
            [
                'authClient'=>$resp['authClient'],
                'authToken'=>$resp['authToken']
            ]
        );

        $respClient = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals(['clientId','clientSecret','accessToken'],array_keys($respClient));

        $client->request(
            'POST',
            '/refreshToken',
            [
                'clientId'=>$respClient['clientId'],
                'clientSecret'=>$respClient['clientSecret']
            ]
        );

        $respClient = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals(['clientId','clientSecret','accessToken'],array_keys($respClient));
    }
}
