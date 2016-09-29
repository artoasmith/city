<?php

namespace CommentsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use UserBundle\Entity\User;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Comment
 *
 * @ORM\Table(name="cm_comment")
 * @ORM\Entity(repositoryClass="CommentsBundle\Repository\CommentRepository")
 */
class Comment
{
    const PUT_ELEMENT_TIME = 300; //5 min
    const DELETE_ELEMENT_TIME = 300; //5 min

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var Page
     * @Serializer\Exclude()
     * @ORM\ManyToOne(targetEntity="Page")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="page", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    private $page;

    /**
     * @var Comment
     * @Serializer\Exclude()
     * @ORM\ManyToOne(targetEntity="Comment")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parentComment", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    private $parentComment;

    /**
     * @var string
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="text", type="text")
     */
    private $text;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    private $position;

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
     * @ORM\Column(name="hasChild", type="boolean")
     */
    private $hasChild = false;

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
     * Set date
     *
     * @param \DateTime $date
     * @return Comment
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
     * Set page
     *
     * @param Page $page
     * @return Comment
     */
    public function setPage(Page $page = null)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get page
     *
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set parentComment
     *
     * @param Comment $parentComment
     * @return Comment
     */
    public function setParentComment(Comment $parentComment = null)
    {
        $this->parentComment = $parentComment;

        return $this;
    }

    /**
     * Get parentComment
     *
     * @return Comment
     */
    public function getParentComment()
    {
        return $this->parentComment;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Comment
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
     * Set position
     *
     * @param integer $position
     * @return Comment
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     * @return Comment
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
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("page")
     * @return int|boolean
     */
    public function getPageId()
    {
        return ($this->page?$this->page->getId():false);
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("parentComment")
     * @return int|boolean
     */
    public function getParentCommentId()
    {
        return ($this->parentComment?$this->parentComment->getId():false) ;
    }

    /**
     * Set hasChild
     *
     * @param boolean $hasChild
     * @return Comment
     */
    public function setHasChild($hasChild = null)
    {
        $this->hasChild = $hasChild;
        return $this;
    }

    /**
     * Get hasChild
     *
     * @return boolean
     */
    public function isHasChild()
    {
        return $this->hasChild;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if($this->parentComment && (!$this->parentComment->getPage() || !$this->getPage() || $this->parentComment->getPage()->getId() != $this->getPage()->getId())) {
            $context->buildViolation('Недопустимое значение')
                ->atPath('page')
                ->addViolation();

        }
    }
}
