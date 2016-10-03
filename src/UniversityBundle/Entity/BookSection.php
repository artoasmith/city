<?php

namespace UniversityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * BookSection
 *
 * @ORM\Table(name="uni_book_section")
 * @ORM\Entity(repositoryClass="UniversityBundle\Repository\BookSectionRepository")
 */
class BookSection
{
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
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var BookSection
     * @Serializer\Exclude()
     * @ORM\ManyToOne(targetEntity="BookSection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parentSection", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    private $parentSection;

    /**
     * @var int
     * @Serializer\Exclude()
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    private $position;


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
     * @return BookSection
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
     * Set parentSection
     *
     * @param BookSection $parentSection
     * @return BookSection
     */
    public function setParentSection(BookSection $parentSection = null)
    {
        $this->parentSection = $parentSection;

        return $this;
    }

    /**
     * Get parentSection
     *
     * @return BookSection
     */
    public function getParentSection()
    {
        return $this->parentSection;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("parentSection")
     * @return integer|boolean
     */
    public function getParentSectionId()
    {
        return ($this->parentSection ? $this->parentSection->getId() : false);
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return BookSection
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
}

