<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use ORM\OneToMany;
use ORM\JoinColumn;

/**
 * Fees
 *
 * @ORM\Table(name="fees")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FeesRepository")
 */
class Fees
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
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @var float
     *
     * @ORM\Column(name="iva", type="float")
     */
    private $iva;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="generationDate", type="datetime")
     */
    private $generationDate;
    
    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var bool
     *
     * @ORM\Column(name="default", type="boolean")
     */
    private $default;
    
    
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
     * @return Fees
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
     * Set price
     *
     * @param float $price
     *
     * @return Fees
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }
    
    /**
     * Set iva
     *
     * @param float $iva
     *
     * @return Fees
     */
    public function setIva($iva)
    {
        $this->iva = $iva;

        return $this;
    }

    /**
     * Get iva
     *
     * @return float
     */
    public function getIva()
    {
        return $this->iva;
    }
    
    /**
     * Set generationDate
     *
     * @param \DateTime $generationDate
     *
     * @return Fees
     */
    public function setGenerationDate($generationDate)
    {
        $this->generationDate = $generationDate;

        return $this;
    }

    /**
     * Get generationDate
     *
     * @return \DateTime
     */
    public function getGenerationDate()
    {
        return $this->generationDate;
    }
    
    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Fees
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }
    
    /**
     * Set default
     *
     * @param boolean $default
     *
     * @return Fees
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Get default
     *
     * @return boolean
     */
    public function getDefault()
    {
        return $this->default;
    }
}
