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
use ApiBundle\Classes\MatchPriorItem;
use Doctrine\DBAL\Exception\SyntaxErrorException;

class DefaultController extends FOSRestController
{
    /**
     * @return array
     */
    public function priorityMatching($key='', $repository='', $fields = [], $priorMap=[]){
        $resp = [
            'items'=>[],
            'meta'=>[
                'total'=>0,
                'selected'=>0
            ]
        ];

        /**
         * @var EntityRepository $repo
         */
        $repo = $this->getDoctrine()->getRepository($repository);
        if(!$repo)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $arrayCallBack = $this->getCallBacks();
        //entity fields set
        $fieldSet = array_merge(
            $this->getDoctrine()->getManager()->getClassMetadata($repo->getClassName())->fieldMappings,
            $this->getDoctrine()->getManager()->getClassMetadata($repo->getClassName())->associationMappings
        );

        $tableAlt = $this->getDoctrine()->getManager()->getClassMetadata($repo->getClassName())->table['name'];
        $tableAlt = "`$tableAlt`";
        $filterFields = (isset($fields[$key])?$fields[$key]:[]);

        $params = [' 1'];
        $totalParams = [' 1'];

        $select = [
            'main'=>["$tableAlt.id as id"],
            'total'=>['COUNT(1) as cnt'],
            'spec'=>['COUNT(1) as cnt']
        ];

        $priceFieldTitle = 'priceFieldTitle2';
        $price = [0];

        $laxParameters = [];
        if(isset($fields['_laxParameters'])){
            if(is_array($fields['_laxParameters']))
                $laxParameters = $fields['_laxParameters'];
            elseif (is_string($fields['_laxParameters']))
                $laxParameters = [$fields['_laxParameters']];
        }

        foreach ($fieldSet as $field){
            if(isset($filterFields[$field['fieldName']])){
                /**
                 * @var MatchPriorItem $priorElement
                 */
                $priorElement = (isset($priorMap[$field['fieldName']])?$priorMap[$field['fieldName']]:false);
                $val = false;
                if(!is_array($filterFields[$field['fieldName']]))
                    $filterFields[$field['fieldName']] = [$filterFields[$field['fieldName']]];
                //build query param
                switch ($field['type']){
                    case 'integer':
                        //sector check
                        if($check = $this->checkSector($filterFields[$field['fieldName']],$arrayCallBack['integer'])){
                            $val = sprintf($check,$tableAlt,$field['fieldName']);
                            if($priorElement){
                                $price = array_merge($price,$priorElement->buildPriceString($val));
                            }
                            break;
                        }
                    case 2: // entity type from associationMappings
                        if(
                            (count($filterFields[$field['fieldName']])==1 && empty($filterFields[$field['fieldName']][0]))
                            ||
                            in_array($filterFields[$field['fieldName']][0],['null','Null','NULL'])
                        ){
                            $val = sprintf(' %s.%s IS NULL',$tableAlt,$field['fieldName']);
                            if($priorElement)
                                $price = array_merge($price,$priorElement->buildPriceString($val));
                            break;
                        }
                        $val = array_map($arrayCallBack['integer'],$filterFields[$field['fieldName']]);
                        $val = array_unique($val);
                        if($priorElement)
                            $price = array_merge($price,$priorElement->buildPriceString($val));
                        $val = sprintf(' %s.%s IN (%s)',$tableAlt,$field['fieldName'],implode(', ',$val));
                        break;
                    case 'datetime':
                        if($check = $this->checkSector($filterFields[$field['fieldName']],$arrayCallBack['datetimefull'])){
                            $val = sprintf($check,$tableAlt,$field['fieldName']);
                            if($priorElement)
                                $price = array_merge($price,$priorElement->buildPriceString($val));
                            break;
                        }
                    case 'array':
                    case 'text':
                    case 'string':
                        $val = array_map($arrayCallBack[$field['type']],$filterFields[$field['fieldName']]);
                        $val = array_filter($val);
                        $val = array_unique($val);
                        foreach ($val as $key=>$elem){
                            $val[$key] = " LOWER({$tableAlt}.`{$field['fieldName']}`) LIKE LOWER({$elem})";
                        }
                        if($val){
                            if($priorElement)
                                $price = array_merge($price,$priorElement->buildPriceString($val));
                            $val = sprintf(" (%s)",implode(' OR',$val));
                        }
                        break;
                }

                if($val){
                    if(!in_array($field['fieldName'],$laxParameters) || !$priorElement){
                        $params[] = $val;
                        if($priorElement && $priorElement->getTotalVariable())
                            $totalParams[] = $val;
                    }
                }
            }
        }

        $sort = [];
        if(count($price)>1){
            $sort[] = " $priceFieldTitle DESC";
        }

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

        //price str
        $select['main'][] = implode('+',$price)." AS $priceFieldTitle";

        //build query
        $query = sprintf(
            'SELECT %s FROM %s WHERE %s %s LIMIT %d,%d',
            implode(', ',$select['main']),
            $tableAlt,
            implode(' AND',$params),
            ($sort?'ORDER BY '.implode(' ,',$sort):''),
            $offset,
            $limit
        );

        try {
            $stmt = $this->getDoctrine()->getManager()
                ->getConnection()
                ->prepare(
                    $query
                );
            $stmt->execute();
            $ideas = $stmt->fetchAll();
        }catch (SyntaxErrorException $e){
            return $resp;
        }

        if(!$ideas)
            return $resp;

        $ideas = array_map($arrayCallBack['_response'],$ideas);
        $obj = $repo->findBy(['id'=>$ideas]);

        //resort response
        $ideas_copy = $ideas;
        foreach ($obj as $element){
            if(false !== $key = array_search($element->getId(),$ideas_copy))
                $ideas[$key] = $element;
        }

        $obj = array_filter($ideas,$arrayCallBack['_resort']);
        $resp['items'] = $obj;

        //total count
        $queryTotal = sprintf(
            'SELECT %s FROM %s WHERE %s ',
            implode(', ',$select['total']),
            $tableAlt,
            implode(' AND',$totalParams)
        );

        $ideasTotal = null;
        try {
            $stmt = $this->getDoctrine()->getManager()
                ->getConnection()
                ->prepare(
                    $queryTotal
                );
            $stmt->execute();
            $ideasTotal = $stmt->fetchAll();
        }catch (SyntaxErrorException $e){}
        $resp['meta']['total'] = ($ideasTotal && isset($ideasTotal[0]) && isset($ideasTotal[0]['cnt'])?intval($ideasTotal[0]['cnt']):0);

        //selected count
        $selectedTotal = sprintf(
            'SELECT %s FROM %s WHERE %s ',
            implode(', ',$select['spec']),
            $tableAlt,
            implode(' AND',$params)
        );
        $ideasSpec = null;
        try {
            $stmt = $this->getDoctrine()->getManager()
                ->getConnection()
                ->prepare(
                    $selectedTotal
                );
            $stmt->execute();
            $ideasSpec = $stmt->fetchAll();
        }catch (SyntaxErrorException $e){}
        $resp['meta']['selected'] = ($ideasSpec && isset($ideasSpec[0]) && isset($ideasSpec[0]['cnt'])?intval($ideasSpec[0]['cnt']):0);

        return $resp;
    }

