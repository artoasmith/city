<?php

namespace NewsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Section
 *
 * @ORM\Table(name="news_section")
 * @ORM\Entity(repositoryClass="NewsBundle\Repository\SectionRepository")
 */
class Section
{
    const ONE = 'newsSection';
    const MANY = 'newsSections';

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
     * @Serializer\Groups({"list", "details"})
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
     * @var string
     * @Serializer\Groups({"details"})
     * @ORM\Column(name="metaTitle", type="string", length=2048, nullable=true)
     */
    private $metaTitle;

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
     * @Serializer\Groups({"list", "details"})
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
     * Set metaDescription
     *
     * @param string $metaDescription
     * @return Section
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
        return $this->metaDescription;
    }


    /**
     * Set metaKeyWords
     *
     * @param string $metaKeyWords
     * @return Section
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
     * Set metaTitle
     *
     * @param string $metaTitle
     * @return Section
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
