<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Form\PatientsType;
use AppBundle\Form\PatientAddressType;
use AppBundle\Form\AddressTypesType;

use AppBundle\Entity\Patients;
use AppBundle\Entity\PatientAddress;
use AppBundle\Entity\AddressTypes;
use AppBundle\Entity\PatientEmails;
use AppBundle\Entity\EmailTypes;
use AppBundle\Entity\PatientTelephones;
use AppBundle\Entity\TelephoneTypes;
use AppBundle\Entity\PatientOperations;
use AppBundle\Entity\Operations;
use AppBundle\Entity\PatientAllergies;
use AppBundle\Entity\Allergies;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PatientsController extends Controller
{
    private $section_name = 'base.global_section_patients';
    private $maxResults = 6;
    private $error = false;
    private $error_message = '';

    private function getTranslatedSectionName()
    {
        return $this->get('translator')->trans($this->section_name, array(), 'base');
    }
    
    /**
     * @Route("/patients/list/{page}", name="patients-list")
     */
    public function indexAction($page = 1)
    {
        $logger = $this->get('logger');
        
        list($total_patients, $patients_list) = $this->get_all_patients($page);        
        $address_types = $this->getAddressTypes();
        $telephone_types = $this->getTelephoneTypes();
        $email_types = $this->getEmailTypes();
        $diseases = $this->getDiseases();
        $operations = $this->getOperations();
        $allergies = $this->getAllergies();
//        $form = $this->create_addNew_patient_form();
        
        $pages = ((int)($total_patients/$this->maxResults))+(($total_patients%$this->maxResults)==0? 0 : 1);
        
        $logger->info('Total patients = ' . $total_patients);
        $logger->info('Pages = ' . $pages);
        $logger->info('Current page = ' . $page);
        
        return $this->render(
            'patients/list_patients.html.twig', array(
                'error' => $this->error,
                'error_message' => $this->error_message,
//                'form' => $form->createView(),
                'address_types' => $address_types,
                'phone_types' => $telephone_types,
                'email_types' => $email_types,
                'diseases_types' => $diseases,
                'operations_types' => $operations,
                'allergies_types' => $allergies,
                'pages' => $pages,
                'current_page' => $page,
                'patients_list' => $patients_list,
                'is_section' =>true,
                'sections' => [
                    ['url'=>'#', 'name'=>$this->getTranslatedSectionName()]
                ]
            )
        );
    }
    
    /**
     * @Route("/patients/save", name="patients-save")
     */
    public function savePatientAction(Request $request){
        $logger = $this->get('logger');

        $result = 'error';
        $action = 'Unknown error';
        $data_correctly_formated = true;

        //set patient values
        $patient = new Patients();
        $patient->setName($request->request->get('name'));
        $patient->setSurname($request->request->get('surname'));
        $patient->setAge($request->request->get('age'));
        $date = $request->request->get('birthday');
        $date = \DateTime::createFromFormat('Y-m-d', $date);
        $patient->setBirthday($date);
        $patient->setJob($request->request->get('job'));
        $patient->setPhoto(FALSE);
        $patient->setEmails(FALSE);
        if($request->request->get('email') != null){
            $patient->setEmails(TRUE);
            $patientEmails = new PatientEmails();
            $patientEmails->setEmail($request->request->get('email'));
            $patientEmails->setEmailType($request->request->get('email_type'));
        }
        $patient->setAddresses(FALSE);
        if($request->request->get('address') != null){
            $patient->setAddresses(TRUE);
            $patientAddress = new PatientAddress();
            $patientAddress->setAddress($request->request->get('address'));
            $patientAddress->setAddressType($request->request->get('address_type'));
        }
        $patient->setTelephones(FALSE);
        if($request->request->get('phone') != null){
            $patient->setTelephones(TRUE);
            $patientTelephones = new PatientTelephones();
            $patientTelephones->setNumber($request->request->get('phone'));
            $patientTelephones->setTelephoneType($request->request->get('phone_type'));
        }
//        $request->request->get('diseases_type'];
        $patient->setDiseases(FALSE);
        $patient->setOperations(FALSE);
        if($request->request->get('operations_type') != null){
            $patient->setOperations(TRUE);
            $patientOperations = new PatientOperations();
            $patientOperations->setOperation($request->request->get('operations_type'));
//            $patientOperations->setComments($request->request->get('allergies_comments'));
        }
        $patient->setAllergies(FALSE);
        if($request->request->get('allergies_type') != null){
            $patient->setAllergies(TRUE);
            $patientAllergies = new PatientAllergies();
            $patientAllergies->setAllergy($request->request->get('allergies_type'));
//            $patientAllergies->setComments($request->request->get('allergies_comments'));
        }
        $patient->setNotes($request->request->get('notes'));
        $data_correctly_formated = true;
        if($data_correctly_formated){
            //everything ok
            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($patient);
                $em->flush();
                
                if($patient->getEmails() == TRUE){
                    $patientEmails->setPatient($patient->getId());
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($patientEmails);
                    $em->flush();
                }
                if($patient->getAddresses() == TRUE){
                    $patientAddress->setPatient($patient->getId());
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($patientAddress);
                    $em->flush();
                }
                if($patient->getTelephones() == TRUE){
                    $patientTelephones->setPatient($patient->getId());
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($patientTelephones);
                    $em->flush();
                }
                if($patient->getOperations() == TRUE){
                    $patientOperations->setPatient($patient->getId());
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($patientOperations);
                    $em->flush();
                }
                if($patient->getAllergies() == TRUE){
                    $patientAllergies->setPatient($patient->getId());
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($patientAllergies);
                    $em->flush();
                }
                
                $action = $this->generateUrl('patients-show', ['patient_id'=>$patient->getId()]);
                $result = 'success';
            } catch (UniqueConstraintViolationException $e){
                $logger->error($e->getMessage());
            }
        } else {
            //not valid
            $action = $this->render(
                'patients/add_patients.html.twig', array(
                    'form' => $form->createView(),
                )
            )->getContent();
            
            $form->createView();
        }

        $response = json_encode(array('status'=>$result, 'action'=>$action));
        return new Response($response);
    }

