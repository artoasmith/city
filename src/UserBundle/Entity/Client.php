<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;


/**
 * @ORM\Entity
 * @ExclusionPolicy("all")
 */
class Client
{
    /**
     * @var int
     * @Expose()
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     * @Expose()
     *
     * @ORM\Column(name="date", type="datetime",nullable=false)
     */
    private $date;

    /**
     * @var User
     *
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="id",nullable=false,onDelete="CASCADE")
     * })
     */
    private $user;

    /**
     * @var string
     * @Expose()
     *
     * @ORM\Column(name="secret", type="string", length=255, nullable=false)
     */
    private $secret;

    /**
     * @ORM\OneToMany(targetEntity="AccessToken", mappedBy="client",cascade={"persist"},orphanRemoval=true)
     */
    protected $accessToken;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->accessToken = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     *
     * @return Client
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
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     * @return Client
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
     * Set secret
     *
     * @param string $secret
     *
     * @return Client
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * Get secret
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Add accessToken
     *
     * @param \UserBundle\Entity\AccessToken $accessToken
     * @return Client
     */
    public function addAccessToken(\UserBundle\Entity\AccessToken $accessToken)
    {
        $accessToken->setClient($this);
        $this->accessToken[] = $accessToken;

        return $this;
    }

    /**
     * Remove accessToken
     *
     * @param \UserBundle\Entity\AccessToken $accessToken
     */
    public function removeAccessToken(\UserBundle\Entity\AccessToken $accessToken)
    {
        $this->accessToken->removeElement($accessToken);
    }

    /**
     * Get accessToken
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }
}