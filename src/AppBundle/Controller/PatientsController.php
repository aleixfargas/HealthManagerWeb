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
    
    private $patient = null;
    private $patientEmails = null;
    private $patientAddress = null;
    private $patientTelephones = null;
    private $patientOperations = null;
    private $patientAllergies = null;
    
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
        $address_types = $this->get_all_address_types();
        $telephone_types = $this->get_all_telephone_types();
        $email_types = $this->get_all_email_types();
        $diseases = $this->get_all_diseases();
        $operations = $this->get_all_operations();
        $allergies = $this->get_all_allergies();
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
    public function saveNewPatientAction(Request $request){
        $logger = $this->get('logger');

        $result = 'error';
        $action = 'Unknown error';
        $data_correctly_formated = true;

        $this->build_patient_entities($request);
        
        $data_correctly_formated = true;
        if($data_correctly_formated){
            //everything ok
            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($this->patient);
                $em->flush();
                
                if($this->patient->getEmails() == TRUE){
                    $this->patientEmails->setPatient($this->patient->getId());
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($this->patientEmails);
                    $em->flush();
                }
                if($this->patient->getAddresses() == TRUE){
                    $this->patientAddress->setPatient($this->patient->getId());
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($this->patientAddress);
                    $em->flush();
                }
                if($this->patient->getTelephones() == TRUE){
                    $this->patientTelephones->setPatient($this->patient->getId());
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($this->patientTelephones);
                    $em->flush();
                }
                if($this->patient->getOperations() == TRUE){
                    foreach($this->patientOperations as $one_patientOperations){
                        $one_patientOperations->setPatient($this->patient->getId());
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($one_patientOperations);
                        $em->flush();
                    }
                }
                if($this->patient->getAllergies() == TRUE){
                    foreach($this->patientAllergies as $one_patientAllergies){
                        $one_patientAllergies->setPatient($this->patient->getId());
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($one_patientAllergies);
                        $em->flush();
                    }
                }
                
                $action = $this->generateUrl('patients-show', ['patient_id'=>$this->patient->getId()]);
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
        // $patient_data = [patient, addresses, allergies, diseases, emails, operations, telephones]
        $patient_data = $this->get_patient($patient_id);
        
        return $this->render(
            'patients/show_patients.html.twig', array(
                'patient_data'=>$patient_data,
                'error' => $this->error,
                'error_message' => $this->error_message,
                'is_section' =>true,
                'sections' => [
                    ['url'=>$this->generateUrl('patients-list'), 'name'=>$this->getTranslatedSectionName()],
                    ['url'=>'#','name'=>$patient_data['patient']->getName()]
                ]
            )
        );
    }

    /**
     * @Route("/patients/edit/{patient_id}", name="patients-edit")
     */
    public function editPatientAction($patient_id)
    {
        // $patient_data = [patient, addresses, allergies, diseases, emails, operations, telephones]
        $patient_data = $this->get_patient($patient_id);
        $all_telephone_types = $this->get_all_telephone_types();
        $all_email_types = $this->get_all_email_types();
        $all_address_types = $this->get_all_address_types();
        $all_allergies_types = $this->get_all_allergies();
        $all_operations_types = $this->get_all_operations();
        
        return $this->render(
            'patients/edit_patients.html.twig', array(
                'patient_data'=>$patient_data,
                'all_phone_types'=>$all_telephone_types,
                'all_email_types'=>$all_email_types,
                'all_address_types'=>$all_address_types,
                'all_allergies_types'=>$all_allergies_types,
                'all_operations_types'=>$all_operations_types,
                'error' => $this->error,
                'error_message' => $this->error_message,
                'is_section' =>true,
                'sections' => [
                    ['url'=>$this->generateUrl('patients-list'), 'name'=>$this->getTranslatedSectionName()],
                    ['url'=>'#','name'=>$patient_data['patient']->getName()]
                ]
            )
        );
    }
    
    /**
     * @Route("/patients/save/edit", name="patients-save-edit")
     */
    public function saveEditPatientAction(Request $request){
        $result = 'error';
        $action = "No modification made";
        $changes = false;
        
        $all_data_success = $this->build_patient_entities($request);
        if($all_data_success){
            try{
                $current_patient = $this->get_patient($this->patient->getId());
                
                $em = $this->getDoctrine()->getManager();
                $patient_to_update = $em->getRepository('AppBundle:Patients')->find($this->patient->getId());
                
                $newDni = $this->patient->getDni();
                if($current_patient['patient']->getDni() != $newDni){
                    $patient_to_update->setDni($newDni);
                    $changes = true;
                }
                $newName = $this->patient->getName();
                if($current_patient['patient']->getName() != $newName){
                    $patient_to_update->setName($newName);
                    $changes = true;
                }
                $newSurname = $this->patient->getSurname();
                if($current_patient['patient']->getSurname() != $newSurname){
                    $patient_to_update->setSurname($newSurname);
                    $changes = true;
                }
                $newAge = $this->patient->getAge();
                if($current_patient['patient']->getAge() != $newAge){
                    $patient_to_update->setAge($newAge);
                    $changes = true;
                }
                $newBirthday = $this->patient->getBirthday();
                if($current_patient['patient']->getBirthday() != $newBirthday){
                    $patient_to_update->setBirthday($newBirthday);
                    $changes = true;
                }
                $newJob = $this->patient->getJob();
                if($current_patient['patient']->getJob() != $newJob){
                    $patient_to_update->setJob($newJob);
                    $changes = true;
                }
                $newAddresses = $this->patient->getAddresses();
                if($current_patient['patient']->getAddresses() != $newAddresses){
                    $patient_to_update->setAddresses($newAddresses);
                    $changes = true;
                }
                $newTelephones = $this->patient->getTelephones();
                if($current_patient['patient']->getTelephones() != $newTelephones){
                    $patient_to_update->setTelephones($newTelephones);
                    $changes = true;
                }
                $newEmails = $this->patient->getEmails();
                if($current_patient['patient']->getEmails() != $newEmails){
                    $patient_to_update->setEmails($newEmails);
                    $changes = true;
                }
                $newDiseases = $this->patient->getDiseases();
                if($current_patient['patient']->getDiseases() != $newDiseases){
                    $patient_to_update->setDiseases($newDiseases);
                    $changes = true;
                }
                $newOperations = $this->patient->getOperations();
                if($current_patient['patient']->getOperations() != $newOperations){
                    $patient_to_update->setOperations($newOperations);
                    $changes = true;
                }
                $newAllergies = $this->patient->getAllergies();
                if($current_patient['patient']->getAllergies() != $newAllergies){
                    $patient_to_update->setAllergies($newAllergies);
                    $changes = true;
                }
                $newNotes = $this->patient->getNotes();
                if($current_patient['patient']->getNotes() != $newNotes){
                    $patient_to_update->setNotes($newNotes);
                    $changes = true;
                }                
                
                //========= Patient Telephones Update =======
                $patientTelephones_to_update = $em->getRepository('AppBundle:PatientTelephones')->find($this->patient->getId());
                
                $newPhoneType = $this->patient->getTeleponeType();
                if($current_patient['telephones']->getTeleponeType() != $newPhoneType){
                    $patientTelephones_to_update->setTeleponeType($newPhoneType);
                    $changes = true;
                }
                $newPhoneNumber = $this->patient->getNumber();
                if($current_patient['telephones']->getNumber() != $newPhoneNumber){
                    $patientTelephones_to_update->setNumber($newPhoneNumber);
                    $changes = true;
                }
                
                if($changes){
                    $em->flush();
                    $result = 'success';
                    $action = $this->generateUrl('patients-show', ['patient_id'=>$this->patient->getId()]);
                } else {
                    $action = "No changes found!";                    
                }

//                            $this->patientEmails->setPatient($this->patient->getId());
//                            $em = $this->getDoctrine()->getManager();
//                            $em->persist($this->patientEmails);
//                            $em->flush();
//                            $this->patientAddress->setPatient($this->patient->getId());
//                            $em = $this->getDoctrine()->getManager();
//                            $em->persist($this->patientAddress);
//                            $em->flush();
//                            $this->patientTelephones->setPatient($this->patient->getId());
//                            $em = $this->getDoctrine()->getManager();
//                            $em->persist($this->patientTelephones);
//                            $em->flush();
//                            foreach($this->patientOperations as $one_patientOperations){
//                                $one_patientOperations->setPatient($this->patient->getId());
//                                $em = $this->getDoctrine()->getManager();
//                                $em->persist($one_patientOperations);
//                                $em->flush();
//                            }
//                            foreach($this->patientAllergies as $one_patientAllergies){
//                                $one_patientAllergies->setPatient($this->patient->getId());
//                                $em = $this->getDoctrine()->getManager();
//                                $em->persist($one_patientAllergies);
//                                $em->flush();
//                            }

            } catch(NotFoundException $e){
                $action = $e->getMessage();                
            }
        }
        
        
        
        $response = json_encode(array('status'=>$result, 'action'=>$action));
        return new Response($response);
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
     * @return array Associative array of enttities, [patient, addresses, allergies, diseases, emails, operations, telephones]
     * @throws Exception NotFoundException
     */
    private function get_patient($patient_id){
        $addresses = $this->get_patient_addresses($patient_id);
        $allergies = $this->get_patient_allergies($patient_id);
        $diseases = $this->get_patient_diseases($patient_id);
        $emails = $this->get_patient_emails($patient_id);
        $operations = $this->get_patient_operations($patient_id);
        $telephones = $this->get_patient_telephones($patient_id);
        
        $patients_repository = $this->getDoctrine()->getRepository('AppBundle:Patients');
        $patient = $patients_repository->find($patient_id);

        if (!$patient) {
            throw $this->createNotFoundException(
                'No patient found'
            );
        }
        
        return ['patient'=>$patient, 'addresses'=>$addresses, 'allergies'=>$allergies, 'diseases'=>$diseases, 'emails'=>$emails, 'operations'=>$operations, 'telephones'=>$telephones];
    }
    
    private function get_patient_addresses($patient_id){
        $repository = $this->getDoctrine()->getRepository('AppBundle:PatientAddress');
        $patient_addresses = $repository->findByPatient($patient_id);
        
        $address_types = array();
        foreach($patient_addresses as $patient_address){
            array_push($address_types, $this->get_address_types($patient_address->getAddressType()));
        }
        
        if (!$patient_addresses) {
//            throw $this->createNotFoundException(
//                'No address found'
//            );
            return [];
        }
        
        return ['types'=>$address_types, 'patient_address'=>$patient_addresses];
    }
    
    private function get_address_types($type_id){
        $repository = $this->getDoctrine()->getRepository('AppBundle:AddressTypes');
        $address_types = $repository->find($type_id);
        
        if (!$address_types) {
//            throw $this->createNotFoundException(
//                'No address types found'
//            );
            return [];
        }
        
        return $address_types;
    }
    
    private function get_patient_allergies($patient_id){
        $repository = $this->getDoctrine()->getRepository('AppBundle:PatientAllergies');
        $patient_allergies = $repository->findByPatient($patient_id);

        $allergies = array();
        foreach($patient_allergies as $patient_allergy){
            array_push($allergies, $this->get_allergy($patient_allergy->getAllergy()));
        }
        
        if (!$patient_allergies) {
//            throw $this->createNotFoundException(
//                'No allergies found'
//            );
            return [];
        }
                
        return $allergies;
    }
    
    private function get_allergy($type_id){
        $repository = $this->getDoctrine()->getRepository('AppBundle:Allergies');
        $allergy = $repository->find($type_id);
        
        if (!$allergy) {
//            throw $this->createNotFoundException(
//                'No address types found'
//            );
            return [];
        }
        
        return $allergy;
    }
    
    private function get_patient_diseases($patient_id){
        $repository = $this->getDoctrine()->getRepository('AppBundle:PatientDiseases');
        $patient_diseases = $repository->findByPatient($patient_id);

        $diseases = array();
        foreach($patient_diseases as $patient_disease){
            array_push($diseases, $this->get_diseases($patient_disease->getDisease()));
        }
        
        if (!$patient_diseases) {
//            throw $this->createNotFoundException(
//                'No diseases found'
//            );
            return [];
        }
        
        return $diseases;
    }
    
    private function get_diseases($type_id){
        $repository = $this->getDoctrine()->getRepository('AppBundle:Diseases');
        $diseases = $repository->find($type_id);
        
        if (!$diseases) {
//            throw $this->createNotFoundException(
//                'No diseases found'
//            );
            return [];
        }
        
        return $diseases;
    }
    
    private function get_patient_emails($patient_id){
        $repository = $this->getDoctrine()->getRepository('AppBundle:PatientEmails');
        $patient_emails = $repository->findByPatient($patient_id);
        
        $email_types = array();
        foreach($patient_emails as $patient_email){
            array_push($email_types, $this->get_email_types($patient_email->getEmailType()));
        }
        
        if (!$patient_emails) {
//            throw $this->createNotFoundException(
//                'No emails found'
//            );
            return [];
        }
        
        return ['types'=>$email_types, 'patient_emails'=>$patient_emails];
    }
    
    private function get_email_types($type_id){
        $repository = $this->getDoctrine()->getRepository('AppBundle:EmailTypes');
        $email_type = $repository->find($type_id);
        
        if (!$email_type) {
//            throw $this->createNotFoundException(
//                "No email types found for type_id = {$type_id}"
//            );
            return [];
        }
        
        return $email_type;
    }
    
    private function get_patient_operations($patient_id){
        $repository = $this->getDoctrine()->getRepository('AppBundle:PatientOperations');
        $patient_operations = $repository->findByPatient($patient_id);
        
        $operations = array();
        foreach($patient_operations as $patient_operation){
            array_push($operations, $this->get_operations($patient_operation->getOperation()));
        }
        
        if (!$patient_operations) {
//            throw $this->createNotFoundException(
//                'No operations found'
//            );
            return [];
        }
        
        return $operations;
    }
 
    private function get_operations($type_id){
        $repository = $this->getDoctrine()->getRepository('AppBundle:Operations');
        $operation = $repository->find($type_id);
        
        if (!$operation) {
//            throw $this->createNotFoundException(
//                'No operation found'
//            );
            return [];
        }
        
        return $operation;
    }
    
    private function get_patient_telephones($patient_id){
        $repository = $this->getDoctrine()->getRepository('AppBundle:PatientTelephones');
        $patient_telephones = $repository->findByPatient($patient_id);
        
        $telephones_types = array();
        foreach($patient_telephones as $patient_telephone){
            array_push($telephones_types, $this->get_telephones_types($patient_telephone->getTeleponeType()));
        }
        
        if (!$patient_telephones) {
//            throw $this->createNotFoundException(
//                'No telephones found'
//            );
            return [];
        }
        
        return ['types'=>$telephones_types, 'patient_telephones'=>$patient_telephones];
    }
    
    private function get_telephones_types($type_id){
        $repository = $this->getDoctrine()->getRepository('AppBundle:TelephoneTypes');
        $telephones_type = $repository->find($type_id);
        
        if (!$telephones_type) {
//            throw $this->createNotFoundException(
//                'No telephone types found'
//            );
            return [];
        }
        
        return $telephones_type;
    }
    
    /**
     * Methods to remove a patient
     * 
     * @param Integer $patient_id Containing patient id
     * @return void
     * @throws Exception NotFoundException
     */
    private function delete_patient($patient_id){
        $this->delete_patient_addresses($patient_id);
        $this->delete_patient_allergies($patient_id);
        $this->delete_patient_diseases($patient_id);
        $this->delete_patient_emails($patient_id);
        $this->delete_patient_operations($patient_id);
        $this->delete_patient_telephones($patient_id);
        
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
    
    private function delete_patient_addresses($patient_id){
        $em = $this->getDoctrine()->getManager();
        $patient_addresses = $em->getRepository('AppBundle:PatientAddress')->findByPatient($patient_id);
        
        if (!$patient_addresses) {
//            throw $this->createNotFoundException(
//                'No patient Addresses found for id ' . $patient_id
//            );
            return false;
        }
        foreach($patient_addresses as $register){
            $em->remove($register);
        }
        $em->flush();
    }
    
    private function delete_patient_allergies($patient_id){
        $em = $this->getDoctrine()->getManager();
        $patient_allergies = $em->getRepository('AppBundle:PatientAllergies')->findByPatient($patient_id);
        
        if (!$patient_allergies) {
//            throw $this->createNotFoundException(
//                'No patient Allergies found for id ' . $patient_id
//            );
            return false;
        }
        
        foreach($patient_allergies as $register){
            $em->remove($register);
        }
        $em->flush();
    }
    
    private function delete_patient_diseases($patient_id){
        $em = $this->getDoctrine()->getManager();
        $patient_diseases = $em->getRepository('AppBundle:PatientDiseases')->findByPatient($patient_id);
        
        if (!$patient_diseases) {
//            throw $this->createNotFoundException(
//                'No patient Diseases found for id ' . $patient_id
//            );
            return false;
        }

        foreach($patient_diseases as $register){
            $em->remove($register);
        }
        $em->flush();
    }
    
    private function delete_patient_emails($patient_id){
        $em = $this->getDoctrine()->getManager();
        $patient_emails = $em->getRepository('AppBundle:PatientEmails')->findByPatient($patient_id);
        
        if (!$patient_emails) {
//            throw $this->createNotFoundException(
//                'No patient Emails found for id ' . $patient_id
//            );
            return false;

        }

        foreach($patient_emails as $register){
            $em->remove($register);
        }
        $em->flush();
    }
    
    private function delete_patient_operations($patient_id){
        $em = $this->getDoctrine()->getManager();
        $patient_operations = $em->getRepository('AppBundle:PatientOperations')->findByPatient($patient_id);
        
        if (!$patient_operations) {
//            throw $this->createNotFoundException(
//                'No patient Operations found for id ' . $patient_id
//            );
            return false;
        }

        foreach($patient_operations as $register){
            $em->remove($register);
        }
        $em->flush();
    }
    
    private function delete_patient_telephones($patient_id){
        $em = $this->getDoctrine()->getManager();
        $patient_telephones = $em->getRepository('AppBundle:PatientTelephones')->findByPatient($patient_id);
        
        if (!$patient_telephones) {
//            throw $this->createNotFoundException(
//                'No patient Operations found for id ' . $patient_id
//            );
            return false;
        }

        foreach($patient_telephones as $register){
            $em->remove($register);
        }
        $em->flush();
    }
    
    /**
     * Method to get all the address Types
     * 
     * @return Array Containing all the types
     */
    private function get_all_address_types(){
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
    private function get_all_telephone_types(){
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
    private function get_all_email_types(){
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
    private function get_all_diseases(){
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
    private function get_all_operations(){
        $operations_Array = array();
        $repository = $this->getDoctrine()->getRepository('AppBundle:Operations');
        $all_operations = $repository->findAll();
        foreach ($all_operations as $operationsField){
            array_push($operations_Array, array('id' => $operationsField->getId(), 'name' => $operationsField->getName(), 'characteristic' => $operationsField->getCharacteristics()));
        }
        
        return $operations_Array;
    }
    
    /**
     * Method to get all the allergies Types
     * 
     * @return Array Containing all the types
     */
    private function get_all_allergies(){
        $allergies_Array = array();
        $repository = $this->getDoctrine()->getRepository('AppBundle:Allergies');
        $all_allergies = $repository->findAll();
        foreach ($all_allergies as $allergiesField){
            array_push($allergies_Array, array('id' => $allergiesField->getId(), 'name' => $allergiesField->getName(), 'characteristic' => $allergiesField->getCharacteristics()));
        }
        
        return $allergies_Array;
    }
    
    /**
     * Method to build a patient Entity and all subEntities based on a request
     * 
     * @param Request $request Containing a patient form
     * @return boolean True if success, false otherwise
     */
    private function build_patient_entities($request){
        $result = true;
        
        $this->patient = new Patients();
        if($request->request->get('id') != null){
            $this->patient->setId($request->request->get('id'));            
        }
        $this->patient->setName($request->request->get('name'));
        $this->patient->setSurname($request->request->get('surname'));
        $this->patient->setAge($request->request->get('age'));
        if($request->request->get('birthday') != null){
            $date = $request->request->get('birthday');
            $date = \DateTime::createFromFormat('Y-m-d', $date);
            $this->patient->setBirthday($date);
        }
        $this->patient->setJob($request->request->get('job'));
        $this->patient->setPhoto(FALSE);
        $this->patient->setEmails(FALSE);
        if($request->request->get('email') != null){
            $this->patient->setEmails(TRUE);
            $this->patientEmails = new PatientEmails();
            $this->patientEmails->setEmail($request->request->get('email'));
            $this->patientEmails->setEmailType($request->request->get('email_type'));
        }
        $this->patient->setAddresses(FALSE);
        if($request->request->get('address') != null){
            $this->patient->setAddresses(TRUE);
            $this->patientAddress = new PatientAddress();
            $this->patientAddress->setAddress($request->request->get('address'));
            $this->patientAddress->setAddressType($request->request->get('address_type'));
        }
        $this->patient->setTelephones(FALSE);
        if($request->request->get('phone') != null){
            $this->patient->setTelephones(TRUE);
            $this->patientTelephones = new PatientTelephones();
            $this->patientTelephones->setNumber($request->request->get('phone'));
            $this->patientTelephones->setTelephoneType($request->request->get('phone_type'));
        }
//        $request->request->get('diseases_type'];
        $this->patient->setDiseases(FALSE);
        $this->patient->setOperations(FALSE);
        if($request->request->get('operations_type') != null){
            $this->patient->setOperations(TRUE);
            $operationType = null;
            $this->patientOperations = array();
            foreach($request->request->get('operations_type') as $operationType){
                $patientOperations_register = new PatientOperations();
                $patientOperations_register->setOperation($operationType);
    //            $patientOperations_register->setComments($request->request->get('allergies_comments'));
                array_push($this->patientOperations, $patientOperations_register);
            }
        }
        $this->patient->setAllergies(FALSE);
        if($request->request->get('allergies_type') != null){
            $this->patient->setAllergies(TRUE);
            $allergiesType = null;
            $this->patientAllergies = array();
            foreach($request->request->get('allergies_type') as $allergiesType){
                $patientAllergies_register = new PatientAllergies();
                $patientAllergies_register->setAllergy($allergiesType);
//              $patientAllergies_register->setComments($request->request->get('allergies_comments'));
                array_push($this->patientAllergies, $patientAllergies_register);
            }
        }
        $this->patient->setNotes($request->request->get('notes'));
        
        return $result;
    }
    
    
//  With form submitting methods
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