    private function checkSector($val,$callback){

        if(!is_array($val) || (!isset($val['from']) && !isset($val['to'])))
            return false;

        $from = (isset($var['from'])?$callback($var['from']):null);
        $to   = (isset($var['to'])  ?$callback($var['to'])  :null);

        $f = [];
        if($from){
            $f[] = ' %1$s.%2$2 >= '.$from;
        }
        if($to){
            $f[] = ' %1$s.%2$2 <= '.$to;
        }
        return implode(' AND',$f);
    }

    private function getCallBacks(){
        return [
            'integer' => function($a){
                return intval($a);
            },
            'string' => function($a){
                $a = strval((is_array($a)?'':$a));
                $a = str_replace(['\'','"','`','%','_',';'],['_','_','_','\%','\_'],$a);
                return "'%$a%'";
            },
            'datetimefull'=>function($a){
                $a = strval((is_array($a)?'':$a));
                $time = strtotime($a);
                if(!$time)
                    return false;
                return date("'Y-m-d H:i:s'",$time);
            },
            'datetime' => function($a){
                $a = strval((is_array($a)?'':$a));
                $time = strtotime($a);
                if(!$time)
                    return false;
                return date("'Y-m-d%'",$time);
            },
            'array' => function($a){
                $a = strval((is_array($a)?'':$a));
                $a = str_replace(['\'','"','`','%','_',';'],['_','_','_','\%','\_'],$a);
                return "'%\"$a\"%'";
            },
            'arraySpec'=>function($a){
                $a = strval((is_array($a)?'':$a));
                $a = str_replace(['\'','"','`','%','_',';'],['_','_','_','\%','\_'],$a);
            return "%\"%$a%\"%";
        },
            '_response' => function($a){
                return $a['id'];
            },
            '_resort' => function($a){
                return is_object($a);
            }
        ];
    }

    public function matching($key='', $repository='', array $fields = array(), $requestSpecParams=[], $specCallback=[])
    {
        /**
         * @var EntityRepository $repo
         */
        $repo = $this->getDoctrine()->getRepository($repository);
        if(!$repo)
            return $this->view(['error'=>Error::NOT_FOUNT_TEXT],Error::NOT_FOUND_CODE)->setTemplate('ApiErrorBundle:Default:error.html.twig');

        $arrayCallBack = $this->getCallBacks();

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

                $altCall = $field['type'];
                if(isset($specCallback[$field['fieldName']]) && isset($arrayCallBack[$field['type'].'Spec']))
                    $altCall = $field['type'].'Spec';

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
                        $val = array_map($arrayCallBack[$altCall],$filterFields[$field['fieldName']]);
                        $val = array_filter($val);
                        $val = array_unique($val);
                        foreach ($val as $key=>$elem){
                            $val[$key] = " LOWER({$tableAlt}.`{$field['fieldName']}`) LIKE LOWER({$elem})";
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
            ($sort?'ORDER BY '.implode(' ,',$sort):''),
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
