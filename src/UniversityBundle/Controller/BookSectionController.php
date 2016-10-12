<?php

namespace UniversityBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use UniversityBundle\Form\Type\BookSectionType;
use UniversityBundle\Entity\BookSection;
use ApiErrorBundle\Entity\Error;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle;
use ApiBundle\Controller\DefaultController as ApiController;

class BookSectionController extends DefaultSectionController
{
    const DEF_ROUTE = 'uniBookSections';

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Create book section",
     *  input="UniversityBundle\Form\Type\BookSectionType"
     * )
     * @Annotations\Post("/api/uniBookSections");
     * @Security("is_granted('ROLE_UNI_SECTION_CREATE')")
     */
    public function postSections(Request $request)
    {
        return $this->postSectionElement($request, new BookSectionType, new BookSection());
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Update book section",
     *  input="UniversityBundle\Form\Type\BookSectionType"
     * )
     * @Annotations\Put("/api/uniBookSections/{id}");
     * @Security("is_granted('ROLE_UNI_SECTION_UPDATE')")
     */
    public function putSections(Request $request,$id=0)
    {
        return $this->putSectionElement($request,new BookSectionType(),$id,'UniversityBundle:BookSection');
    }


    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Delete book section"
     * )
     * @Annotations\Delete("/api/uniBookSections/{id}");
     * @Security("is_granted('ROLE_UNI_SECTION_DELETE')")
     */
    public function deleteSections(Request $request,$id=0)
    {
        return $this->deleteSectionElement($id,'UniversityBundle:BookSection',BookSection::ONE);
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Get book sections"
     * )
     * @Annotations\QueryParam(name="section[title]", description="element title")
     * @Annotations\QueryParam(name="section[parentSection]", description="element parent section")
     * @Annotations\QueryParam(name="_sort", default={"id":"ASC"})
     * @Annotations\QueryParam(name="_limit",  requirements="\d+", nullable=true, strict=true)
     * @Annotations\QueryParam(name="_offset", requirements="\d+", nullable=true, strict=true)
     *
     * @Annotations\Get("/api/uniBookSections")
     */
    public function getSections(Request $request)
    {
        $arr = $request->query->all();
        return $this->view([BookSection::MANY=>$this->matching('section','UniversityBundle:BookSection', $arr)],Error::SUCCESS_GET_CODE)
            ->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Get article section"
     * )
     * @Annotations\Get("/api/uniBookSections/{id}");
     */
    public function getSectionElement(Request $request,$id=0)
    {
        $Section = $this->getDoctrine()->getRepository('UniversityBundle:BookSection')->find($id);
        if(!$Section)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        return $this->view([BookSection::ONE=>$Section],Error::SUCCESS_GET_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

}