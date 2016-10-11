<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use UserBundle\Entity\User;
use FileBundle\Entity\File;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FileBundle\Controller\DefaultController as FileController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Gregwar\ImageBundle\Services\ImageHandling;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Value;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use ApiErrorBundle\Entity\Error;
use ApiBundle\Entity\Tag;

class DefaultController extends FOSRestController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @POST("/api")
     * @Security("is_granted('ROLE_USER')")
     */
    public function getDemosAction(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $data = array("hello" => $user);

        $view = $this->view($data);
        return $this->handleView($view);

        $files = $request->files->get('key');
        try{
            $files = FileController::upload($files,'images');
        } catch (FileException $e){
            $view = $this->view(['error'=>$e->getMessage()],422);
            return $this->handleView($view);
        }

        /**
         * @var ImageHandling $imageHandling
         * @var File $file
         */
        $imageHandling = $this->get('image.handling');
        foreach ($files as $file){
            $imageHandling->open($file->getFile())
                          ->grayscale()
                          ->rotate(12)
                          ->save(sprintf('%s/%d/%s',$file->getFolder(),$file->getId(),$file->getSourse()));
            $file->deleteFile();
        }
        $data = array("hello" => $user);

        $view = $this->view($data);
        return $this->handleView($view);
    }

    public function matching($key='', $repository='', array $fields = array(), $requestSpecParams=[])
    {
        /**
         * @var EntityRepository $repo
         */
        $repo = $this->getDoctrine()->getRepository($repository);
        if(!$repo)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $arrayCallBack = [
            'integer' => function($a){
                return intval($a);
            },
            'string' => function($a){
                $a = strval((is_array($a)?'':$a));
                return str_replace(['\'','"','`','%','_',';'],['_','_','_','\%','\_'],$a);
            },
            'datetime' => function($a){
                $a = strval((is_array($a)?'':$a));
                $time = strtotime($a);
                if(!$time)
                    return false;
                return date('Y-m-d',$time);
            },
            'array' => function($a){
                $a = strval((is_array($a)?'':$a));
                $a = str_replace(['\'','"','`','%','_',';'],['_','_','_','\%','\_'],$a);
                return "\"$a\"";
            },
            '_response' => function($a){
                return $a['id'];
            },
            '_resort' => function($a){
                return is_object($a);
            }
        ];

        //entity fields set
        $fieldSet = array_merge(
            $this->getDoctrine()->getManager()->getClassMetadata($repo->getClassName())->fieldMappings,
            $this->getDoctrine()->getManager()->getClassMetadata($repo->getClassName())->associationMappings
        );

        $params = [' 1'];
        $tableAlt = $this->getDoctrine()->getManager()->getClassMetadata($repo->getClassName())->table['name'];
        $tableAlt = "`$tableAlt`";
        $filterFields = (isset($fields[$key])?$fields[$key]:[]);

        foreach ($fieldSet as $field){

            if(isset($requestSpecParams[$field['fieldName']])){

                $params[] = sprintf(' %s.`%s` %s',$tableAlt,$field['fieldName'],$requestSpecParams[$field['fieldName']]);
            }

            if(isset($filterFields[$field['fieldName']])){
                $val = false;
                if(!is_array($filterFields[$field['fieldName']]))
                    $filterFields[$field['fieldName']] = [$filterFields[$field['fieldName']]];
                //build query param
                switch ($field['type']){
                    case 2: // entity type from associationMappings
                    case 'integer':
                        //is null check
                        if(
                            (count($filterFields[$field['fieldName']])==1 && empty($filterFields[$field['fieldName']][0]))
                            ||
                            in_array($filterFields[$field['fieldName']][0],['null','Null','NULL'])
                        ){
                            $val = sprintf(' %s.%s IS NULL',$tableAlt,$field['fieldName']);
                            break;
                        }
                        $val = array_map($arrayCallBack['integer'],$filterFields[$field['fieldName']]);
                        $val = array_unique($val);
                        $val = sprintf(' %s.%s IN (%s)',$tableAlt,$field['fieldName'],implode(', ',$val));
                        break;
                    case 'array':
                    case 'text':
                    case 'datetime':
                    case 'string':
                        $val = array_map($arrayCallBack[$field['type']],$filterFields[$field['fieldName']]);
                        $val = array_filter($val);
                        $val = array_unique($val);
                        foreach ($val as $key=>$elem){
                            $val[$key] = " LOWER({$tableAlt}.`{$field['fieldName']}`) LIKE LOWER('%{$elem}%')";
                        }
                        if($val)
                            $val = sprintf(" (%s)",implode(' OR',$val));
                        break;
                }

                if($val)
                    $params[] = $val;
            }
        }


        $sort = [];
        if(isset($fields['_sort']) && is_array($fields['_sort'])){
            foreach ($fields['_sort'] as $key=>$param){
                $param = (in_array($param,['DESC','desc','Desc'])?'DESC':'ASC');
                if(isset($fieldSet[$key])){
                    $sort[] = " {$tableAlt}.`{$key}` $param";
                }
            }
        }

        $offset = (isset($fields['_offset']) && $fields['_offset']>0?intval($fields['_offset']):0);
        $limit = (isset($fields['_limit']) && $fields['_limit']>0?intval($fields['_limit']):30);

        //build query
        $query = sprintf(
            'SELECT %1$s.id as id FROM %1$s WHERE %2$s %3$s LIMIT %4$d,%5$d',
            $tableAlt,
            implode(' AND',$params),
            ($sort?implode(' ,',$sort):''),
            $offset,
            $limit
        );

        $stmt = $this->getDoctrine()->getManager()
            ->getConnection()
            ->prepare(
                $query
            );
        $stmt->execute();
        $ideas = $stmt->fetchAll();
        if(!$ideas)
            return [];

        $ideas = array_map($arrayCallBack['_response'],$ideas);
        $resp = $repo->findBy(['id'=>$ideas]);
        if(!$resp)
            return [];

        //resort response
        $ideas_copy = $ideas;
        foreach ($resp as $element){
            if(false !== $key = array_search($element->getId(),$ideas_copy))
                $ideas[$key] = $element;
        }

        return array_filter($ideas,$arrayCallBack['_resort']);
    }

    public function checkSectionArray($array,$repitory)
    {
        $res = [];
        $section = $this->getDoctrine()->getRepository($repitory)->findBy(['id'=>$array]);
        if($section){
            $res = array_map(function($a){return $a->getId();},$section);
        }
        return $res;
    }

    public function tagsManipulator($action,$type,$tagsArray = []){
        if(is_string($tagsArray))
            $tagsArray = [$tagsArray];

        if(!is_array($tagsArray) || empty($tagsArray))
            return false;

        $tagsArray = array_map(function($a){return str_replace(['\'','"','`','%','_',';'],['_','_','_','\%','\_'],mb_strtolower($a));},$tagsArray);

        switch($action){
            case 'add':
                $this->addTags($type,$tagsArray);
                break;
            case 'remove':
                $this->removeTags($type,$tagsArray);
                break;
            default:
                return false;
        }
    }

    private function removeTags($entity,$tags){
        $repo = $this->getDoctrine()->getRepository($entity);
        if(!$repo)
            return false;

        $fields = $this->getDoctrine()->getManager()->getClassMetadata($repo->getClassName())->fieldMappings;
        if(!isset($fields['tags']))
            return false;

        $tableAlt = $this->getDoctrine()->getManager()->getClassMetadata($repo->getClassName())->table['name'];
        $tableAlt = "`$tableAlt`";
        //stabilization value
        $tags = array_values($tags);
        $queryTags = array_map(function ($a){return "%\"$a\"%";},$tags);

        //build query
        $format = " SELECT count(1) as cnt FROM $tableAlt WHERE LOWER(tags) like '%s'";
        $queryParams = array_map(function ($a) use ($format) {return sprintf($format,$a);},$queryTags);

        $query = implode(' UNION',$queryParams);

        $stmt = $this->getDoctrine()->getManager()
            ->getConnection()
            ->prepare(
                $query
            );
        $stmt->execute();
        $ideas = $stmt->fetchAll();
        //unset existing tags in model
        if($ideas){
            foreach ($ideas as $k=>$count){
                if($count['cnt'] > 0){
                    unset($tags[$k]);
                }
            }
        }

        if($tags){
            $tags = $this->getDoctrine()->getRepository('ApiBundle:Tag')->findBy(['entity'=>$entity,'title'=>$tags]);
            if($tags){
                $manager = $this->getDoctrine()->getManager();
                foreach ($tags as $tag){
                    $manager->remove($tag);
                }
                $manager->flush();
            }
        }
    }

    private function addTags($entity,$tags){
        $queryParam = array_map(function($a){return " t.title LIKE '$a'";},$tags);
        $query = sprintf("SELECT t.title as tag FROM tag as t WHERE t.`entity` LIKE '%s' AND (%s) GROUP BY t.title",$entity,implode(' OR',$queryParam));

        $stmt = $this->getDoctrine()->getManager()
            ->getConnection()
            ->prepare(
                $query
            );
        $stmt->execute();
        $ideas = $stmt->fetchAll();

        if($ideas){
            foreach ($ideas as $tag){
                $key = array_search($tag['tag'],$tags);
                if($key !== false)
                    unset($tags[$key]);
            }
        }

        if($tags){
            $manager = $this->getDoctrine()->getManager();
            foreach ($tags as $tag){
                $tagElement = new Tag();
                $tagElement->setEntity($entity)
                           ->setTitle($tag);
                $manager->persist($tagElement);
            }
            $manager->flush();
        }
    }
}
