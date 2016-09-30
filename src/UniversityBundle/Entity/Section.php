<?php

namespace UniversityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Section
 *
 * @ORM\Table(name="uni_section")
 * @ORM\Entity(repositoryClass="UniversityBundle\Repository\SectionRepository")
 */
class Section
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
     * @var Section
     * @Serializer\Exclude()
     * @ORM\ManyToOne(targetEntity="Section")
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
     * @return Section
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
     * @param Section $parentSection
     * @return Section
     */
    public function setParentSection(Section $parentSection = null)
    {
        $this->parentSection = $parentSection;

        return $this;
    }

    /**
     * Get parentSection
     *
     * @return Section
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
     * @return Section
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
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if($this->parentSection) {
            $checker = $this->checkSectionStrict($this);
            if($checker === false) {
                $context->buildViolation('Недопустимое значение')
                    ->atPath('parentSection')
                    ->addViolation();
            }
        }
    }

    private function checkSectionStrict(Section $section,$max_insert_count=20)
    {
        $max_insert_count--;
        if(!$section->getParentSection())
            return true;
        if($max_insert_count == 0)
            return false;
        return $this->checkSectionStrict($section->getParentSection(),$max_insert_count);
    }
}

