<?php

namespace FileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * File
 *
 * @ORM\Table(name="file")
 * @ORM\Entity(repositoryClass="FileBundle\Repository\FileRepository")
 */
class File
{
    const PIC_TYPE = 'picture';
    const FILE_TYPE = 'file';
    const PDF_TYPE = 'pdf';

    const FILE_PREFIX = 'http://city.loc/';
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="folder", type="string", length=255)
     */
    private $folder;

    /**
     * @var string
     *
     * @ORM\Column(name="sourse", type="string", length=255)
     */
    private $sourse;

    /**
     * @var string
     *
     * @ORM\Column(name="originalName", type="string", length=255)
     */
    private $originalName;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return File
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return File
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return File
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set folder
     *
     * @param string $folder
     * @return File
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Get folder
     *
     * @return string 
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Set sourse
     *
     * @param string $sourse
     * @return File
     */
    public function setSourse($sourse)
    {
        $this->sourse = $sourse;

        return $this;
    }

    /**
     * Get sourse
     *
     * @return string 
     */
    public function getSourse()
    {
        return $this->sourse;
    }

    /**
     * Set originalName
     *
     * @param string $originalName
     * @return File
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;

        return $this;
    }

    /**
     * Get originalName
     *
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    public function getFile(){
        return $this->getFolder().$this->getSourse();
    }

    public function getUrl(){
        return self::FILE_PREFIX.$this->getFile();
    }

    public function deleteFile(){
        @unlink($this->getFile());
        $dirPath = sprintf('%s/%d/',$this->getFolder(),$this->getId());
        self::deleteDir($dirPath);
        return $this;
    }

    public static function deleteDir($dir){
        $files = glob($dir . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        @rmdir($dir);
    }
}
