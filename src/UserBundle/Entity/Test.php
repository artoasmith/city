<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Test
 *
 * @ORM\Table(name="test")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\TestRepository")
 */
class Test
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
     * @Serializer\SerializedName("bananaCase")
     * @ORM\Column(name="string", type="string", length=255)
     */
    private $string;

    /**
     * @var string
     * @Serializer\Exclude()
     * @ORM\Column(name="integ", type="string", length=255)
     */
    private $integ;


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
     * Set string
     *
     * @param string $string
     * @return Test
     */
    public function setString($string)
    {
        $this->string = $string;

        return $this;
    }

    /**
     * Get string
     *
     * @return string 
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * Set integ
     *
     * @param string $integ
     * @return Test
     */
    public function setInteg($integ)
    {
        $this->integ = $integ;

        return $this;
    }

    /**
     * Get integ
     *
     * @return string 
     */
    public function getInteg()
    {
        return $this->integ;
    }
}
