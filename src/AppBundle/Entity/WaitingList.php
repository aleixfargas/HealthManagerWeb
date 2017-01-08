<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use ORM\OneToMany;
use ORM\JoinColumn;

/**
 * WaitingList
 *
 * @ORM\Table(name="waiting_list")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\WaitingListRepository")
 */
class WaitingList
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
     * @var \DateTime
     *
     * @ORM\Column(name="askedDate", type="datetime")
     */
    private $askedDate;
    
    /**
     * @var int
     *
     * @ORM\Column(name="user", type="integer")
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    private $user;

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
     * @return WaitingList
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
     * @return WaitingList
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
     * Set askedDate
     *
     * @param \DateTime $askedDate
     *
     * @return WaitingList
     */
    public function setAskedDate($askedDate)
    {
        $this->askedDate = $askedDate;

        return $this;
    }

    /**
     * Get askedDate
     *
     * @return \DateTime
     */
    public function getAskedDate()
    {
        return $this->askedDate;
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
