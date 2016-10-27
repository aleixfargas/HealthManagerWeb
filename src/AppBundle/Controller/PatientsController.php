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
        $action = 0;

        $patients = new Patients();
        $form = $this->createForm(PatientsType::class, $patients,
            array(
                'attr' => ['id'=>'form_new_patient', 'url'=>$this->generateUrl('patients_save')],
//                'action' => $this->generateUrl('patients_save'),
//                'method' => 'POST',
            )
        );
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $patients = $form->getData();

            try {
                $logger->info('Trace Doctrine OK');
                $em = $this->getDoctrine()->getManager();
                $em->persist($patients);
                $em->flush();
                $action = $this->generateUrl('patients_show', ['patient'=>$patients->getId()]);
                $result = 'success';
            } catch (UniqueConstraintViolationException $e){
                $this->error_message = $e->getMessage();
            }
        } else {
            $logger->info('Trace unique exception');
            $action = $form->createView();
        }
                
        $logger->info('result = ' . $result);
        $logger->info('action = ' . $action);

        $response = json_encode(array('status'=>$result, 'action'=>$action));
        
        if($response) $logger->info('Response = ' . $response);
        else $logger->info('json_decode returns false!');
        
        return new Response($response);
    }

    /**
     * @Route("/patients/{patient}", name="patients_show")
     */
    public function showPatientAction($patient)
    {
        return $this->render(
            'patients/show_patients.html.twig', array(
                'patient_id'=>$patient,
                'error' => $this->error,
                'error_message' => $this->error_message,
                'is_section' =>true,
                'sections' => [
                    ['url'=>$this->generateUrl('patients'), 'name'=>$this->getTranslatedSectionName()],
                    ['url'=>'#','name'=>$patient]
                ]
            )
        );
    }
}