//  With form submitting
//    public function savePatientAction(Request $request){
//        $logger = $this->get('logger');
//
//        $result = 'error';
//        $action = 'Unknown error';
//
//        $form = $this->create_addNew_patient_form();
//        
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $patient = $form->getData();
//            
//            try {
//                $em = $this->getDoctrine()->getManager();
//                $em->persist($patient);
//                $em->flush();
//                $action = $this->generateUrl('patients-show', ['patient_id'=>$patient->getId()]);
//                $result = 'success';
//            } catch (UniqueConstraintViolationException $e){
//                $logger->error($e->getMessage());
//            }
//        } else {
//            $action = $this->render(
//                'patients/add_patients.html.twig', array(
//                    'form' => $form->createView(),
//                )
//            )->getContent();
//            
//            $form->createView();
//        }
//
//        $response = json_encode(array('status'=>$result, 'action'=>$action));
//        return new Response($response);
//    }
    
    /**
     * @Route("/patients/remove", name="patients-remove")
     */
    public function removePatientAction(Request $request){
        $patients_array = $request->request->get('patients_array');
        
        $logger = $this->get('logger');
        $result = 'error';
        
        foreach ($patients_array as $patient_id){
            try {
                $this->delete_patient($patient_id);
                $result = 'success';
                $action = $this->generateUrl('patients-list');
            } catch (NotFoundHttpException $e){
                $logger->error($e->getMessage());
                $result = 'error';
                $action = "Could not remove patient with id $patient_id, try again later!";
            }
        }

        $response = json_encode(array('status'=>$result, 'action'=>$action));
        return new Response($response);
    }

    /**
     * @Route("/patients/show/{patient_id}", name="patients-show")
     */
    public function showPatientAction($patient_id)
    {
        $patient = $this->get_patient($patient_id);
        
        return $this->render(
            'patients/show_patients.html.twig', array(
                'patient'=>$patient,
                'error' => $this->error,
                'error_message' => $this->error_message,
                'is_section' =>true,
                'sections' => [
                    ['url'=>$this->generateUrl('patients-list'), 'name'=>$this->getTranslatedSectionName()],
                    ['url'=>'#','name'=>$patient->getName()]
                ]
            )
        );
    }
    
    //================== PRIVATE METHODS ===================
    /**
     * Method to get all the patients
     * 
     * @return array Containing all patients
     * @throws Exception NotFoundException
     */
    private function get_all_patients($page){
        $limit = $this->maxResults;
        $offset = $this->maxResults * ($page-1);

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT p
            FROM AppBundle:Patients p
            ORDER BY p.name ASC'
        )->setFirstResult($offset)->setMaxResults($limit);

        $paginator = new Paginator($query);
        
        return [$paginator->count(), $paginator->getIterator()];
    }
    
    /**
     * Method to get the patient that match with the id
     * 
     * @param Integer $patient_id Containing patient id
     * @return Entity Containing the patient
     * @throws Exception NotFoundException
     */
    private function get_patient($patient_id){
        $patients_repository = $this->getDoctrine()->getRepository('AppBundle:Patients');
        $patient = $patients_repository->find($patient_id);

        if (!$patient) {
            throw $this->createNotFoundException(
                'No patient found'
            );
        }
        
        return $patient;
    }
    
    /**
     * Method to remove a patient
     * 
     * @param Integer $patient_id Containing patient id
     * @return void
     * @throws Exception NotFoundException
     */
    private function delete_patient($patient_id){
        $em = $this->getDoctrine()->getManager();
        $patient = $em->getRepository('AppBundle:Patients')->find($patient_id);

        if (!$patient) {
            throw $this->createNotFoundException(
                'No patient found for id ' . $patient_id
            );
        }

        $em->remove($patient);
        $em->flush();
    }
    
    /**
     * Method to get all the address Types
     * 
     * @return Array Containing all the types
     */
    private function getAddressTypes(){
        $address_typesArray = array();
        $repository = $this->getDoctrine()->getRepository('AppBundle:AddressTypes');
        $all_addressTypes = $repository->findAll();
        foreach ($all_addressTypes as $addressTypesField){
            array_push($address_typesArray, array('id' => $addressTypesField->getId(), 'name' => $addressTypesField->getName()));
        }
        
        return $address_typesArray;
    }
    
    /**
     * Method to get all the telephone Types
     * 
     * @return Array Containing all the types
     */
    private function getTelephoneTypes(){
        $telephone_typesArray = array();
        $repository = $this->getDoctrine()->getRepository('AppBundle:TelephoneTypes');
        $all_telephoneTypes = $repository->findAll();
        foreach ($all_telephoneTypes as $telephoneTypesField){
            array_push($telephone_typesArray, array('id' => $telephoneTypesField->getId(), 'name' => $telephoneTypesField->getName()));
        }
        
        return $telephone_typesArray;
    }
    
    /**
     * Method to get all the email Types
     * 
     * @return Array Containing all the types
     */
    private function getEmailTypes(){
        $email_typesArray = array();
        $repository = $this->getDoctrine()->getRepository('AppBundle:EmailTypes');
        $all_emailTypes = $repository->findAll();
        foreach ($all_emailTypes as $emailTypesField){
            array_push($email_typesArray, array('id' => $emailTypesField->getId(), 'name' => $emailTypesField->getName()));
        }
        
        return $email_typesArray;
    }
    
    
    /**
     * Method to get all the diseases Types
     * 
     * @return Array Containing all the types
     */
    private function getDiseases(){
        $diseases_Array = array();
        $repository = $this->getDoctrine()->getRepository('AppBundle:Diseases');
        $all_diseases = $repository->findAll();
        foreach ($all_diseases as $diseasesField){
            array_push($diseases_Array, array('id' => $diseasesField->getId(), 'name' => $diseasesField->getName(), 'characteristics' => $diseasesField->getCharacteristics()));
        }
        
        return $diseases_Array;
    }
    
    /**
     * Method to get all the operations Types
     * 
     * @return Array Containing all the types
     */
    private function getOperations(){
        $operations_Array = array();
        $repository = $this->getDoctrine()->getRepository('AppBundle:Operations');
        $all_operations = $repository->findAll();
        foreach ($all_operations as $operationsField){
            array_push($operations_Array, array('id' => $operationsField->getId(), 'name' => $operationsField->getName()));
        }
        
        return $operations_Array;
    }
    
    /**
     * Method to get all the allergies Types
     * 
     * @return Array Containing all the types
     */
    private function getAllergies(){
        $allergies_Array = array();
        $repository = $this->getDoctrine()->getRepository('AppBundle:Allergies');
        $all_allergies = $repository->findAll();
        foreach ($all_allergies as $allergiesField){
            array_push($allergies_Array, array('id' => $allergiesField->getId(), 'name' => $allergiesField->getName()));
        }
        
        return $allergies_Array;
    }
    
    /**
     * Method to get a patient form
     * 
     * @param Integer $patient Containing a patient Entity
     * @return Object Containing the form
     */
    private function create_addNew_patient_form(){
        $namesArray = array();

        //Seguir aquests passos, assert a les entites posat...
        //https://symfony.com/doc/current/form/embedded.html
        
        
        
        $addressTypes = new AddressTypes();
        $patientAddress = new PatientAddress();
        $patient = new Patients();
        
        $addressTypesType_subForm = $this->createForm(AddressTypesType::class, $addressTypes,
            array(
                'names'=>$namesArray
            )
        );
        $patientAddressType_subForm = $this->createForm(PatientAddressType::class, $patientAddress
//            array(
//                'AddressTypesTypeSubForm' => $addressTypesType_subForm,
//            )
        );
        
        $patients_form = $this->createForm(PatientsType::class, $patient,
            array(
                'attr' => ['id'=>'form_new_patient', 'url'=>$this->generateUrl('patients-save')]
//                    'action' => $this->generateUrl('patients_save'),
//                    'method' => 'POST',
            )
        );
        
        return $patients_form;
    }
}