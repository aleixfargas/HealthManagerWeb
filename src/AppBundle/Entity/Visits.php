<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use ORM\OneToMany;
use ORM\JoinColumn;

/**
 * Visits
 *
 * @ORM\Table(name="visits")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VisitsRepository")
 */
class Visits
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
     * @var int
     *
     * @ORM\Column(name="patient", type="integer")
     * @ORM\ManyToOne(targetEntity="Patients")
     * @ORM\JoinColumn(name="patient", referencedColumnName="id")
     */
    private $patient;

    /**
     * @var int
     *
     * @ORM\Column(name="physiotherapist", type="integer")
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="physiotherapist", referencedColumnName="id")
     */
    private $physiotherapist;

    /**
     * @var int
     *
     * @ORM\Column(name="duration", type="integer")
     */
    private $duration;

    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="string", length=255, nullable=true)
     */
    private $reason;

    /**
     * @var string
     *
     * @ORM\Column(name="comments", type="string", length=255, nullable=true)
     */
    private $comments;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="visitDate", type="datetime")
     */
    private $visitDate;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="reservationDate", type="datetime")
     */
    private $reservationDate;

    /**
     * @var int
     *
     * @ORM\Column(name="fee", type="integer")
     * @ORM\ManyToOne(targetEntity="Fees")
     * @ORM\JoinColumn(name="fee", referencedColumnName="id")
     */
    private $fee;

    /**
     * @var int
     *
     * @ORM\Column(name="invoice", type="integer")
     * @ORM\ManyToOne(targetEntity="Invoices")
     * @ORM\JoinColumn(name="invoice", referencedColumnName="id")
     */
    private $invoice;
    
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
     * Set patient
     *
     * @param integer $patient
     *
     * @return Visits
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;

        return $this;
    }

    /**
     * Get patient
     *
     * @return int
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * Set physiotherapist
     *
     * @param integer $physiotherapist
     *
     * @return Visits
     */
    public function setPhysiotherapist($physiotherapist)
    {
        $this->physiotherapist = $physiotherapist;

        return $this;
    }
    
    /**
     * Get physiotherapist
     *
     * @return integer
     */
    public function getPhysiotherapist()
    {
        return $this->physiotherapist;
    }
    
    /**
     * Get duration
     *
     * @return integer
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set duration
     *
     * @param integer $duration
     *
     * @return Visits
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }
    
    /**
     * Get reason
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set reason
     *
     * @param string $reason
     *
     * @return Visits
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }
    
    /**
     * Get comments
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set comments
     *
     * @param string $comments
     *
     * @return Visits
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Set visitDate
     *
     * @param \DateTime $visitDate
     *
     * @return Visits
     */
    public function setVisitDate($visitDate)
    {
        $this->askedDate = $askedDate;

        return $this;
    }

    /**
     * Get visitDate
     *
     * @return \DateTime
     */
    public function getVisitDate()
    {
        return $this->visitDate;
    }
    
    /**
     * Set reservationDate
     *
     * @param \DateTime $reservationDate
     *
     * @return Visits
     */
    public function setReservationDate($askedDate)
    {
        $this->reservationDate = $reservationDate;

        return $this;
    }

    /**
     * Get reservationDate
     *
     * @return \DateTime
     */
    public function getReservationDate()
    {
        return $this->reservationDate;
    }
    
    /**
     * Set fee
     *
     * @param integer $fee
     *
     * @return Visits
     */
    public function setFee($fee)
    {
        $this->fee = $fee;

        return $this;
    }

    /**
     * Get fee
     *
     * @return int
     */
    public function getfee()
    {
        return $this->fee;
    }

    /**
     * Set invoice
     *
     * @param integer $invoice
     *
     * @return Visits
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;

        return $this;
    }
    
    /**
     * Get invoice
     *
     * @return integer
     */
    public function getInvoice()
    {
        return $this->invoice;
    }
}
