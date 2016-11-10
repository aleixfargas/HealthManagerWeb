<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use ORM\OneToMany;
use ORM\JoinColumn;

/**
 * PatientAddress
 *
 * @ORM\Table(name="patient_address")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PatientAddressRepository")
 */
class PatientAddress
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
     * @ORM\Column(name="addressType", type="integer")
     * @ORM\ManyToOne(targetEntity="AddressTypes")
     * @ORM\JoinColumn(name="addressType", referencedColumnName="id")
     */
    private $addressType;

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
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;

    
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
     * Set address
     *
     * @param integer $addressType
     *
     * @return PatientAddress
     */
    public function setAddressType($addressType)
    {
        $this->addressType = $addressType;

        return $this;
    }

    /**
     * Get addressType
     *
     * @return int
     */
    public function getAddressType()
    {
        return $this->addressType;
    }

    /**
     * Set patient
     *
     * @param integer $patient
     *
     * @return PatientAddress
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
     * Set address
     *
     * @param string $address
     *
     * @return PatientAddress
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }
}
