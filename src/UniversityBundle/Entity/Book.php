<?php

namespace UniversityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FileBundle\Entity\File;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use NewsBundle\NewsBundle;

/**
 * Book
 *
 * @ORM\Table(name="uni_book")
 * @ORM\Entity(repositoryClass="UniversityBundle\Repository\BookRepository")
 */
class Book
{
    const DEF_PICTURE_FOLDER = 'images/uniBook';
    const DEF_FILE_FOLDER = 'files/uniBook';
    const ONE = 'uniBook';
    const MANY = 'uniBooks';

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
     * @ORM\Column(name="title", type="string", length=2048)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="author", type="string", length=255)
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var array
     *
     * @ORM\Column(name="tags", type="array", nullable=true)
     */
    private $tags;

    /**
     * @var array
     *
     * @ORM\Column(name="sections", type="array")
     */
    private $sections;

    /**
     * @var \FileBundle\Entity\File
     * @Serializer\Exclude()
     * @ORM\ManyToOne(targetEntity="\FileBundle\Entity\File")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="picture", referencedColumnName="id",onDelete="SET NULL")
     * })
     */
    private $picture;

    /**
     * @Serializer\Exclude()
     * @Assert\File(maxSize="10000000")
     */
    private $pictureFile;

    /**
     * @var \FileBundle\Entity\File
     * @Serializer\Exclude()
     * @ORM\ManyToOne(targetEntity="\FileBundle\Entity\File")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="document", referencedColumnName="id",onDelete="SET NULL")
     * })
     */
    private $document;

    /**
     * @Serializer\Exclude()
     * @Assert\File(maxSize="10000000")
     */
    private $documentFile;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Book
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
     * Set author
     *
     * @param string $author
     *
     * @return Book
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Book
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set tags
     *
     * @param array $tags
     *
     * @return Book
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Get tags
     *
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set sections
     *
     * @param array $sections
     *
     * @return Book
     */
    public function setSections($sections)
    {
        $this->sections = $sections;

        return $this;
    }

    /**
     * Get sections
     *
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * Set document
     *
     * @param \FileBundle\Entity\File $document
     * @return Book
     */
    public function setDocument(\FileBundle\Entity\File $document = null)
    {
        if($document->getType() == File::PDF_TYPE)
            $this->document = $document;

        return $this;
    }

    /**
     * Get document
     *
     * @return \FileBundle\Entity\File
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("document")
     * @return string
     */
    public function getDocumentId()
    {
        return ($this->document?$this->document->getId():false);
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("documentUrl")
     * @return string
     */
    public function getDocumentUrl()
    {
        if(!$this->document)
            return false;
        return $this->document->getUrl();
    }


    /**
     * @return mixed
     */
    public function getDocumentFile()
    {
        return $this->documentFile;
    }

    /**
     * @param mixed $documentFile
     * @return Book
     */
    public function setDocumentFile($documentFile)
    {
        $this->documentFile = $documentFile;

        return $this;
    }

    /**
     * Set picture
     *
     * @param \FileBundle\Entity\File $picture
     * @return Book
     */
    public function setPicture(\FileBundle\Entity\File $picture = null)
    {
        if($picture->getType() == File::PIC_TYPE)
            $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture
     *
     * @return \FileBundle\Entity\File
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("picture")
     * @return string
     */
    public function getPictureId()
    {
        return ($this->picture?$this->picture->getId():false);
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("pictureUrl")
     * @return string
     */
    public function getPictureUrl()
    {
        if(!$this->picture)
            return false;
        return $this->picture->getUrl();
    }


    /**
     * @return mixed
     */
    public function getPictureFile()
    {
        return $this->pictureFile;
    }

    /**
     * @param mixed $pictureFile
     * @return Book
     */
    public function setPictureFile($pictureFile)
    {
        $this->pictureFile = $pictureFile;

        return $this;
    }

}

