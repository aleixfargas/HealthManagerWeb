<?php
// src/AppBundle/Controller/CalendarController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Form\PostType;

use AppBundle\Entity\Allergies;
use AppBundle\Entity\PatientAllergies;

use Doctrine\ORM\Tools\Pagination\Paginator;

class AllergiesController extends Controller{
    private $section_name = 'base.global_section_allergies';
    private $maxResults = 6;

    private $error = false;
    private $error_message = '';
    
    private $alergies = null;
    
    private $logger = null;

    private function getTranslatedSectionName(){
        return $this->get('translator')->trans($this->section_name, array(), 'base');
    }
    
    /**
     * @Route("/allergies/list/{page}", name="allergies-list")
    */
    public function listAllergiesAction($page = 1){        
        list($total_allergies, $allergies_list) = $this->get_all_allergies($page);
        $pages = ((int)($total_allergies/$this->maxResults))+(($total_allergies%$this->maxResults)==0? 0 : 1);
        
        return $this->render(
            'allergies/list_allergies.html.twig', array(
                'error' => $this->error,
                'error_message' => $this->error_message,
                'allergies_list' => $allergies_list,
                'pages' => $pages,
                'current_page' => $page,
                'url_paginator' => 'visits-list',
                'is_section' =>true,
                'sections' => [
                    ['url'=>$this->generateUrl('allergies-list'), 'name'=>$this->getTranslatedSectionName()]
                ],
            )
        );
    }
    
    
    /**
     * @Route("/allergies/new/", name="allergies-new")
    */
    public function newAllergiesAction(Request $request){        
        list($total_allergies, $allergies_list) = $this->get_all_allergies($page);
        $pages = ((int)($total_allergies/$this->maxResults))+(($total_allergies%$this->maxResults)==0? 0 : 1);
        
        return $this->render(
            'allergies/list_allergies.html.twig', array(
                'error' => $this->error,
                'error_message' => $this->error_message,
                'allergies_list' => $allergies_list,
                'pages' => $pages,
                'current_page' => $page,
                'url_paginator' => 'visits-list',
                'is_section' =>true,
                'sections' => [
                    ['url'=>$this->generateUrl('allergies-list'), 'name'=>$this->getTranslatedSectionName()]
                ],
            )
        );
    }
    
    //================ PRIVATE METHODS ==================
    
    private function get_all_allergies($page){
        $limit = $this->maxResults;
        $offset = $this->maxResults * ($page-1);

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT a
            FROM AppBundle:Allergies a
            ORDER BY a.name ASC'
        )->setFirstResult($offset)->setMaxResults($limit);

        $paginator = new Paginator($query);

        return [$paginator->count(), $paginator->getIterator()];
    }
    
    private function get_allergy($allergy_id){
        $result = false;
        $patient_name = false;
        
        $allergies_repository = $this->getDoctrine()->getRepository('AppBundle:Allergies');
        $allergy = $allergies_repository->find($allergy_id);
        
        if($allergy){
            $patient_name = $this->get_visit_patient_name($allergy->getPatient());
            $result = $allergy;
        } 
        
        return $result;
    }
    
    /**
     * Methods to remove an allergy
     * 
     * @param Integer $allergy_id Containing visit id
     * @return void
     * @throws Exception NotFoundException
     */
    private function delete_allergy($allergy_id){
        $em = $this->getDoctrine()->getManager();
        $allergy = $em->getRepository('AppBundle:Allergies')->find($allergy_id);

        if (!$allergy) {
            throw $this->createNotFoundException(
                'No visit found for id ' . $allergy_id
            );
        }

        $em->remove($allergy);
        $em->flush();
    }
}