<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 04.10.16
 * Time: 13:00
 */

namespace UniversityBundle\Tests\Controller;

use ApiBundle\Tests\Controller\BaseControllerTest;
use UniversityBundle\Controller\EventController;
use UniversityBundle\Controller\EventSectionController;
use UniversityBundle\Form\Type\EventType;
use UniversityBundle\Form\Type\EventSectionType;
use UniversityBundle\Entity\Event;
use UniversityBundle\Entity\EventSection;

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
            "/api/".EventSectionController::DEF_ROUTE,
            [
                EventSectionType::NAME=>[
                    'title'=>'test'
                ]
            ],
            [],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $resp = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals([EventSection::ONE],array_keys($resp));

        $articleSection = $resp[EventSection::ONE];

        $client->request(
            'PUT',
            sprintf("/api/%s/%d",EventSectionController::DEF_ROUTE,$articleSection['id']),
            [
                EventSectionType::NAME=>[
                    'title'=>'test 22'
                ]
            ],
            [],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $resp = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals([EventSection::ONE],array_keys($resp));

        $this->getElements(sprintf("/api/%s",EventSectionController::DEF_ROUTE),[EventSection::MANY]);
        $this->getElements(sprintf("/api/%s/%d",EventSectionController::DEF_ROUTE,$articleSection['id']),[EventSection::ONE]);

        //articleTest
        $this->elementCrudTest($articleSection);

        $this->deleteElement(sprintf("/api/%s/%d",EventSectionController::DEF_ROUTE,$articleSection['id']));
    }

    public function elementCrudTest($sect){
        $client = static::createClient();

        $client->request(
            'POST',
            "/api/".EventController::DEF_ROUTE,
            [
                EventType::NAME=>[
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
                EventType::NAME=>[
                    'pictureFile'=>$this->getFile('pic')
                ]
            ],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $resp = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals([Event::ONE],array_keys($resp));

        $element = $resp[Event::ONE];

        $client->request(
            'PUT',
            sprintf("/api/%s/%d",EventController::DEF_ROUTE,$element['id']),
            [
                EventType::NAME=>[
                    'title'=>'text',
                    'description'=>'text',
                    'date'=> date('Y-m-d H:i',time()+1000),
                    'duration'=>123,
                    'tags'=>['type2','text'],
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
        $this->assertEquals([Event::ONE],array_keys($resp));

        $this->getElements(sprintf("/api/%s",EventController::DEF_ROUTE),[Event::MANY]);
        $this->getElements(sprintf("/api/%s/%d",EventController::DEF_ROUTE,$element['id']),[Event::ONE]);

        //file manipulation
        $this->deleteElement(sprintf('/api/%s/%d/files/%d',EventController::DEF_ROUTE,$element['id'],$element['picture']));

        $client->request(
            'POST',
            sprintf("/api/%s/%d/files",EventController::DEF_ROUTE,$element['id']),
            [],
            [
                EventType::NAME=>[
                    'pictureFile'=>$this->getFile('pic')
                ]
            ],
            [
                'HTTP_Authorization'=>$this->access_token
            ]
        );

        $resp = json_decode($client->getResponse()->getContent(),true);
        $this->assertEquals([Event::ONE],array_keys($resp));

        $this->deleteElement(sprintf("/api/%s/%d",EventController::DEF_ROUTE,$element['id']));
    }
}