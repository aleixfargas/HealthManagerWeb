<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Patients
 *
 * @ORM\Table(name="patients")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PatientsRepository")
 * @UniqueEntity("dni", message="Dni already exists!")
 */
class Patients
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
     * @ORM\Column(name="dni", type="string", length=45, nullable=true, unique=true)
     */
    private $dni;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=45)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="surname", type="string", length=45, nullable=true)
     */
    private $surname;

    /**
     * @var int
     *
     * @ORM\Column(name="age", type="integer", nullable=true)
     */
    private $age;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthday", type="date", nullable=true)
     */
    private $birthday;

    /**
     * @var string
     *
     * @ORM\Column(name="job", type="string", length=45, nullable=true)
     */
    private $job;

    /**
     * @var bool
     *
     * @ORM\Column(name="photo", type="boolean")
     */
    private $photo;

    /**
     * @var bool
     *
     * @ORM\Column(name="addresses", type="boolean")
     */
    private $addresses;

    /**
     * @var bool
     *
     * @ORM\Column(name="telephones", type="boolean")
     */
    private $telephones;

    /**
     * @var bool
     *
     * @ORM\Column(name="emails", type="boolean")
     */
    private $emails;

    /**
     * @var bool
     *
     * @ORM\Column(name="diseases", type="boolean")
     */
    private $diseases;

    /**
     * @var bool
     *
     * @ORM\Column(name="operations", type="boolean")
     */
    private $operations;

    /**
     * @var bool
     *
     * @ORM\Column(name="allergies", type="boolean")
     */
    private $allergies;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="registerDate", type="datetime")
     */
    private $registerDate;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="string", length=255, nullable=true)
     */
    private $notes;

    /**
     * @var int
     *
     * @ORM\Column(name="user", type="integer")
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    private $user;

    
    public function __construct($user) {
        $format = 'Y-m-d H:i:s';
        $date = \DateTime::createFromFormat($format, date($format));
        $this->setRegisterDate($date);
        $this->setUser($user);
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
     * @param string $id
     *
     * @return Patients
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this->id;
    }
    
    /**
     * Set dni
     *
     * @param string $dni
     *
     * @return Patients
     */
    public function setDni($dni)
    {
        $this->dni = $dni;

        return $this;
    }

    /**
     * Get dni
     *
     * @return string
     */
    public function getDni()
    {
        return $this->dni;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Patients
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
     * Set surname
     *
     * @param string $surname
     *
     * @return Patients
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get surname
     *
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set age
     *
     * @param integer $age
     *
     * @return Patients
     */
    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * Get age
     *
     * @return int
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set birthday
     *
     * @param \DateTime $birthday
     *
     * @return Patients
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set job
     *
     * @param string $job
     *
     * @return Patients
     */
    public function setJob($job)
    {
        $this->job = $job;

        return $this;
    }

    /**
     * Get job
     *
     * @return string
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * Set photo
     *
     * @param boolean $photo
     *
     * @return Patients
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo
     *
     * @return bool
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set addresses
     *
     * @param boolean $addresses
     *
     * @return Patients
     */
    public function setAddresses($addresses)
    {
        $this->addresses = $addresses;

        return $this;
    }

    /**
     * Get addresses
     *
     * @return bool
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * Set telephones
     *
     * @param boolean $telephones
     *
     * @return Patients
     */
    public function setTelephones($telephones)
    {
        $this->telephones = $telephones;

        return $this;
    }

    /**
     * Get telephones
     *
     * @return bool
     */
    public function getTelephones()
    {
        return $this->telephones;
    }

    /**
     * Set emails
     *
     * @param boolean $emails
     *
     * @return Patients
     */
    public function setEmails($emails)
    {
        $this->emails = $emails;

        return $this;
    }

    /**
     * Get emails
     *
     * @return bool
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * Set diseases
     *
     * @param boolean $diseases
     *
     * @return Patients
     */
    public function setDiseases($diseases)
    {
        $this->diseases = $diseases;

        return $this;
    }

    /**
     * Get diseases
     *
     * @return bool
     */
    public function getDiseases()
    {
        return $this->diseases;
    }

    /**
     * Set operations
     *
     * @param boolean $operations
     *
     * @return Patients
     */
    public function setOperations($operations)
    {
        $this->operations = $operations;

        return $this;
    }

    /**
     * Get operations
     *
     * @return bool
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * Set allergies
     *
     * @param boolean $allergies
     *
     * @return Patients
     */
    public function setAllergies($allergies)
    {
        $this->allergies = $allergies;

        return $this;
    }

    /**
     * Get allergies
     *
     * @return bool
     */
    public function getAllergies()
    {
        return $this->allergies;
    }

    /**
     * Set registerDate
     *
     * @param \DateTime $registerDate
     *
     * @return Patients
     */
    public function setRegisterDate($registerDate)
    {
        $this->registerDate = $registerDate;

        return $this;
    }

    /**
     * Get registerDate
     *
     * @return \DateTime
     */
    public function getRegisterDate()
    {
        return $this->registerDate;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return Patients
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
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
