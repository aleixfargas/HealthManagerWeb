<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Allergies
 *
 * @ORM\Table(name="allergies")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AllergiesRepository")
 */
class Allergies
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
     *
     * @ORM\Column(name="name", type="string", length=45)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="characteristics", type="string", length=255, nullable=true)
     */
    private $characteristics;

    /**
     * @var int
     *
     * @ORM\Column(name="user", type="integer")
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    private $user;
    
    /**
     * Set id
     *
     * @param string $id
     *
     * @return Allergies
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * Set name
     *
     * @param string $name
     *
     * @return Allergies
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set characteristics
     *
     * @param string $characteristics
     *
     * @return Allergies
     */
    public function setCharacteristics($characteristics)
    {
        $this->characteristics = $characteristics;

        return $this;
    }

    /**
     * Get characteristics
     *
     * @return string
     */
    public function getCharacteristics()
    {
        return $this->characteristics;
    }
    
    /**
     * Get user
     *
     * @return user
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }
   
    /**
     * Get user
     *
     * @return user
     */
    public function getUser()
    {
        return $this->user;
    }
}
