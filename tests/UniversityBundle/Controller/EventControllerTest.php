<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 04.10.16
 * Time: 13:00
 */

namespace UniversityBundle\Tests\Controller;

use ApiBundle\Tests\Controller\BaseControllerTest;

class EventControllerTest extends BaseControllerTest
{
    const BASE_SECTION_ROUTE = 'uniEventSections';
    const BASE_SECTION_ELEMENT = 'uniEventSection';
    const BASE_SECTION_ELEMENTS = 'uniEventSections';

    const BASE_ELEMENT_ROUTE = 'uniEvents';
    const BASE_ELEMENT = 'uniEvent';
    const BASE_ELEMENTS= 'uniEvents';

    public function testGlob(){
        $this->auth();

        $client = static::createClient();

        $client->request(
            'POST',
            "/api/".self::BASE_SECTION_ROUTE,
            [
                self::BASE_SECTION_ELEMENT=>[
                    'title'=>'test'
                ]
            ],
            [],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $resp = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals([self::BASE_SECTION_ELEMENT],array_keys($resp));

        $articleSection = $resp[self::BASE_SECTION_ELEMENT];

        $client->request(
            'PUT',
            sprintf("/api/%s/%d",self::BASE_SECTION_ROUTE,$articleSection['id']),
            [
                self::BASE_SECTION_ELEMENT=>[
                    'title'=>'test 22'
                ]
            ],
            [],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $resp = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals([self::BASE_SECTION_ELEMENT],array_keys($resp));

        $this->getElements(sprintf("/api/%s",self::BASE_SECTION_ROUTE),[self::BASE_SECTION_ELEMENTS]);
        $this->getElements(sprintf("/api/%s/%d",self::BASE_SECTION_ROUTE,$articleSection['id']),[self::BASE_SECTION_ELEMENT]);

        //articleTest
        $this->elementCrudTest($articleSection);

        $this->deleteElement(sprintf("/api/%s/%d",self::BASE_SECTION_ROUTE,$articleSection['id']));
    }

    public function elementCrudTest($sect){
        $client = static::createClient();

        $client->request(
            'POST',
            "/api/".self::BASE_ELEMENT_ROUTE,
            [
                self::BASE_ELEMENT=>[
                    'title'=>'text',
                    'description'=>'text',
                    'date'=> date('Y-m-d H:i',time()+1000),
                    'duration'=>123,
                    'tags'=>['type','text'],
                    'sections'=>[
                        $sect['id']
                    ]
                ]
            ],
            [
                self::BASE_ELEMENT=>[
                    'pictureFile'=>$this->getFile('pic')
                ]
            ],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $resp = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals([self::BASE_ELEMENT],array_keys($resp));

        $element = $resp[self::BASE_ELEMENT];

        $client->request(
            'PUT',
            sprintf("/api/%s/%d",self::BASE_ELEMENT_ROUTE,$element['id']),
            [
                self::BASE_ELEMENT=>[
                    'title'=>'text',
                    'description'=>'text',
                    'date'=> date('Y-m-d H:i',time()+1000),
                    'duration'=>123,
                    'tags'=>['type','text'],
                    'sections'=>[
                        $sect['id']
                    ]
                ]
            ],
            [],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $resp = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals([self::BASE_ELEMENT],array_keys($resp));

        $this->getElements(sprintf("/api/%s",self::BASE_ELEMENT_ROUTE),[self::BASE_ELEMENTS]);
        $this->getElements(sprintf("/api/%s/%d",self::BASE_ELEMENT_ROUTE,$element['id']),[self::BASE_ELEMENT]);

        //file manipulation
        $this->deleteElement(sprintf('/api/%s/%d/files/%d',self::BASE_ELEMENT_ROUTE,$element['id'],$element['picture']));

        $client->request(
            'POST',
            sprintf("/api/%s/%d/files",self::BASE_ELEMENT_ROUTE,$element['id']),
            [],
            [
                self::BASE_ELEMENT=>[
                    'pictureFile'=>$this->getFile('pic')
                ]
            ],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $resp = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals([self::BASE_ELEMENT],array_keys($resp));

        $this->deleteElement(sprintf("/api/%s/%d",self::BASE_ELEMENT_ROUTE,$element['id']));
    }
}