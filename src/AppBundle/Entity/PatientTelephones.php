<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use ORM\OneToMany;
use ORM\JoinColumn;

/**
 * PatientTelephones
 *
 * @ORM\Table(name="patient_telephones")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PatientTelephonesRepository")
 */
class PatientTelephones
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
     * @ORM\Column(name="telephoneType", type="integer")
     * @ORM\ManyToOne(targetEntity="TelephoneTypes")
     * @ORM\JoinColumn(name="telephoneType", referencedColumnName="id")
     */
    private $telephoneType;

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
     * @ORM\Column(name="number", type="integer", nullable=true)
     */
    private $number;

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return PatientTelephones
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
     * Set telephoneType
     *
     * @param integer $telephoneType
     *
     * @return PatientTelephones
     */
    public function setTeleponeType($telephoneType)
    {
        $this->telephoneType = $telephoneType;

        return $this;
    }

    /**
     * Get telephoneType
     *
     * @return int
     */
    public function getTeleponeType()
    {
        return $this->telephoneType;
    }

    /**
     * Set patient
     *
     * @param integer $patient
     *
     * @return PatientTelephones
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
     * Set number
     *
     * @param string $number
     *
     * @return PatientTelephones
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set telephoneType
     *
     * @param integer $telephoneType
     *
     * @return PatientTelephones
     */
    public function setTelephoneType($telephoneType)
    {
        $this->telephoneType = $telephoneType;

        return $this;
    }

    /**
     * Get telephoneType
     *
     * @return integer
     */
    public function getTelephoneType()
    {
        return $this->telephoneType;
    }
}
