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
 * Event
 *
 * @ORM\Table(name="uni_event")
 * @ORM\Entity(repositoryClass="UniversityBundle\Repository\EventRepository")
 */
class Event
{
    const ARCHIVE_STATUS = 'archive';
    const DEF_PICTURE_FOLDER = 'images/uniEvent';

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
     * @Assert\NotBlank()
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
     * @Serializer\Exclude()
     * @Assert\File(maxSize="10000000")
     */
    private $pictureFile;

    /**
     * @var \DateTime
     * @Assert\NotBlank()
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     * @Assert\NotBlank()
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
     * @var int
     * @Assert\NotBlank()
     * @ORM\Column(name="duration", type="integer")
     */
    private $duration;

    /**
     * @var array
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=true)
     */
    private $status;

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
     * @var \CommentsBundle\Entity\Page
     * @Serializer\Exclude()
     * @ORM\ManyToOne(targetEntity="\CommentsBundle\Entity\Page")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="commentPage", referencedColumnName="id",onDelete="SET NULL")
     * })
     */
    private $commentPage;

    /**
     * @var array
     * @Assert\NotBlank()
     * @ORM\Column(name="sections", type="array")
     */
    private $sections;

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
     * @return Event
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
     *
     * @return Event
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
     * Set description
     *
     * @param string $description
     *
     * @return Event
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
     * @return Event
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
     * Set duration
     *
     * @param integer $duration
     *
     * @return Event
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Event
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set picture
     *
     * @param \FileBundle\Entity\File $picture
     * @return Event
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
     * Set commentPage
     *
     * @param \CommentsBundle\Entity\Page $commentPage
     * @return Event
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
     * @return mixed
     */
    public function getPictureFile()
    {
        return $this->pictureFile;
    }

    /**
     * @param mixed $pictureFile
     * @return Event
     */
    public function setPictureFile($pictureFile)
    {
        $this->pictureFile = $pictureFile;

        return $this;
    }

    /**
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     * @return Event
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

    /**
     * Set sections
     *
     * @param array $sections
     * @return Event
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
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if($this->date) {
            if($this->date->getTimestamp() < time()) {
                $context->buildViolation('Недопустимое значение')
                    ->atPath('date')
                    ->addViolation();
            }
        }
    }
}

