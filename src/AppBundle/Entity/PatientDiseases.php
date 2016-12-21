<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use ORM\OneToMany;
use ORM\JoinColumn;

/**
 * PatientDiseases
 *
 * @ORM\Table(name="patient_diseases")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PatientDiseasesRepository")
 */
class PatientDiseases
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
     * @ORM\Column(name="disease", type="integer")
     * @ORM\ManyToOne(targetEntity="Diseases")
     * @ORM\JoinColumn(name="disease", referencedColumnName="id")
     */
    private $disease;

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
     * Set id
     *
     * @param Integer $id
     * 
     * @return int
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
     * Set disease
     *
     * @param integer $disease
     *
     * @return PatientDiseases
     */
    public function setDisease($disease)
    {
        $this->disease = $disease;

        return $this;
    }

    /**
     * Get disease
     *
     * @return int
     */
    public function getDisease()
    {
        return $this->disease;
    }

    /**
     * Set patient
     *
     * @param integer $patient
     *
     * @return PatientDiseases
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
     * @return PatientDiseases
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
     * @return PatientDiseases
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
