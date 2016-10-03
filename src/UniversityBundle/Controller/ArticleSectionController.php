<?php

namespace UniversityBundle\Controller;


use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use UniversityBundle\Form\Type\ArticleSectionType;
use UniversityBundle\Entity\ArticleSection;
use ApiErrorBundle\Entity\Error;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle;
use ApiBundle\Controller\DefaultController as ApiController;

class ArticleSectionController extends DefaultSectionController
{
    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Create article section",
     *  input="UniversityBundle\Form\Type\ArticleSectionType"
     * )
     * @Annotations\Post("/api/uni_article_sections");
     * @Security("is_granted('ROLE_UNI_SECTION_CREATE')")
     */
    public function postSections(Request $request)
    {
        return $this->postSectionElement($request, new ArticleSectionType, new ArticleSection());
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Update article section",
     *  input="UniversityBundle\Form\Type\ArticleSectionType"
     * )
     * @Annotations\Put("/api/uni_article_sections/{id}");
     * @Security("is_granted('ROLE_UNI_SECTION_UPDATE')")
     */
    public function putSections(Request $request,$id=0)
    {
        return $this->putSectionElement($request,new ArticleSectionType(),$id,'UniversityBundle:ArticleSection');
    }


    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Delete article section"
     * )
     * @Annotations\Delete("/api/uni_article_sections/{id}");
     * @Security("is_granted('ROLE_UNI_SECTION_DELETE')")
     */
    public function deleteSections(Request $request,$id=0)
    {
        return $this->deleteSectionElement($id,'UniversityBundle:ArticleSection',ArticleSectionType::name);
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Get article sections"
     * )
     * @Annotations\QueryParam(name="section[title]", description="element title")
     * @Annotations\QueryParam(name="section[parentSection]", description="element parent section")
     * @Annotations\QueryParam(name="_sort", default={"id":"ASC"})
     * @Annotations\QueryParam(name="_limit",  requirements="\d+", nullable=true, strict=true)
     * @Annotations\QueryParam(name="_offset", requirements="\d+", nullable=true, strict=true)
     *
     * @Annotations\Get("/api/uni_article_sections")
     */
    public function getSections(Request $request)
    {
        $arr = $request->query->all();
        return $this->view([ArticleSectionType::names=>$this->matching('section','UniversityBundle:ArticleSection', $arr)],Error::SUCCESS_GET_CODE)
            ->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

    /**
     * @ApiDoc(
     *  section="University",
     *  resource=true,
     *  description="Get article section"
     * )
     * @Annotations\Get("/api/uni_article_sections/{id}");
     */
    public function getSectionElement(Request $request,$id=0)
    {
        $Section = $this->getDoctrine()->getRepository('UniversityBundle:ArticleSection')->find($id);
        if(!$Section)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        return $this->view([ArticleSectionType::name=>$Section],Error::SUCCESS_GET_CODE)->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }

}