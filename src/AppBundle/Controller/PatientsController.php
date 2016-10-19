<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PatientsController extends Controller
{
    private $section_name = 'base.global_section_patients';

    private function getTranslatedSectionName(){
        return $this->get('translator')->trans($this->section_name, array(), 'base');
    }
    
    /**
     * @Route("/patients", name="patients")
     */
    public function indexAction(Request $request)
    {
        return $this->render(
            'patients/patients.html.twig', array(
                'is_section' =>true,
                'sections' => [['url'=>$this->generateUrl('patients'), 'name'=>$this->getTranslatedSectionName()]]
            )
        );
    }
}