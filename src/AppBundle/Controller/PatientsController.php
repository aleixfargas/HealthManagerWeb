<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Form\PatientsType;
use AppBundle\Entity\Patients;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class PatientsController extends Controller
{
    private $section_name = 'base.global_section_patients';
    
    private $error = false;
    private $error_message = '';

    private function getTranslatedSectionName()
    {
        return $this->get('translator')->trans($this->section_name, array(), 'base');
    }
    
    /**
     * @Route("/patients", name="patients")
     */
    public function indexAction($form = false)
    {
        $patients_list = $this->get_all_patients();
        
        if($form === false){
            $patients = new Patients();
            $form = $this->createForm(PatientsType::class, $patients,
                array(
                    'attr' => ['id'=>'form_new_patient', 'url'=>$this->generateUrl('patients_save')],
//                    'action' => $this->generateUrl('patients_save'),
//                    'method' => 'POST',
                )
            );
        }
        
        return $this->render(
            'patients/patients.html.twig', array(
                'error' => $this->error,
                'error_message' => $this->error_message,
                'form' => $form->createView(),
                'patients_list' => $patients_list,
                'is_section' =>true,
                'sections' => [
                    ['url'=>'#', 'name'=>$this->getTranslatedSectionName()]
                ]
            )
        );
    }
    
    /**
     * @Route("/patients/save", name="patients_save")
     */
    public function savePatient(Request $request){
        $logger = $this->get('logger');

        $result = 'error';
        $action = 'Unknown error';

        $patients = new Patients();
        $form = $this->createForm(PatientsType::class, $patients,
            array(
                'attr' => ['id'=>'form_new_patient', 'url'=>$this->generateUrl('patients_save')],
            )
        );
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $patient = $form->getData();
            
            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($patients);
                $em->flush();
                $action = $this->generateUrl('patients_show', ['patient'=>$patient->getId()]);
                $result = 'success';
            } catch (UniqueConstraintViolationException $e){
                $logger->error($e->getMessage());
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
     * @Route("/patients/{patient_id}", name="patients_show")
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
                    ['url'=>$this->generateUrl('patients'), 'name'=>$this->getTranslatedSectionName()],
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
    private function get_all_patients(){
        $patients_repository = $this->getDoctrine()->getRepository('AppBundle:Patients');
        $patients_list = $patients_repository->findAll();

        if (!$patients_list) {
            throw $this->createNotFoundException(
                'No patients found'
            );
        }
        
        return $patients_list;
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
}