<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Form\PatientsType;
use AppBundle\Entity\Patients;

class PatientsController extends Controller
{
    private $section_name = 'base.global_section_patients';

    private function getTranslatedSectionName()
    {
        return $this->get('translator')->trans($this->section_name, array(), 'base');
    }
    
    /**
     * @Route("/patients", name="patients")
     */
    public function indexAction(Request $request)
    {
        $patients = new Patients();
        $form = $this->createForm(PatientsType::class, $patients, array(
            'action' => $this->generateUrl('patients_save'),
            'method' => 'GET',
        ));
        
        return $this->render(
            'patients/patients.html.twig', array(
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
        return $this->redirect($this->generateUrl('patients'));
    }

    /**
     * @Route("/patients/{patient}", name="patients_show")
     */
    public function showPatientAction($patient)
    {
        return $this->render(
            'patients/show_patients.html.twig', array(
                'patient_id'=>$patient,
                'is_section' =>true,
                'sections' => [
                    ['url'=>$this->generateUrl('patients'), 'name'=>$this->getTranslatedSectionName()],
                    ['url'=>'#','name'=>$patient]
                ]
            )
        );
    }
}