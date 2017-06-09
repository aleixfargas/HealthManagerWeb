<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    private $logger;
    private $section_name = 'base.global_section_dashboard';

        private function getTranslatedSectionName(){
        return $this->get('translator')->trans($this->section_name, array(), 'base');
    }
    
    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function indexAction(Request $request)
    {
        $this->logger = $this->get('logger');
        $this->logger->info('Trace 0');
        $VisitsRepository = $this->getDoctrine()->getRepository('AppBundle:Visits');
        $PatientsRepository = $this->getDoctrine()->getRepository('AppBundle:Patients');
        
        $nextVisit = $VisitsRepository->getNextVisit();
        $patient_names = array();
        foreach ($nextVisit as $visit){
            $patient = $PatientsRepository->get_patient_name($visit->getPatient());
            $patient_name = "";
            if (!$patient) {
                $patient_name = $this->get('translator')->trans('visits.section_unknow_patient', array(), 'visits');
            } else {
                $patient_name = $patient->getName() . " " . $patient->getSurname();
            }
            array_push($patient_names, $patient_name);
        }
        
        $this->logger->info(var_export($nextVisit, true));
        return $this->render(
            'default/index.html.twig', array(
                'next_visit' =>$nextVisit,
                'next_visit_patient_names' =>$patient_names,
                'is_section' =>true,
                'sections' => [
//                    ['url'=>$this->generateUrl('homepage'), 'name'=>$this->getTranslatedSectionName()]
                ]
            )
        );
    }
    
    
}