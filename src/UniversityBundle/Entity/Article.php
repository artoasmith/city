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
 * Article
 *
 * @ORM\Table(name="uni_article")
 * @ORM\Entity(repositoryClass="UniversityBundle\Repository\ArticleRepository")
 */
class Article
{
    const DEF_PICTURE_FOLDER = 'images/uniArticle';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="author", type="string", length=255)
     */
    private $author;

    /**
     * @var \DateTime
     * @Assert\NotBlank()
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="text", type="text")
     */
    private $text;

    /**
     * @var array
     *
     * @ORM\Column(name="tags", type="array", nullable=true)
     */
    private $tags;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="title", type="string", length=2048)
     */
    private $title;

    /**
     * @var array
     * @Assert\NotBlank()
     * @ORM\Column(name="sections", type="array")
     */
    private $sections;

    /**
     * @var \CommentsBundle\Entity\Page
     * @Serializer\Exclude()
     * @ORM\ManyToOne(targetEntity="\CommentsBundle\Entity\Page")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="commentPage", referencedColumnName="id",onDelete="SET NULL")
     * })
     */
    private $commentPage;

    /**
     * @var \UserBundle\Entity\User
     * @Serializer\Exclude()
     * @ORM\ManyToOne(targetEntity="\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="id",onDelete="SET NULL")
     * })
     */
    private $user;

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
     * Set author
     *
     * @param string $author
     *
     * @return Article
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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Article
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
     * Set text
     *
     * @param string $text
     *
     * @return Article
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set tags
     *
     * @param array $tags
     *
     * @return Article
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
     * Set title
     *
     * @param string $title
     *
     * @return Article
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
     * Set sections
     *
     * @param array $sections
     *
     * @return Article
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
     * Set picture
     *
     * @param \FileBundle\Entity\File $picture
     * @return Article
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
     * @return Article
     */
    public function setPictureFile($pictureFile)
    {
        $this->pictureFile = $pictureFile;

        return $this;
    }

    /**
     * Set commentPage
     *
     * @param \CommentsBundle\Entity\Page $commentPage
     * @return Article
     */
    public function setCommentPage(\CommentsBundle\Entity\Page $commentPage = null)
    {
        $this->commentPage = $commentPage;

        return $this;
    }

    /**
     * Get commentPage
     *
     * @return \CommentsBundle\Entity\Page
     */
    public function getCommentPage()
    {
        return $this->commentPage;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("commentPage")
     * @return string
     */
    public function getCommentPageId()
    {
        return ($this->commentPage?$this->commentPage->getId():false);
    }


    /**
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     * @return Article
     */
    public function setUser(\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("user")
     * @return int|boolean
     */
    public function getUserId()
    {
        return ($this->user?$this->user->getId():false);
    }
}

