<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use ORM\OneToMany;
use ORM\JoinColumn;

/**
 * Invoices
 *
 * @ORM\Table(name="invoices")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InvoicesRepository")
 */
class Invoices
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
     * @var \DateTime
     *
     * @ORM\Column(name="generationDate", type="datetime")
     */
    private $generationDate;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="exportedDate", type="datetime")
     */
    private $exportedDate;
    
    /**
     * @var int
     *
     * @ORM\Column(name="administrator", type="integer")
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumn(name="administrator", referencedColumnName="id")
     */
    private $administrator;
    
    
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
     * Set generationDate
     *
     * @param \DateTime $generationDate
     *
     * @return Invoices
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
     * Set exportedDate
     *
     * @param \DateTime $exportedDate
     *
     * @return Invoices
     */
    public function setExportedDate($exportedDate)
    {
        $this->exportedDate = $exportedDate;

        return $this;
    }

    /**
     * Get exportedDate
     *
     * @return \DateTime
     */
    public function getExportedDate()
    {
        return $this->exportedDate;
    }
    
    /**
     * Set administrator
     *
     * @param integer $administrator
     *
     * @return Invoices
     */
    public function setAdministrator($administrator)
    {
        $this->administrator = $administrator;

        return $this;
    }

    /**
     * Get administrator
     *
     * @return int
     */
    public function getAdministrator()
    {
        return $this->administrator;
    }
}
