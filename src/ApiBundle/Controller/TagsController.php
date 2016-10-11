<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 11.10.16
 * Time: 17:11
 */

namespace ApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use ApiErrorBundle\Entity\Error;

class TagsController extends DefaultController
{
    /**
     * @Annotations\Get("/api/tags");
     * @Annotations\QueryParam(name="entity", description="entity tags")
     * @Annotations\QueryParam(name="q", description="Search tags string")
     */
    public function tagsController(Request $request){
        $array =[
            'newsArticle'=>'NewsBundle:Article',
            'uniArticle'=>'UniversityBundle:Article',
            'uniBook'=>'UniversityBundle:Book',
            'uniEvent'=>'UniversityBundle:Event'
        ];
        $entity = $request->query->get('entity');
        $resp = [];
        if(isset($array[$entity])){
            $q = strval($request->query->get('q'));
            if($q){
                $q = str_replace(['\'','"','`','%','_',';'],['_','_','_','\%','\_'],mb_strtolower($q));
                $repo = $this->getDoctrine()->getRepository('ApiBundle:Tag');
                $query = $repo->createQueryBuilder('a')
                    ->where('a.entity LIKE :entity')
                    ->setParameter('entity', $array[$entity])
                    ->andWhere('a.title LIKE :q')
                    ->setParameter('q',"%$q%")
                    ->getQuery();
                $resp=$query->getResult();
            } else {
                $resp = $this->getDoctrine()->getRepository('ApiBundle:Tag')->findBy(['entity'=>$array[$entity]]);
            }
        }
        return $this->view(['tags'=>$resp],Error::SUCCESS_GET_CODE)
                    ->setTemplate('ApiErrorBundle:Default:unformat.html.twig');
    }
}