<?php

namespace UniversityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * SectionArticle
 *
 * @ORM\Table(name="uni_article_section")
 * @ORM\Entity(repositoryClass="UniversityBundle\Repository\SectionArticleRepository")
 */
class ArticleSection
{
    const ONE = 'uniArticleSection';
    const MANY = 'uniArticleSections';

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
     * @var ArticleSection
     * @Serializer\Exclude()
     * @ORM\ManyToOne(targetEntity="ArticleSection")
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
     * @return ArticleSection
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
     * @param ArticleSection $parentSection
     * @return ArticleSection
     */
    public function setParentSection(ArticleSection $parentSection = null)
    {
        $this->parentSection = $parentSection;

        return $this;
    }

    /**
     * Get parentSection
     *
     * @return ArticleSection
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
     * @return ArticleSection
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

    private function checkSectionStrict(ArticleSection $section,$max_insert_count=20)
    {
        $max_insert_count--;
        if(!$section->getParentSection())
            return true;
        if($max_insert_count == 0)
            return false;
        return $this->checkSectionStrict($section->getParentSection(),$max_insert_count);
    }
}

