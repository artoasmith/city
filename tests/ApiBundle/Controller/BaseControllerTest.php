<?php

namespace ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BaseControllerTest extends WebTestCase
{
    public $username = 'unitest';
    public $password = 'unitest1488';

    public $client_id = '1_3bcbxd9e24g0gk4swg0kwgcwg4o8k8g4g888kwc44gcc0gwwk4';
    public $client_secret = '4ok2x70rlfokc8g0wws8c8kwcokw80k44sg48goc0ok4w0so0k';

    public $refresh_token = '';
    public $access_token = '';

    public function auth(){
        $client = static::createClient();

        $client->request(
            'POST',
            '/oauth/v2/token',
            [
                'grant_type'=>'password',
                'client_id'=>$this->client_id,
                'client_secret'=>$this->client_secret,
                'username'=>$this->username,
                'password'=>$this->password
            ]
        );

        $resp = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals(['access_token', 'expires_in', 'token_type', 'scope', 'refresh_token'],array_keys($resp));

        $this->access_token = ucfirst($resp['token_type']).' '.$resp['access_token'];
        $this->refresh_token = $resp['refresh_token'];
    }

    public function deleteElement($route){
        $client = static::createClient();

        $client->request(
            'DELETE',
            $route,
            [],
            [],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $this->assertEquals(204,$client->getResponse()->getStatusCode());
    }

    public function getElements($route,$assert,$params=[]){
        $client = static::createClient();

        $client->request(
            'GET',
            $route,
            $params,
            [],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $resp = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals($assert,array_keys($resp));
    }

    public function getFile($type){
        $file = tempnam(sys_get_temp_dir(), 'upl'); // create file
        switch ($type){
            case 'pic':
                imagepng(imagecreatetruecolor(10, 10), $file); // create and write image/png to file
                return new UploadedFile(
                    $file,
                    'new_image.png'
                );
            default:
                return null;
        }
    }

    /*
        protected $file;
        protected $image;

        public function setUp()
        {
            $this->file =
            imagepng(imagecreatetruecolor(10, 10), $this->file); // create and write image/png to it
            $this->image = new UploadedFile(
                $this->file,
                'new_image.png'
            );
        }

        public function testMime()
        {
            $res = $file->getMimeType();
            $this->assertEquals('image/png', $res);
        }

        public function tearDown()
        {
            unlink($this->file);
        }
    */

    public function testBase(){
        $this->assertTrue(true);
    }
}