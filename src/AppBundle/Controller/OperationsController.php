<?php
// src/AppBundle/Controller/CalendarController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Form\PostType;

use AppBundle\Entity\Operations;
use AppBundle\Entity\PatientOperations;

use Doctrine\ORM\Tools\Pagination\Paginator;

class OperationsController extends Controller{
    private $section_name = 'base.global_section_operations';
    private $maxResults = 6;

    private $error = false;
    private $error_message = '';
    
    private $operation = null;
    
    private $logger = null;
    
    
    private function translateId($domain, $id){
        return $this->get('translator')->trans($id, array(), $domain);
    }
    
    private function getTranslatedSectionName(){
        return $this->translateId('base', $this->section_name);
    }
    
    /**
     * @Route("/operations/list/{page}", name="operations-list")
    */
    public function listOperationsAction($page = 1){        
        list($total_operations, $operations_list) = $this->get_all_operations($page);
        $pages = ((int)($total_operations/$this->maxResults))+(($total_operations%$this->maxResults)==0? 0 : 1);
        
        return $this->render(
            'operations/list_operations.html.twig', array(
                'error' => $this->error,
                'error_message' => $this->error_message,
                'operations_list' => $operations_list,
                'pages' => $pages,
                'current_page' => $page,
                'url_paginator' => 'operations-list',
                'is_section' =>true,
                'sections' => [
                    ['url'=>$this->generateUrl('operations-list'), 'name'=>$this->getTranslatedSectionName()]
                ],
            )
        );
    }
    
    /**
     * @Route("/operations/save/", name="operations-save")
    */
    public function saveNewOperationsAction(Request $request){        
        $em = $this->getDoctrine()->getManager();
        $this->build_operation_entity($request);
        
        $em->persist($this->operation);
        $em->flush();
        $result = 'success';
        $action = $this->generateUrl('operations-list');
        
        $response = json_encode(array('status'=>$result, 'action'=>$action));
        return new Response($response);
    }
    
    /**
     * @Route("/operations/remove", name="operations-remove")
     */
    public function removeOperationAction(Request $request){
        $operations_array = $request->request->get('operations_array');
        $result = 'error';
        $action = $this->translateId('operations', 'operations.section_multiple_assigns_error');
        
        foreach ($operations_array as $operation_id){
            try {
                if($this->delete_operation($operation_id)){
                    $result = 'success';
                    $action = $this->generateUrl('operations-list');
                }
            } catch (NotFoundHttpException $e){
                $logger->error($e->getMessage());
                $result = 'error';
                $action = $this->translateId('operations', 'operations.section_could_not_remove_error');
            }
        }

        $response = json_encode(array('status'=>$result, 'action'=>$action));
        return new Response($response);
    }
    
    //================ PRIVATE METHODS ==================
    
    private function get_all_operations($page){
        $limit = $this->maxResults;
        $offset = $this->maxResults * ($page-1);

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT a
            FROM AppBundle:Operations a
            WHERE a.user = :user_id
            ORDER BY a.name ASC'
        )->setParameter('user_id', $this->get_logged_User_id())
        ->setFirstResult($offset)
        ->setMaxResults($limit);

        $paginator = new Paginator($query);

        return [$paginator->count(), $paginator->getIterator()];
    }
    
    private function build_operation_entity($request){
        $this->operation = new Operations($this->get_logged_User_id());
        if($request->request->get('operation_id') != null){
            $this->operation->setId($request->request->get('operation_id'));            
        }
        if($request->request->get('operation_name') != null){
            $this->operation->setName($request->request->get('operation_name'));            
        }
        if($request->request->get('operation_characteristics') != null){
            $this->operation->setCharacteristics($request->request->get('operation_characteristics'));            
        }
    }
    
    private function get_operation($operation_id){
        $result = false;
        $patient_name = false;
        
        $operations_repository = $this->getDoctrine()->getRepository('AppBundle:Operations');
        $operation = $operations_repository->findOneBy(
            array('id'=>$operation_id, 'user'=>$this->get_logged_User_id())
        );
        
        if($operation){
//            $patient_name = $this->get_visit_patient_name($operation->getPatient());
            $result = $operation;
        } 
        
        return $result;
    }
    
    /**
     * Methods to remove an operation
     * 
     * @param Integer $operation_id Containing operation id
     * @return Boolean True if success, false if any patient set with this allergy
     * @throws Exception NotFoundException
     */
    private function delete_operation($operation_id){
        $result = false;

        $em = $this->getDoctrine()->getManager();
        $patients_with_operations = $em->getRepository('AppBundle:PatientOperations')->findBy(
            array('operation'=>$operation_id)
        );
        if(count($patients_with_operations) === 0){
            $operation = $em->getRepository('AppBundle:Operations')->findOneBy(
                array('id'=>$operation_id, 'user'=>$this->get_logged_User_id())
            );

            if (!$operation) {
                throw $this->createNotFoundException(
                    $this->translateId('operations', 'operations.section_no_operation_found') . ': ' . $operation_id
                );
            }

            $em->remove($operation);
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