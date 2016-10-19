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
         * @var UploadedFile $f
         */
        foreach ($files as $f){
            if($f->getError())
                throw new FileException('Загруженный файл "'.$f->getClientOriginalName().'"слишком большой. Пожалуйста, попробуйте загрузить файл меньшего размера.');
        }
        /**
         * @var UploadedFile $file
         */
        foreach ($files as $file){
            $expansion = DefaultController::getExpansion($file->getMimeType());

            if(!$expansion || ($expectedType && $expectedType != $expansion))
                throw  new FileException('Недопустимый тип');
        }

        $res = [];
        $folder = sprintf('upload/%s/',$folder);
        $folderPath = str_replace('//','/',sprintf('%s/../../../web/%s',__DIR__,$folder));
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
            $file->move($folderPath, $filename);

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
            File::VIDEO_TYPE=>[
                'video/msvideo',
                'video/avi',
                'video/x-msvideo',
                'video/mpeg',
                'video/quicktime'
            ],
            File::FILE_TYPE => [
                'application/pdf',
                'application/msword',
                'application/x-excel',
                'application/excel',
                'application/vnd.ms-excel',
                'application/x-msexcel',
                'application/rtf',
                'application/x-rtf',
                'text/richtext',
                'text/plain',
                'powerpoint',
                'image/vnd.djvu',
                'image/x-djvu',
                'application/mspowerpoint',
                'application/vnd.ms-powerpoint',
                'model/x-pov',
                'application/vnd.ms-office',
                'application/mspowerpoint',
                'application/vnd.ms-powerpoint',
                'application/mspowerpoint',
                'application/powerpoint',
                'application/vnd.ms-powerpoint',
                'application/x-mspowerpoint',
                'application/mspowerpoint',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.template'
            ],
            File::PDF_TYPE => [
                'application/pdf'
            ]
        ];

        foreach ($types as $key=>$typesArray){
            if(in_array($type,$typesArray))
                return $key;
        }
        return false;
    }


}
