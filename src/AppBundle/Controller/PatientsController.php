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

    private function translateId($domain, $id){
        return $this->get('translator')->trans($id, array(), $domain);
    }

    private function getTranslatedSectionName()
    {
        return $this->translateId('base', $this->section_name);
    }
    
    /**
     * @Route("/patients/list/{page}", name="patients-list")
     */
    public function indexAction($page = 1)
    {
        $logger = $this->get('logger');
        
        list($total_patients, $patients_list) = $this->get_all_patients($page);
//        $form = $this->create_addNew_patient_form();
        
        $pages = ((int)($total_patients/$this->maxResults))+(($total_patients%$this->maxResults)==0? 0 : 1);
        
//        $logger->info('Total patients = ' . $total_patients);
//        $logger->info('Pages = ' . $pages);
//        $logger->info('Current page = ' . $page);
        
        return $this->render(
            'patients/list_patients.html.twig', array(
                'error' => $this->error,
                'error_message' => $this->error_message,
                'patients_list' => $patients_list,
                'has_search' => true,
                'search_url' => $this->generateUrl('patients-search'),
                'paginator' => true,
                'pages' => $pages,
                'current_page' => $page,
                'url_paginator' => 'patients-list',
                'is_section' =>true,
                'sections' => [
                    ['url'=>'#', 'name'=>$this->getTranslatedSectionName()]
                ]
            )
        );
    } 
    
    /**
     * @Route("/patients/search", name="patients-search")
     */
    public function searchPatientAction(Request $request)
    {
        $logger = $this->get('logger');

        $result = false;
        $search = $request->query->get('search');
        if($search != null){
            $patients_list = $this->get_all_patients_ByName($search);
            $patients_matched_telephone = $this->get_all_patients_ByTelephone($search);
            $logger->info("TRACE names");
            $logger->info(var_export($patients_list, true));
            $logger->info("TRACE telephones");
            $logger->info(var_export($patients_matched_telephone, true));
            foreach($patients_matched_telephone as $patient_match_telephone){
                $patient_noFound = true;
                foreach($patients_list as $patient_match_name){
                    if($patient_match_name->getId() == $patient_match_telephone->getId()){
                        $patient_noFound = false;
                    }    
                }
                if($patient_noFound){
                    array_push($patients_list, $patient_match_telephone);
                }
            }
            
            $logger->info("TRACE result");
            $logger->info(var_export($patients_list, true));
            
            $result = $this->render(
                'patients/list_patients.html.twig', array(
                    'error' => $this->error,
                    'error_message' => $this->error_message,
                    'patients_list' => $patients_list,
                    'has_search' => true,
                    'search_previous' => $search,
                    'search_url' => $this->generateUrl('patients-search'),
                    'is_section' =>true,
                    'sections' => [
                        ['url'=>  $this->generateUrl('patients-list'), 'name'=>$this->getTranslatedSectionName()],
                        ['url'=>'#', 'name'=>$this->get('translator')->trans('base.global_search', array(), 'base') . ": '{$search}'"]
                    ]
                )
            );
        }
        else {
            $result = $this->redirectToRoute('patients-list');
        }
        
        return $result;
    }
    
    /**
     * @Route("/patients/new", name="patients-new")
     */
    public function createNewPatientAction()
    {
        $all_telephone_types = $this->get_all_telephone_types();
        $all_email_types = $this->get_all_email_types();
        $all_address_types = $this->get_all_address_types();
        $all_allergies_types = $this->get_all_allergies();
        $all_operations_types = $this->get_all_operations();
        
        return $this->render(
            'patients/add_patients.html.twig', array(
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
                    ['url'=>'#','name'=>$this->translateId('patients', 'patients.section_patients_add_form')]
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
        $action = $this->translateId('base', 'base.global_unknown_error');
        $data_correctly_formated = true;

        $this->build_patient_entities($request);
        
        $data_correctly_formated = true;
        if($data_correctly_formated){
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
                $action = $this->translateId('patients', 'patients.section_patients_already_exist');
            }
        } else {
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
                $action = $this->translateId('patients', 'patients.section_could_not_remove');
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
        $action = $this->translateId('base', 'base.global_no_changes_found');
        $changes = false;

        $logger = $this->get('logger');
        
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
                if($this->patientTelephones != null){
                    if($this->patientTelephones->getId() != null){
                        $patientTelephones_to_update = $em->getRepository('AppBundle:PatientTelephones')->find($this->patientTelephones->getId());
                    } else {
                        $patientTelephones_to_update = clone($this->patientTelephones);
                        $em->persist($patientTelephones_to_update);
                    }
                    
                    $oldPhoneType = null;
                    $oldPhoneNumber = null;
                    if(!empty($current_patient['telephones'])){
                        $oldPhoneType = $current_patient['telephones']['patient_telephones'][0]->getTeleponeType();
                        $oldPhoneNumber = $current_patient['telephones']['patient_telephones'][0]->getNumber();
                    }
                    
                    $newPhoneType = $this->patientTelephones->getTeleponeType();
                    if($oldPhoneType != $newPhoneType){
                        $patientTelephones_to_update->setTeleponeType($newPhoneType);
                        $changes = true;
                    }
                    $newPhoneNumber = $this->patientTelephones->getNumber();
                    if($oldPhoneNumber != $newPhoneNumber){
                        $patientTelephones_to_update->setNumber($newPhoneNumber);
                        $changes = true;
                    }
                }
                
                //========= Patient Email Update =======
                if($this->patientEmails != null){
                    if($this->patientEmails->getId() != null){
                        $patientEmails_to_update = $em->getRepository('AppBundle:patientEmails')->find($this->patientEmails->getId());
                    } else {
                        $patientEmails_to_update = clone($this->patientEmails);
                        $em->persist($patientEmails_to_update);
                    }
                    
                    $oldEmailType = null;
                    $oldEmailAddress = null;
                    if(!empty($current_patient['emails'])){
                        $oldEmailType = $current_patient['emails']['patient_emails'][0]->getEmailType();
                        $oldEmailAddress = $current_patient['emails']['patient_emails'][0]->getEmail();
                    }
                    
                    $newEmailType = $this->patientEmails->getEmailType();
                    if($oldEmailType != $newEmailType){
                        $patientEmails_to_update->setEmailType($newEmailType);
                        $changes = true;
                    }
                    $newEmailAddress = $this->patientEmails->getEmail();
                    if($oldEmailAddress != $newEmailAddress){
                        $patientEmails_to_update->setEmail($newEmailAddress);
                        $changes = true;
                    }
                }
                
                //========= Patient Address Update =======
                if($this->patientAddress != null){
                    if($this->patientAddress->getId() != null){
                        $patientAddress_to_update = $em->getRepository('AppBundle:patientAddress')->find($this->patientAddress->getId());
                    } else {
                        $patientAddress_to_update = clone($this->patientAddress);
                        $em->persist($patientAddress_to_update);
                    }
                    
                    $oldAddressType = null;
                    $oldAddressName = null;
                    if(!empty($current_patient['addresses'])){
                        $oldAddressType = $current_patient['addresses']['patient_address'][0]->getAddressType();
                        $oldAddressName = $current_patient['addresses']['patient_address'][0]->getAddress();
                    }
                    
                    $newAddressType = $this->patientAddress->getAddressType();
                    if($oldAddressType != $newAddressType){
                        $patientAddress_to_update->setAddressType($newAddressType);
                        $changes = true;
                    }
                    $newAddressName = $this->patientAddress->getAddress();
                    if($oldAddressName != $newAddressName){
                        $patientAddress_to_update->setAddress($newAddressName);
                        $changes = true;
                    }
                }
                
                //========= Patient Allergies Update =======
//                $logger->info("TRACE edit Allergies");
                $patientAllergy_to_update = array();

                $i = 0;
                $n = 0;
                $patientEntities_to_keep = array();
                $current_allergies_num = count($current_patient['allergies']);
                $new_allergies_num = count($this->patientAllergies);
//                $logger->info("Start, counting newAllergies = {$new_allergies_num}");
                for($n = 0; $n < $new_allergies_num; $n++){
                    $patientEntityFound = false;
                    $newEntity = $this->patientAllergies[$n];
                    $newAllergy = $newEntity->getAllergy();
//                    $logger->info("Searching new Allergy {$newAllergy} in current allergies...");
                    for($i = 0; (($i < $current_allergies_num) && !$patientEntityFound); $i++){
                        //check if the new entity is in the current patient entities
                        $currentEntity = $current_patient['allergies'][$i];
                        if($currentEntity->getId() == $newAllergy){
                            //Entity found in currentPatientEntity, so we can keep it
//                            $logger->info("Found! new {$newAllergy} == current {$currentEntity->getId()}");
                            array_push($patientEntities_to_keep, $currentEntity->getId());
                            $patientEntityFound = true;
                        }
                    }

                    if(!$patientEntityFound){
                        //-1 to prevent when no allergies selected
                        if($newAllergy != -1){
                            //Insert new patientEntity with the current Entity
//                            $logger->info("Not Found! Inserting {$newAllergy} to DB...");
                            array_push($patientEntities_to_keep, $newAllergy);

                            $patientEntity = new PatientAllergies();
                            $patientEntity->setAllergy($newAllergy);
                            $patientEntity->setPatient($this->patient->getId());
                            $em->persist($patientEntity);
                            $changes = true;
                        }
                    }
                }

//                $logger->info("Searching current patientAllergies to delete...");                    
                foreach($current_patient['allergies'] as $currentEntity){
                    if(!in_array($currentEntity->getId(), $patientEntities_to_keep)){
                        //delete $currentPatientEntity
                        $repository = $this->getDoctrine()->getRepository('AppBundle:PatientAllergies');
                        $currentPatientEntity = $repository->findOneByAllergy($currentEntity->getId());
//                        $logger->info("Deleting PatientAllergy -> {$currentPatientEntity->getId()}...");                    
                        $em->remove($currentPatientEntity);
                        $changes = true;
                    }
                }
                
                //========= Patient Operations Update =======
                $patientOperation_to_update = array();

                $i = 0;
                $n = 0;
                $patientEntities_to_keep = array();
                $current_operations_num = count($current_patient['operations']);
                $new_operations_num = count($this->patientOperations);
//                    $logger->info("Start, counting newOperations = {$new_operations_num}");
                for($n = 0; $n < $new_operations_num; $n++){
                    $patientEntityFound = false;
                    $newEntity = $this->patientOperations[$n];
                    $newOperation = $newEntity->getOperation();
//                        $logger->info("Searching new Operation {$newOperation} in current operations...");
                    for($i = 0; (($i < $current_operations_num) && !$patientEntityFound); $i++){
                        //check if the new entity is in the current patient entities
                        $currentEntity = $current_patient['operations'][$i];
                        if($currentEntity->getId() == $newOperation){
                            //Entity found in currentPatientEntity, so we can keep it
//                                $logger->info("Found! new {$newOperation} == current {$currentEntity->getId()}");
                            array_push($patientEntities_to_keep, $currentEntity->getId());
                            $patientEntityFound = true;
                        }
                    }

                    if(!$patientEntityFound){
                        //-1 to prevent when no operations selected
                        if($newOperation != -1){
                            //Insert new patientEntity with the current Entity
//                                $logger->info("Not Found! Inserting {$newOperation} to DB...");
                            array_push($patientEntities_to_keep, $newOperation);

                            $patientEntity = new PatientOperations();
                            $patientEntity->setOperation($newOperation);
                            $patientEntity->setPatient($this->patient->getId());
                            $em->persist($patientEntity);
                            $changes = true;
                        }
                    }
                }

//                    $logger->info("Searching current patientOperations to delete...");                    
                foreach($current_patient['operations'] as $currentEntity){
                    if(!in_array($currentEntity->getId(), $patientEntities_to_keep)){
                        //delete $currentPatientEntity
                        $repository = $this->getDoctrine()->getRepository('AppBundle:PatientOperations');
                        $currentPatientEntity = $repository->findOneByOperation($currentEntity->getId());
//                            $logger->info("Deleting PatientOperation -> {$currentPatientEntity->getId()}...");                    
                        $em->remove($currentPatientEntity);
                        $changes = true;
                    }
                }
                
                if($changes){
                    $em->flush();
                    $result = 'success';
                    $action = $this->generateUrl('patients-show', ['patient_id'=>$this->patient->getId()]);
                }
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
            WHERE p.user = :user_id
            ORDER BY p.name ASC'
        )->setParameter('user_id', $this->get_logged_User_id())
        ->setFirstResult($offset)
        ->setMaxResults($limit);

        $paginator = new Paginator($query);
        
        return [$paginator->count(), $paginator->getIterator()];
    }
    
    /**
     * Method to get all the patients by name
     * @param String $search containing all or a part of the name to search for.
     * @return array Containing all patients that contains $search
     * @throws Exception NotFoundException
     */
    private function get_all_patients_ByName($search){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT p
            FROM AppBundle:Patients p
            WHERE p.name LIKE :search 
            OR p.surname LIKE :search
            AND p.user = :user_id
            ORDER BY p.name ASC'
        )->setParameter('user_id', $this->get_logged_User_id())
        ->setParameter('search', "%" . $search . "%");

        $patients_founded = $query->getResult();
        
        return $patients_founded;
    }
    
    /**
     * Method to get all the patients by telephone number
     * @param String $search containing all or a part of the number to search for.
     * @return array Containing all patients that contains $search
     * @throws Exception NotFoundException
     */
    private function get_all_patients_ByTelephone($search){
        $patients = array();
        $search_copy = $search;
        settype($search_copy, "integer");
        if($search_copy != 0){
            $interval_init = $search;
            $interval_end = $search;
            for($i=strlen($search); $i < 9; $i++){
                $interval_init = $interval_init.'0';
                $interval_end = $interval_end.'9';
            }
            settype($interval_init, "integer");
            settype($interval_end, "integer");

            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery(
                'SELECT p
                FROM AppBundle:Patients p
                WHERE p.id IN (
                    SELECT pt.patient 
                    FROM AppBundle:PatientTelephones pt 
                    WHERE pt.number >= :patients_numbers 
                    AND pt.number <= :patients_numbers_max
                )'
                )->setParameter('patients_numbers', $interval_init)
                ->setParameter('patients_numbers_max', $interval_end);

            $patients = $query->getResult();
        }
        return $patients;
    }
    
    /**
     * Method to get the patient that match with the id
     * 
     * @param Integer $patient_id Containing patient id
     * @return array Associative array of entities, [patient, addresses, allergies, diseases, emails, operations, telephones]
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
        $patient = $patients_repository->findOneBy(
            array('id'=>$patient_id, 'user'=>$this->get_logged_User_id())
        );

        if (!$patient) {
            throw $this->createNotFoundException(
                $this->translateId('patients', 'patients.section_no_patient')
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
        $patient = $em->getRepository('AppBundle:Patients')->findOneBy(
            array('id'=>$patient_id, 'user'=>$this->get_logged_User_id())
        );

        if (!$patient) {
            throw $this->createNotFoundException(
                $this->translateId('patients', 'patients.section_no_patient')
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
        $logger = $this->get('logger');
        
        $this->patient = new Patients($this->get_logged_User_id());
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
            $this->patientEmails->setId($request->request->get('email_id'));
            $this->patientEmails->setPatient($this->patient->getId());
            $this->patientEmails->setEmail($request->request->get('email'));
            $this->patientEmails->setEmailType($request->request->get('email_type'));
        }
        $this->patient->setAddresses(FALSE);
        if($request->request->get('address') != null){
            $this->patient->setAddresses(TRUE);
            $this->patientAddress = new PatientAddress();
            $this->patientAddress->setId($request->request->get('address_id'));
            $this->patientAddress->setPatient($this->patient->getId());
            $this->patientAddress->setAddress($request->request->get('address'));
            $this->patientAddress->setAddressType($request->request->get('address_type'));
        }
        $this->patient->setTelephones(FALSE);
        if($request->request->get('phone') != null){
            $this->patient->setTelephones(TRUE);
            $this->patientTelephones = new PatientTelephones();
            $this->patientTelephones->setId($request->request->get('phone_id'));
            $this->patientTelephones->setPatient($this->patient->getId());
            $this->patientTelephones->setNumber($request->request->get('phone'));
            $this->patientTelephones->setTelephoneType($request->request->get('phone_type'));
        }
//        $request->request->get('diseases_type'];
        $this->patient->setDiseases(FALSE);
        
        $this->patient->setOperations(FALSE);
        $this->patientOperations = array();
        if($request->request->get('operations_type') != null && $request->request->get('operations_type')[0] != -1){
            $this->patient->setOperations(TRUE);
            $operationType = null;
            foreach($request->request->get('operations_type') as $operationType){
                $patientOperations_register = new PatientOperations();
                $patientOperations_register->setPatient($this->patient->getId());
                $patientOperations_register->setOperation($operationType);
    //            $patientOperations_register->setComments($request->request->get('allergies_comments'));
                array_push($this->patientOperations, $patientOperations_register);
            }
        }
        $this->patient->setAllergies(FALSE);
        $this->patientAllergies = array();
        if($request->request->get('allergies_type') != null && $request->request->get('allergies_type')[0] != -1){
            $this->patient->setAllergies(TRUE);
            $allergiesType = null;
            foreach($request->request->get('allergies_type') as $allergiesType){
                $patientAllergies_register = new PatientAllergies();
                $patientAllergies_register->setPatient($this->patient->getId());
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
     * @deprecated since version 0.1
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
        $patient = new Patients($this->get_logged_User_id());
        
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
    
    private function get_logged_User_id(){
        $user = $this->getUser();
        return $user->getId();        
    }
}