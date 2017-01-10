<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    private $section_name = 'base.global_section_dashboard';

        private function getTranslatedSectionName(){
        return $this->get('translator')->trans($this->section_name, array(), 'base');
    }
    
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        
        return $this->render(
            'default/index.html.twig', array(
                'is_section' =>true,
                'sections' => [
//                    ['url'=>$this->generateUrl('homepage'), 'name'=>$this->getTranslatedSectionName()]
                ]
            )
        );
    }
}