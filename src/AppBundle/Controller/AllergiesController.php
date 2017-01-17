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
    
    private $allergy = null;
    
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
                'url_paginator' => 'allergies-list',
                'is_section' =>true,
                'sections' => [
                    ['url'=>$this->generateUrl('allergies-list'), 'name'=>$this->getTranslatedSectionName()]
                ],
            )
        );
    }
    
    /**
     * @Route("/allergies/save/", name="allergies-save")
    */
    public function saveNewAllergiesAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $this->build_allergy_entity($request);
        
        $em->persist($this->allergy);
        $em->flush();
        $result = 'success';
        $action = $this->generateUrl('allergies-list');
        
        $response = json_encode(array('status'=>$result, 'action'=>$action));
        return new Response($response);
    }
    
    /**
     * @Route("/allergies/remove", name="allergies-remove")
     */
    public function removeAllergyAction(Request $request){
        $allergies_array = $request->request->get('allergies_array');
        $result = 'error';
        $action = "One or various patients have this allergy. If you really want to delete it, first remove it from all the patients!";
        
        foreach ($allergies_array as $allergy_id){
            try {
                if($this->delete_allergy($allergy_id)){
                    $result = 'success';
                    $action = $this->generateUrl('allergies-list');
                }
            } catch (NotFoundHttpException $e){
                $logger->error($e->getMessage());
                $action = "Could not remove allergy with id {$allergy_id}, try again later!";
            }
        }

        $response = json_encode(array('status'=>$result, 'action'=>$action));
        return new Response($response);
    }
    
    //================ PRIVATE METHODS ==================
    
    private function get_all_allergies($page){
        $limit = $this->maxResults;
        $offset = $this->maxResults * ($page-1);

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT a
            FROM AppBundle:Allergies a
            WHERE a.user = :user_id
            ORDER BY a.name ASC'
        )->setParameter('user_id', $this->get_logged_User_id())
        ->setFirstResult($offset)
        ->setMaxResults($limit);

        $paginator = new Paginator($query);

        return [$paginator->count(), $paginator->getIterator()];
    }
    
    private function build_allergy_entity($request){
        $this->allergy = new Allergies($this->get_logged_User_id());
        if($request->request->get('allergy_id') != null){
            $this->allergy->setId($request->request->get('allergy_id'));            
        }
        if($request->request->get('allergy_name') != null){
            $this->allergy->setName($request->request->get('allergy_name'));            
        }
        if($request->request->get('allergy_characteristics') != null){
            $this->allergy->setCharacteristics($request->request->get('allergy_characteristics'));            
        }
    }
    
    private function get_allergy($allergy_id){
        $result = false;
        $patient_name = false;
        
        $allergies_repository = $this->getDoctrine()->getRepository('AppBundle:Allergies');
        $allergy = $allergies_repository->findOneBy(
            array('id'=>$allergy_id, 'user'=>$this->get_logged_User_id())
        );
        
        if($allergy){
//            $patient_name = $this->get_visit_patient_name($allergy->getPatient());
            $result = $allergy;
        } 
        
        return $result;
    }
    
    /**
     * Methods to remove an allergy
     * 
     * @param Integer $allergy_id Containing visit id
     * @return Boolean True if success, false if any patient set with this allergy
     * @throws Exception NotFoundException If no Allergy found
     */
    private function delete_allergy($allergy_id){
        $result = false;
        
        $em = $this->getDoctrine()->getManager();
        $patients_with_allergies = $em->getRepository('AppBundle:PatientAllergies')->findBy(
            array('allergy'=>$allergy_id)
        );
        if(count($patients_with_allergies) === 0){
            $allergy = $em->getRepository('AppBundle:Allergies')->findOneBy(
                array('id'=>$allergy_id, 'user'=>$this->get_logged_User_id())
            );

            if (!$allergy) {
                throw $this->createNotFoundException(
                    'No visit found for id ' . $allergy_id
                );
            }

            $em->remove($allergy);
            $em->flush();
            $result = true;
        }
        
        return $result;
    }
    
    private function get_logged_User_id(){
        $user = $this->getUser();
        return $user->getId();        
    }
}