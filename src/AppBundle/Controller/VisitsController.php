<?php
// src/AppBundle/Controller/CalendarController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Form\PostType;

class VisitsController extends Controller{
    private $section_name = 'base.global_section_visits';
    
    private function getTranslatedSectionName(){
        return $this->get('translator')->trans($this->section_name, array(), 'base');
    }
    
    /**
     * @Route("/visits/list", name="visits-list")
    */
    public function listVisitsAction(){

        return $this->render(
            'calendar/month_calendar.html.twig', array( 
                'month'=>true, 
                'week'=>false, 
                'day'=>false,
                'is_section' =>true,
                'sections' => [
                    ['url'=>$this->generateUrl('visits-list'), 'name'=>$this->getTranslatedSectionName()]
                ]
            )
        );
    }
}