<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use ORM\OneToMany;
use ORM\JoinColumn;

/**
 * PatientOperations
 *
 * @ORM\Table(name="patient_operations")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PatientOperationsRepository")
 */
class PatientOperations
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
     * @ORM\Column(name="operation", type="integer")
     * @ORM\ManyToOne(targetEntity="Operations")
     * @ORM\JoinColumn(name="operation", referencedColumnName="id")
     */
    private $operation;

    /**
     * @var int
     *
     * @ORM\Column(name="patient", type="integer")
     * @ORM\ManyToOne(targetEntity="Patients")
     * @ORM\JoinColumn(name="patient", referencedColumnName="id")
     */
    private $patient;

    /**
     * @var string
     *
     * @ORM\Column(name="comments", type="string", length=255, nullable=true)
     */
    private $comments;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp", type="datetime")
     */
    private $timestamp;
    
    public function __construct() {
        $format = 'Y-m-d H:i:s';
        $date = \DateTime::createFromFormat($format, date($format));
        $this->setTimestamp($date);
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
     * Set allergy
     *
     * @param integer $operation
     *
     * @return PatientOperations
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;

        return $this;
    }

    /**
     * Get operation
     *
     * @return int
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Set patient
     *
     * @param integer $patient
     *
     * @return PatientOperations
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
     * Set comments
     *
     * @param string $comments
     *
     * @return PatientOperations
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

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
     * Set timestamp
     *
     * @param \DateTime $timestamp
     *
     * @return PatientOperations
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Get timestamp
     *
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
