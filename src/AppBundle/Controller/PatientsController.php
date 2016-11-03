<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Form\PatientsType;
use AppBundle\Entity\Patients;

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
        $form = $this->create_addNew_patient_form();
        
        $pages = ((int)($total_patients/$this->maxResults))+(($total_patients%$this->maxResults)==0? 0 : 1);
        
        $logger->info('Total patients = ' . $total_patients);
        $logger->info('Pages = ' . $pages);
        $logger->info('Current page = ' . $page);
        
        return $this->render(
            'patients/list_patients.html.twig', array(
                'error' => $this->error,
                'error_message' => $this->error_message,
                'form' => $form->createView(),
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

        $form = $this->create_addNew_patient_form();
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $patient = $form->getData();
            
            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($patient);
                $em->flush();
                $action = $this->generateUrl('patients-show', ['patient_id'=>$patient->getId()]);
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
//        $patients_repository = $this->getDoctrine()->getRepository('AppBundle:Patients');
//        $patients_list = $patients_repository->findAll();

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT p
            FROM AppBundle:Patients p
            ORDER BY p.name ASC'
        )->setFirstResult($offset)->setMaxResults($limit);

//        $patients_list = $query->getResult();
        $paginator = new Paginator($query);
        
//        if (!$patients_list) {
//            throw $this->createNotFoundException(
//                'No patients found'
//            );
//        }
        
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
     * Method to get a patient form
     * 
     * @param Integer $patient Containing a patient Entity
     * @return Object Containing the form
     */
    private function create_addNew_patient_form(){
        $patient = new Patients();
        $form = $this->createForm(PatientsType::class, $patient,
            array(
                'attr' => ['id'=>'form_new_patient', 'url'=>$this->generateUrl('patients-save')],
//                    'action' => $this->generateUrl('patients_save'),
//                    'method' => 'POST',
            )
        );
        
        return $form;
    }
}