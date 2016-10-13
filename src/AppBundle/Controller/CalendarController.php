<?php
// src/AppBundle/Controller/CalendarController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Form\PostType;

class CalendarController extends Controller{
    private $section_name = 'base.global_section_calendar';
    
    private function getTranslatedSectionName(){
        return $this->get('translator')->trans($this->section_name, array(), 'base');
    }
    
    /**
     * @Route("/calendar/month", name="calendar-month")
    */
    public function monthCalendarAction(){

        return $this->render(
            'calendar/month_calendar.html.twig', array( 
                'is_section' =>true,
                'sections' => [['url'=>$this->generateUrl('calendar-month'), 'name'=>$this->getTranslatedSectionName()]],
                'month'=>true, 
                'week'=>false, 
                'day'=>false
            )
        );
    }
    
    /**
     * @Route("/calendar/week", name="calendar-week")
    */
    public function weekCalendarAction(){      
        $translated_section = $this->get('translator')->trans($this->section_name, array(), 'base');

        return $this->render(
            'calendar/week_calendar.html.twig', array(
                'is_section' =>true,
                'sections' => [['url'=>$this->generateUrl('calendar-week'), 'name'=>$this->getTranslatedSectionName()]],
                'month'=>false, 
                'week'=>true, 
                'day'=>false
            )
        );
    }

    /**
     * @Route("/calendar/day", name="calendar-day")
    */
    public function dayCalendarAction(){
        $translated_section = $this->get('translator')->trans($this->section_name, array(), 'base');

        return $this->render(
            'calendar/day_calendar.html.twig', array(
                'is_section' =>true,
                'sections' => [['url'=>$this->generateUrl('calendar-week'), 'name'=>$this->getTranslatedSectionName()]],
                'month'=>false, 
                'week'=>false, 
                'day'=>true)
        );
    }
}