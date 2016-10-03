<?php

namespace UniversityBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use UniversityBundle\Form\Type\EventType;
use UniversityBundle\Form\Type\EventPictureType;
use UniversityBundle\Entity\Section;
use UniversityBundle\Entity\Event;
use ApiErrorBundle\Entity\Error;
use Symfony\Component\HttpFoundation\Request;
use FileBundle\Controller\DefaultController as FileController;
use FileBundle\Entity\File;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle;
use ApiBundle\Controller\DefaultController as ApiController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class EventController extends ApiController
{
    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Create event element",
     *  input="UniversityBundle\Form\Type\EventType"
     * )
     * @Annotations\Post("/api/uni_events");
     * @Security("is_granted('ROLE_UNI_EVENT_CREATE')")
     */
    public function postEvent(Request $request)
    {
        $form = $this->createForm(new EventType(),new Event())
                     ->handleRequest($request);
        /**
         * @var Event $Event
         */
        $Event = $form->getData();
        $Event->setUser($this->get('security.context')->getToken()->getUser());

        $Event->setSections($this->checkSectionArray($Event->getSections(),'UniversityBundle:Section'));

        $errors = $this->get('validator')->validate($Event);
        if (count($errors) > 0)
            return $this->view(['error'=>$errors],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        if($Event->getPictureFile()){
            try{
                $files = FileController::upload($Event->getPictureFile(),Event::DEF_PICTURE_FOLDER,File::PIC_TYPE);
                $Event->setPicture(array_shift($files));
            } catch (FileException $e){
                return $this->view(['error'=>[["property_path"=>'pictureFile','message'=>$e->getMessage()]]],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');
            }
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($Event->getPicture());
        $manager->persist($Event);
        $manager->flush();

        return $this->view(['uni_event'=>$Event],Error::SUCCESS_POST_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Update event element",
     *  input="UniversityBundle\Form\Type\EventType"
     * )
     * @Annotations\Put("/api/uni_events/{id}");
     * @Security("is_granted('ROLE_UNI_EVENT_UPDATE')")
     */
    public function putEvent(Request $request,$id=0)
    {
        /**
         * @var Event $event
         */
        $event = $this->getDoctrine()->getRepository('UniversityBundle:Event')->find($id);
        if(!$event)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');


        $form = $this->createForm(new EventType(),$event,array('method' => 'PUT'))
            ->handleRequest($request);
        $event = $form->getData();
        $event->setSections($this->checkSectionArray($event->getSections(),'UniversityBundle:Section'));
        $errors = $this->get('validator')->validate($event);
        if (count($errors) > 0)
            return $this->view(['error'=>$errors],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($event);
        $manager->flush();

        return $this->view(['uni_event'=>$event],Error::SUCCESS_PUT_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Delete event element"
     * )
     * @Annotations\Delete("/api/uni_events/{id}");
     * @Security("is_granted('ROLE_UNI_EVENT_DELETE')")
     */
    public function deleteEvent(Request $request,$id=0)
    {
        /**
         * @var Event $article
         */
        $event = $this->getDoctrine()->getRepository('UniversityBundle:Event')->find($id);
        if(!$event)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $manager = $this->getDoctrine()->getManager();

        if($event->getPicture()){
            $manager->remove($event->getPicture()->deleteFile());
        }

        $manager->remove($event);
        $manager->flush();

        return $this->view(['uni_event'=>$event],Error::SUCCESS_DELETE_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Get event elements"
     * )
     * @Annotations\Get("/api/uni_events");
     * @Annotations\QueryParam(name="event[id]", description="element object")
     * @Annotations\QueryParam(name="event[title]", description="element title")
     * @Annotations\QueryParam(name="event[date]", description="element date")
     * @Annotations\QueryParam(name="event[tags]", description="element tags")
     * @Annotations\QueryParam(name="event[text]", description="element text")
     * @Annotations\QueryParam(name="event[description]", description="element description")
     * @Annotations\QueryParam(name="event[duration]", description="element duration")
     * @Annotations\QueryParam(name="event[sections]", description="element sections")
     * @Annotations\QueryParam(name="_sort", default={"id":"ASC"})
     * @Annotations\QueryParam(name="_limit",  requirements="\d+", nullable=true, strict=true)
     * @Annotations\QueryParam(name="_offset", requirements="\d+", nullable=true, strict=true)
     */
    public function getEventList(Request $request)
    {
        $arr = $request->query->all();
        return $this->view(['uni_events'=>$this->matching('event','UniversityBundle:Event', $arr)],Error::SUCCESS_GET_CODE)
            ->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Get event element"
     * )
     * @Annotations\Get("/api/uni_events/{id}");
     */
    public function getEvents(Request $request,$id=0)
    {
        /**
         * @var Event $event
         */
        $event = $this->getDoctrine()->getRepository('UniversityBundle:Event')->find($id);
        if(!$event)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        return $this->view(['uni_event'=>$event],Error::SUCCESS_GET_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Delete event element picture"
     * )
     * @Annotations\Delete("/api/uni_events/{id}/files/{file_id}");
     * @Security("is_granted('ROLE_UNI_EVENT_UPDATE')")
     */
    public function deleteEventsPicture(Request $request,$id=0,$file_id=0)
    {
        /**
         * @var Event $Event
         */
        $Event = $this->getDoctrine()->getRepository('UniversityBundle:Event')->find($id);
        if(!$Event || !$Event->getPicture() || $Event->getPicture()->getId() != $file_id)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($Event->getPicture()->deleteFile());
        $manager->flush();

        return $this->view(['uni_event'=>$Event],Error::SUCCESS_DELETE_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Update event element picture",
     *  input="UniversityBundle\Form\Type\EventPictureType"
     * )
     * @Annotations\Post("/api/uni_events/{id}/files");
     * @Security("is_granted('ROLE_UNI_EVENT_UPDATE')")
     */
    public function postEventsPicture(Request $request,$id=0)
    {
        /**
         * @var Event $Event
         */
        $Event = $this->getDoctrine()->getRepository('UniversityBundle:Event')->find($id);
        if(!$Event)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $form = $this->createForm(new EventPictureType(),$Event)
            ->handleRequest($request);
        $Event = $form->getData();
        if(!$Event->getPictureFile())
            return $this->view(['error'=>[["property_path"=>'pictureFile','message'=>'Значение не может быть пустым']]],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        //check file
        try{
            $files = FileController::upload($Event->getPictureFile(),Event::DEF_PICTURE_FOLDER,File::PIC_TYPE);
        } catch (FileException $e){
            return $this->view(['error'=>[["property_path"=>'pictureFile','message'=>$e->getMessage()]]],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');
        }
        $manager = $this->getDoctrine()->getManager();

        //delete old file
        if($Event->getPicture()){
            $manager->remove($Event->getPicture()->deleteFile());
        }

        if($pic = array_shift($files))
            $manager->persist($pic);

        $Event->setPicture($pic);
        $manager->persist($Event);
        $manager->flush();

        return $this->view(['uni_event'=>$Event],Error::SUCCESS_POST_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }
}