<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 28.09.16
 * Time: 10:41
 */

namespace NewsBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use NewsBundle\Entity\Section;
use ApiErrorBundle\Entity\Error;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations;
use NewsBundle\Form\Type\SectionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle;
use ApiBundle\Controller\DefaultController as ApiController;

class SectionController extends ApiController
{
    const DEF_ROUTE = 'newsSections';

    /**
     * @ApiDoc(
     *  section="News",
     *  resource=true,
     *  description="Create article section",
     *  input="NewsBundle\Form\Type\SectionType"
     * )
     * @Annotations\Post("/api/newsSections");
     * @Security("is_granted('ROLE_NEWS_SECTION_CREATE')")
     */
    public function postSections(Request $request)
    {
        $form = $this->createForm(new SectionType(),new Section())
            ->handleRequest($request);
        /**
         * @var Section $section
         */
        $section = $form->getData();

        $errors = $this->get('validator')->validate($section);
        if (count($errors) > 0)
            return $this->view(['error'=>$errors],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($section);
        $manager->flush();

        $section->setPosition($section->getId());
        $manager->persist($section);
        $manager->flush();

        return $this->view([Section::ONE=>$section],Error::SUCCESS_POST_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="News",
     *  resource=true,
     *  description="Update article section",
     *  input="NewsBundle\Form\Type\SectionType"
     * )
     * @Annotations\Put("/api/newsSections/{id}");
     * @Security("is_granted('ROLE_NEWS_SECTION_UPDATE')")
     */
    public function putSections(Request $request,$id=0)
    {
        /**
         * @var Section $section
         */
        $section = $this->getDoctrine()->getRepository('NewsBundle:Section')->find($id);
        if(!$section)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');


        $form = $this->createForm(new SectionType(),$section,array('method' => 'PUT'))
            ->handleRequest($request);
        $section = $form->getData();

        $errors = $this->get('validator')->validate($section);
        if (count($errors) > 0)
            return $this->view(['error'=>$errors],Error::FORM_ERROR_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($section);
        $manager->flush();

        return $this->view([Section::ONE=>$section],Error::SUCCESS_PUT_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }


    /**
     * @ApiDoc(
     *  section="News",
     *  resource=true,
     *  description="Delete article section"
     * )
     * @Annotations\Delete("/api/newsSections/{id}");
     * @Security("is_granted('ROLE_NEWS_SECTION_DELETE')")
     */
    public function deleteSections(Request $request,$id=0)
    {
        /**
         * @var Section $section
         */
        $section = $this->getDoctrine()->getRepository('NewsBundle:Section')->find($id);
        if(!$section)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($section);
        $manager->flush();

        return $this->view([Section::ONE=>$section],Error::SUCCESS_DELETE_CODE)->setTemplate('NewsBundle:Default:section.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="News",
     *  resource=true,
     *  description="Get article sections"
     * )
     * @Annotations\QueryParam(name="section[title]", description="element title")
     * @Annotations\QueryParam(name="section[parentSection]", description="element parent section")
     * @Annotations\QueryParam(name="_sort", default={"id":"ASC"})
     * @Annotations\QueryParam(name="_limit",  requirements="\d+", nullable=true, strict=true)
     * @Annotations\QueryParam(name="_offset", requirements="\d+", nullable=true, strict=true)
     *
     * @Annotations\Get("/api/newsSections")
     */
    public function getSections(Request $request)
    {
        $arr = $request->query->all();
        return $this->view([Section::MANY=>$this->matching('section','NewsBundle:Section', $arr)],Error::SUCCESS_GET_CODE)
            ->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="News",
     *  resource=true,
     *  description="Get article element"
     * )
     * @Annotations\Get("/api/newsSections/{id}");
     */
    public function getArticles(Request $request,$id=0)
    {
        /**
         * @var Section $article
         */
        $Section = $this->getDoctrine()->getRepository('NewsBundle:Section')->find($id);
        if(!$Section)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        return $this->view([Section::ONE=>$Section],Error::SUCCESS_GET_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }
}