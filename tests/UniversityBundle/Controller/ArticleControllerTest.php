<?php

namespace UniversityBundle\Tests\Controller;

use ApiBundle\Tests\Controller\BaseControllerTest;

class ArticleControllerTest extends BaseControllerTest
{
    const BASE_SECTION_ROUTE = 'uni_article_sections';
    const BASE_SECTION_ELEMENT = 'uni_article_section';
    const BASE_SECTION_ELEMENTS = 'uni_article_sections';

    const BASE_ELEMENT_ROUTE = 'uni_articles';
    const BASE_ELEMENT = 'uni_article';
    const BASE_ELEMENTS= 'uni_articles';

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
                    'author'=>'text',
                    'date'=> date('Y-m-d H:i',time()+1000),
                    'text'=>'text',
                    'tags'=>['type','text'],
                    'title'=>'text',
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
                    'author'=>'text',
                    'date'=> date('Y-m-d H:i',time()+1000),
                    'text'=>'text',
                    'tags'=>['type','text'],
                    'title'=>'text',
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