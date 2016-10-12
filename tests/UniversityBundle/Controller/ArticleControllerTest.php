<?php

namespace UniversityBundle\Tests\Controller;

use ApiBundle\Tests\Controller\BaseControllerTest;
use UniversityBundle\Controller\ArticleController;
use UniversityBundle\Controller\ArticleSectionController;
use UniversityBundle\Form\Type\ArticleType;
use UniversityBundle\Form\Type\ArticleSectionType;
use UniversityBundle\Entity\Article;
use UniversityBundle\Entity\ArticleSection;

class ArticleControllerTest extends BaseControllerTest
{
    public function testGlob(){
        $this->auth();

        $client = static::createClient();

        $client->request(
            'POST',
            "/api/".ArticleSectionController::DEF_ROUTE,
            [
                ArticleSectionType::NAME=>[
                    'title'=>'test'
                ]
            ],
            [],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $resp = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals([ArticleSection::ONE],array_keys($resp));

        $articleSection = $resp[ArticleSection::ONE];

        $client->request(
            'PUT',
            sprintf("/api/%s/%d",ArticleSectionController::DEF_ROUTE,$articleSection['id']),
            [
                ArticleSectionType::NAME=>[
                    'title'=>'test 22'
                ]
            ],
            [],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $resp = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals([ArticleSection::ONE],array_keys($resp));

        $this->getElements(sprintf("/api/%s",ArticleSectionController::DEF_ROUTE),[ArticleSection::MANY]);
        $this->getElements(sprintf("/api/%s/%d",ArticleSectionController::DEF_ROUTE,$articleSection['id']),[ArticleSection::ONE]);

        //articleTest
        $this->elementCrudTest($articleSection);

        $this->deleteElement(sprintf("/api/%s/%d",ArticleSectionController::DEF_ROUTE,$articleSection['id']));
    }

    public function elementCrudTest($sect){
        $client = static::createClient();

        $client->request(
            'POST',
            "/api/".ArticleController::DEF_ROUTE,
            [
                ArticleType::NAME=>[
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
                ArticleType::NAME=>[
                    'pictureFile'=>$this->getFile('pic')
                ]
            ],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $resp = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals([Article::ONE],array_keys($resp));

        $element = $resp[Article::ONE];

        $client->request(
            'PUT',
            sprintf("/api/%s/%d",ArticleController::DEF_ROUTE,$element['id']),
            [
                ArticleType::NAME=>[
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
        $this->assertEquals([Article::ONE],array_keys($resp));

        $this->getElements(sprintf("/api/%s",ArticleController::DEF_ROUTE),[Article::MANY]);
        $this->getElements(sprintf("/api/%s/%d",ArticleController::DEF_ROUTE,$element['id']),[Article::ONE]);

        //file manipulation
        $this->deleteElement(sprintf('/api/%s/%d/files/%d',ArticleController::DEF_ROUTE,$element['id'],$element['picture']));

        $client->request(
            'POST',
            sprintf("/api/%s/%d/files",ArticleController::DEF_ROUTE,$element['id']),
            [],
            [
                ArticleType::NAME=>[
                    'pictureFile'=>$this->getFile('pic')
                ]
            ],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $resp = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals([Article::ONE],array_keys($resp));

        $this->deleteElement(sprintf("/api/%s/%d",ArticleController::DEF_ROUTE,$element['id']));
    }
}