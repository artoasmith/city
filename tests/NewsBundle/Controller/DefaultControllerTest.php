<?php

namespace NewsBundle\Tests\Controller;

use ApiBundle\Tests\Controller\BaseControllerTest;
use NewsBundle\Entity\Article;
use NewsBundle\Entity\Section;
use NewsBundle\Form\Type\SectionType;
use NewsBundle\Form\Type\ArticleType;
use NewsBundle\Controller\ArticleController;
use NewsBundle\Controller\SectionController;
use FileBundle\Entity\File;

class DefaultControllerTest extends BaseControllerTest
{
    public function testIndex()
    {
        $this->auth();

        $client = static::createClient();

        $client->request(
            'POST',
            "/api/".SectionController::DEF_ROUTE,
            [
                SectionType::NAME=>[
                    'title'=>'test',
                    'metaDescription'=>'desc desc',
                    'metaKeyWords'=>'words words'
                ]
            ],
            [],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $resp = $this->assertKeys([Section::ONE],$client);
        $section = $resp[Section::ONE];

        $client->request(
            'PUT',
            sprintf("/api/%s/%d",SectionController::DEF_ROUTE,$section['id']),
            [
                SectionType::NAME=>[
                    'title'=>'test 22',
                    'metaDescription'=>'desc desc',
                    'metaKeyWords'=>'words words'
                ]
            ],
            [],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $this->assertKeys([Section::ONE],$client);

        $this->getElements(sprintf("/api/%s",SectionController::DEF_ROUTE),[Section::MANY]);
        $this->getElements(sprintf("/api/%s/%d",SectionController::DEF_ROUTE,$section['id']),[Section::ONE]);

        //testNewsArticles
        $this->newsArticle($section);

        $this->deleteElement(sprintf("/api/%s/%d",SectionController::DEF_ROUTE,$section['id']));
    }

    public function newsArticle($section){
        $client = static::createClient();

        $client->request(
            'POST',
            "/api/".ArticleController::DEF_ROUTE,
            [
                ArticleType::NAME=>[
                    'title'=>'text',
                    'author'=>'article',
                    'date'=> date('Y-m-d H:i',time()+1000),
                    'tags'=>['type','text'],
                    'text'=>'text',
                    'sections'=>[
                        $section['id']
                    ],
                    'description'=>'sd',
                    'metaTitle'=>'sd',
                    'metaDescription'=>'ds',
                    'metaKeyWords'=>'ds'
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

        $resp = $this->assertKeys([Article::ONE,File::MANY],$client);
        $element = $resp[Article::ONE];

        $client->request(
            'PUT',
            sprintf("/api/%s/%d",ArticleController::DEF_ROUTE,$element['id']),
            [
                ArticleType::NAME=>[
                    'title'=>'text',
                    'author'=>'article',
                    'date'=> date('Y-m-d H:i',time()+1000),
                    'tags'=>['type','text'],
                    'text'=>'text',
                    'sections'=>[
                        $section['id']
                    ],
                    'description'=>'sd',
                    'metaTitle'=>'sd',
                    'metaDescription'=>'ds',
                    'metaKeyWords'=>'ds'
                ]
            ],
            [],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $this->assertKeys([Article::ONE,File::MANY],$client);

        $this->getElements(sprintf("/api/%s/autocomplete",ArticleController::DEF_ROUTE),['items'],['sections'=>$section['id'],'field'=>'title','q'=>'te']);
        $this->getElements(sprintf("/api/%s",ArticleController::DEF_ROUTE),[Article::MANY,'meta']);
        $this->getElements(sprintf("/api/%s/%d",ArticleController::DEF_ROUTE,$element['id']),[Article::ONE,File::MANY]);

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

        $this->assertKeys([Article::ONE,File::MANY],$client);

        $this->deleteElement(sprintf("/api/%s/%d",ArticleController::DEF_ROUTE,$element['id']));
    }
}
