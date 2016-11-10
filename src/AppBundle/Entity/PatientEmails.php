<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use ORM\OneToMany;
use ORM\JoinColumn;

/**
 * PatientEmails
 *
 * @ORM\Table(name="patient_emails")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PatientEmailsRepository")
 */
class PatientEmails
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
     * @ORM\Column(name="emailType", type="integer")
     * @ORM\ManyToOne(targetEntity="EmailTypes")
     * @ORM\JoinColumn(name="emailType", referencedColumnName="id")
     */
    private $emailType;

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
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    
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
     * Set emailType
     *
     * @param integer $emailType
     *
     * @return PatientEmails
     */
    public function setEmailType($emailType)
    {
        $this->emailType = $emailType;

        return $this;
    }

    /**
     * Get emailType
     *
     * @return int
     */
    public function getEmailType()
    {
        return $this->emailType;
    }

    /**
     * Set patient
     *
     * @param integer $patient
     *
     * @return PatientEmails
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
     * Set email
     *
     * @param string $email
     *
     * @return PatientEmails
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
}
