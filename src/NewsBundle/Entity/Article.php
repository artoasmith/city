<?php

namespace NewsBundle\Entity;

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
 * @ORM\Table(name="news_article")
 * @ORM\Entity(repositoryClass="NewsBundle\Repository\ArticleRepository")
 */
class Article
{
    const DEF_PICTURE_FOLDER = 'images/newsArticle';
    const DEF_FILE_FOLDER = 'files/newsArticle';

    const ONE = 'newsArticle';
    const MANY = 'newsArticles';

    /**
     * @var int
     * @Serializer\Groups({"list", "details"})
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Serializer\Groups({"list", "details"})
     * @ORM\Column(name="title", type="string", length=2048)
     */
    private $title;

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
     * @var bool
     * @Serializer\Groups({"list", "details"})
     * @ORM\Column(name="hasVideo", type="boolean", nullable=true)
     */
    private $hasVideo = false;

    /**
     * @var bool
     * @Serializer\Groups({"list", "details"})
     * @ORM\Column(name="hasImg", type="boolean", nullable=true)
     */
    private $hasImg = false;

    /**
     * @Serializer\Exclude()
     * @Assert\File(maxSize="10000000")
     */
    private $pictureFile;

    /**
     * @var \DateTime
     * @Assert\NotBlank()
     * @Serializer\Groups({"list", "details"})
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var array
     * @Serializer\Groups({"details"})
     * @ORM\Column(name="tags", type="array", nullable=true)
     */
    private $tags;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Serializer\Groups({"details"})
     * @ORM\Column(name="text", type="text")
     */
    private $text;

    /**
     * @var array
     * @Assert\NotBlank()
     * @Serializer\Groups({"details"})
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
     * @var string
     * @Assert\NotBlank()
     * @Serializer\Groups({"list", "details"})
     * @ORM\Column(name="author", type="string", length=255, nullable=true)
     */
    private $author;

    /**
     * @var string
     * @Serializer\Groups({"list", "details"})
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     * @Serializer\Groups({"details"})
     * @ORM\Column(name="metaTitle", type="string", length=2048, nullable=true)
     */
    private $metaTitle;

    /**
     * @var string
     * @Serializer\Groups({"details"})
     * @ORM\Column(name="metaDescription", type="text", nullable=true)
     */
    private $metaDescription;

    /**
     * @var string
     * @Serializer\Groups({"details"})
     * @ORM\Column(name="metaKeyWords", type="text", nullable=true)
     */
    private $metaKeyWords;

    /**
     * @var integer
     * @Serializer\Groups({"list", "details"})
     * @ORM\Column(name="views", type="integer", nullable=true)
     */
    private $views;

    /**
     * @var array
     */
    private $files;

    /**
     * @var array
     * @Serializer\SerializedName("files")
     * @Serializer\Groups({"details"})
     * @ORM\Column(name="files", type="array", nullable=true)
     */
    private $filesArray;

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
     * Set date
     *
     * @param \DateTime $date
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
     * Set tags
     *
     * @param array $tags
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
     * Set text
     *
     * @param string $text
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
     * Set sections
     *
     * @param array $sections
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
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("picture")
     * @Serializer\Groups({"list", "details"})
     * @return string
     */
    public function getPictureId()
    {
        return ($this->picture?$this->picture->getId():false);
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("pictureUrl")
     * @Serializer\Groups({"list", "details"})
     * @return string
     */
    public function getPictureUrl()
    {
        if(!$this->picture)
            return false;
        return $this->picture->getUrl();
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
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("commentsCount")
     * @Serializer\Groups({"list", "details"})
     * @return string
     */
    public function getCommentsCount()
    {
        if(!$this->commentPage)
            return 0;
        return intval($this->commentPage->getCommentsCount());
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
     */
    public function setPictureFile($pictureFile)
    {
        $this->pictureFile = $pictureFile;
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
     * @Serializer\Groups({"details"})
     * @return int|boolean
     */
    public function getUserId()
    {
        return ($this->user?$this->user->getId():false);
    }

    /**
     * Set hasVideo
     *
     * @param boolean $hasVideo
     * @return Article
     */
    public function setHasVideo($hasVideo)
    {
        $this->hasVideo = $hasVideo;

        return $this;
    }

    /**
     * Get hasVideo
     *
     * @return boolean
     */
    public function getHasVideo()
    {
        return $this->hasVideo;
    }

    /**
     * Set hasImg
     *
     * @param boolean $hasImg
     * @return Article
     */
    public function setHasImg($hasImg)
    {
        $this->hasImg = $hasImg;

        return $this;
    }

    /**
     * Get hasImg
     *
     * @return boolean
     */
    public function getHasImg()
    {
        return $this->hasImg;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Article
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
     * Set metaTitle
     *
     * @param string $metaTitle
     * @return Article
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    /**
     * Get metaTitle
     *
     * @return string
     */
    public function getMetaTitle()
    {
        return ($this->metaTitle?$this->metaTitle:$this->title);
    }

    /**
     * Set metaDescription
     *
     * @param string $metaDescription
     * @return Article
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    /**
     * Get metaDescription
     *
     * @return string
     */
    public function getMetaDescription()
    {
        return ($this->metaDescription?$this->metaDescription:$this->description);
    }


    /**
     * Set metaKeyWords
     *
     * @param string $metaKeyWords
     * @return Article
     */
    public function setMetaKeyWords($metaKeyWords)
    {
        $this->metaKeyWords = $metaKeyWords;

        return $this;
    }

    /**
     * Get metaKeyWords
     *
     * @return string
     */
    public function getMetaKeyWords()
    {
        return $this->metaKeyWords;
    }

    /**
     * Set views
     *
     * @param integer $views
     * @return Article
     */
    public function setViews($views)
    {
        $this->views = $views;

        return $this;
    }

    /**
     * Get views
     *
     * @return integer
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * Set files
     *
     * @param array $files
     * @return Article
     */
    public function setFiles($files)
    {
        $this->files = $files;

        return $this;
    }

    /**
     * Get files
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Set filesArray
     *
     * @param array $filesArray
     * @return Article
     */
    public function setFilesArray($filesArray)
    {
        $this->filesArray = $filesArray;

        return $this;
    }

    /**
     * Get filesArray
     *
     * @return array
     */
    public function getFilesArray()
    {
        return $this->filesArray;
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
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if($this->sections)
            $this->sections = array_filter($this->sections);

        if($this->sections && !empty($this->sections)) {
            $em = NewsBundle::getContainer()->get('doctrine')->getManager();
            $stmt = $em->getConnection()
               ->prepare(
                   sprintf('
                        SELECT id FROM `news_section`
                        WHERE id IN (%s)
                    ',
                       implode(', ',$this->sections)
                   )
               );
            $stmt->execute();
            $ideas = $stmt->fetchAll();

            if(!$ideas) {
                $context->buildViolation('Недопустимое значение')
                    ->atPath('sections')
                    ->addViolation();
            } else {
                $resp = [];
                foreach ($ideas as $element)
                    $resp[] = $element['id'];
                $this->sections = $resp;
            }
        }
    }

    public function checkTextInner(){
        if(!$this->text)
            return;

        if(preg_match("/(<img|<svg)/",$this->text))
            $this->hasImg = true;

        if(preg_match("/(<iframe)/",$this->text))
            $this->hasVideo = true;
    }
}
