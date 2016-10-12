<?php

namespace UniversityBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use UniversityBundle\Form\Type\SectionEventType;
use UniversityBundle\Entity\SectionEvent;
use ApiErrorBundle\Entity\Error;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle;
use ApiBundle\Controller\DefaultController as ApiController;
use UniversityBundle\Entity\ArticleSection;

class DefaultSectionController extends ApiController
{
    public function postSectionElement($request,$form,$object){
        $form = $this->createForm($form,$object)
            ->handleRequest($request);
        /**
         * @var ArticleSection $object
         * @var SectionEventType $form
         */
        $object = $form->getData();

        $errors = $this->get('validator')->validate($object);
        if (count($errors) > 0)
            return $this->view(['error'=>$errors],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($object);
        $manager->flush();

        $object->setPosition($object->getId());
        $manager->persist($object);
        $manager->flush();

        return $this->view([$object::ONE=>$object],Error::SUCCESS_POST_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    public function putSectionElement($request,$form,$id,$repository){
        $section = $this->getDoctrine()->getRepository($repository)->find($id);
        if(!$section)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');


        $form = $this->createForm($form,$section,array('method' => 'PUT'))
            ->handleRequest($request);
        $section = $form->getData();

        $errors = $this->get('validator')->validate($section);
        if (count($errors) > 0)
            return $this->view(['error'=>$errors],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($section);
        $manager->flush();

        return $this->view([$section::ONE=>$section],Error::SUCCESS_PUT_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    public function deleteSectionElement($id,$repository,$key){
        $section = $this->getDoctrine()->getRepository($repository)->find($id);
        if(!$section)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($section);
        $manager->flush();

        return $this->view([$key=>$section],Error::SUCCESS_DELETE_CODE)->setTemplate('NewsBundle:Default:section.html.twig');
    }
}