<?php

namespace UniversityBundle\Tests\Controller;

use ApiBundle\Tests\Controller\BaseControllerTest;
use Symfony\Component\Form\Tests\Fixtures\FBooType;
use UniversityBundle\Controller\BookController;
use UniversityBundle\Controller\BookSectionController;
use UniversityBundle\Form\Type\BookType;
use UniversityBundle\Form\Type\BookSectionType;
use UniversityBundle\Entity\Book;
use UniversityBundle\Entity\BookSection;

class BookControllerTest extends BaseControllerTest
{
    public function testGlob(){
        $this->auth();

        $client = static::createClient();

        $client->request(
            'POST',
            "/api/".BookSectionController::DEF_ROUTE,
            [
                BookSectionType::NAME=>[
                    'title'=>'test'
                ]
            ],
            [],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $resp = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals([BookSection::ONE],array_keys($resp));

        $articleSection = $resp[BookSection::ONE];

        $client->request(
            'PUT',
            sprintf("/api/%s/%d",BookSectionController::DEF_ROUTE,$articleSection['id']),
            [
                BookSectionType::NAME=>[
                    'title'=>'test 22'
                ]
            ],
            [],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $resp = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals([BookSection::ONE],array_keys($resp));

        $this->getElements(sprintf("/api/%s",BookSectionController::DEF_ROUTE),[BookSection::MANY]);
        $this->getElements(sprintf("/api/%s/%d",BookSectionController::DEF_ROUTE,$articleSection['id']),[BookSection::ONE]);

        //articleTest
        $this->elementCrudTest($articleSection);

        $this->deleteElement(sprintf("/api/%s/%d",BookSectionController::DEF_ROUTE,$articleSection['id']));
    }

    public function elementCrudTest($sect){
        $client = static::createClient();

        $client->request(
            'POST',
            "/api/".BookController::DEF_ROUTE,
            [
                BookType::NAME=>[
                    'author'=>'text',
                    'description'=>'text',
                    'tags'=>['type','text'],
                    'title'=>'text',
                    'sections'=>[
                        $sect['id']
                    ]
                ]
            ],
            [
                BookType::NAME=>[
                    'pictureFile'=>$this->getFile('pic')
                ]
            ],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $resp = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals([Book::ONE],array_keys($resp));

        $element = $resp[Book::ONE];

        $client->request(
            'PUT',
            sprintf("/api/%s/%d",BookController::DEF_ROUTE,$element['id']),
            [
                BookType::NAME=>[
                    'author'=>'text',
                    'description'=>'text',
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
        $this->assertEquals([Book::ONE],array_keys($resp));

        $this->getElements(sprintf("/api/%s",BookController::DEF_ROUTE),[Book::MANY]);
        $this->getElements(sprintf("/api/%s/%d",BookController::DEF_ROUTE,$element['id']),[Book::ONE]);

        //file manipulation
        $this->deleteElement(sprintf('/api/%s/%d/files/%d',BookController::DEF_ROUTE,$element['id'],$element['picture']));

        $client->request(
            'POST',
            sprintf("/api/%s/%d/files",BookController::DEF_ROUTE,$element['id']),
            [],
            [
                BookType::NAME=>[
                    'pictureFile'=>$this->getFile('pic')
                ]
            ],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $resp = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals([Book::ONE],array_keys($resp));

        $this->deleteElement(sprintf("/api/%s/%d",BookController::DEF_ROUTE,$element['id']));
    }
}