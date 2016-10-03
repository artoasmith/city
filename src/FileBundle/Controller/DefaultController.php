<?php

namespace FileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use FileBundle\Entity\File;

class DefaultController extends Controller
{
    /**
     * @param $files
     * @param $folder
     * @return array
     */
    static function upload($files,$folder,$expectedType=false){
        if(!$files)
            throw new FileException('Пустые данные');

        if(!is_array($files))
            $files = [$files];

        /**
         * @var UploadedFile $file
         */
        foreach ($files as $file){
            $expansion = DefaultController::getExpansion($file->getMimeType());
            if(!$expansion || ($expectedType && $expectedType != $expansion))
                throw  new FileException('Недопустимый тип');
        }

        $res = [];
        $folder = str_replace('//','/',sprintf('upload/%s/',$folder));
        foreach ($files as $file){
            $expansion = DefaultController::getExpansion($file->getMimeType());

            $format = strtolower(substr($file->getClientOriginalName(), strripos($file->getClientOriginalName(), '.') + 1));
            if ($format == 'jpeg') {
                $format = 'jpg';
            }

            $newName = md5(
                implode(
                    rand(10,99),
                    array(
                        time(),
                        'stuff',
                        'word'
                    )
                )
            );

            $filename = $newName . '.' . $format;
            $file->move($folder, $filename);

            $fileEntity = new File();
            $fileEntity->setDate(new \DateTime())
                       ->setFolder($folder)
                       ->setOriginalName($file->getClientOriginalName())
                       ->setSourse($filename)
                       ->setTitle($file->getClientOriginalName())
                       ->setType($expansion);

            $res[] = $fileEntity;
        }
        return $res;
    }

    /**
     * @param string $type
     * @return bool|string
     */
    static function getExpansion($type){
        $types = [
            File::PIC_TYPE => [
                'image/png',
                'image/jpeg'
            ],
            File::FILE_TYPE => [

            ],
            File::PDF_TYPE => [

            ]
        ];

        foreach ($types as $key=>$typesArray){
            if(in_array($type,$typesArray))
                return $key;
        }
        return false;
    }


}
