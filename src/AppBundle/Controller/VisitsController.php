<?php
// src/AppBundle/Controller/CalendarController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Form\PostType;

use AppBundle\Entity\Patients;
use AppBundle\Entity\Visits;

use Doctrine\ORM\Tools\Pagination\Paginator;

class VisitsController extends Controller{
    private $section_name = 'base.global_section_visits';
    private $maxResults = 14;

    private $error = false;
    private $error_message = '';
    
    private $visit = null;
    
    private $logger = null;

    private function getTranslatedSectionName(){
        return $this->get('translator')->trans($this->section_name, array(), 'base');
    }
    
    /**
     * @Route("/visits/list/{day}", name="visits-list")
    */
    public function listVisitsAction($day = false){
        if($day === false) $day = date("Y-m-d");

        $patients_names = array();
        $visits_list = $this->get_day_visits($day);
        
        foreach($visits_list as $visit){
            array_push($patients_names, $this->get_visit_patient_name($visit->getPatient()));
        }
        
        return $this->render(
            'visits/list_visits.html.twig', array(
                'error' => $this->error,
                'error_message' => $this->error_message,
                'visits_list' => $visits_list,
                'visits_patients_name' => $patients_names,
                'list_date' => $day,
                'is_section' =>true,
                'sections' => [
                    ['url'=>$this->generateUrl('visits-list'), 'name'=>$this->getTranslatedSectionName()]
                ],
            )
        );
    }
    
    /**
     * @Route("/visits/new", name="visits-new")
     */
    public function createNewVisitAction()
    {   
        $patients = $this->get_all_visits_patients();

        return $this->render(
            'visits/add_visits.html.twig', array(
                'all_patients' => $patients,
                'error' => $this->error,
                'error_message' => $this->error_message,
                'is_section' =>true,
                'sections' => [
                    ['url'=>$this->generateUrl('visits-list'), 'name'=>$this->getTranslatedSectionName()],
                    ['url'=>'#', 'name'=>"Add new visit Form"]
                ]
            )
        );
    }
    
    /**
     * @Route("/visits/save", name="visits-save")
     */
    public function saveNewVisitAction(Request $request){
//        $this->logger = $this->get('logger');
        $em = $this->getDoctrine()->getManager();

        $this->build_visit_entity($request);
//        $patient_to_update = $em->getRepository('AppBundle:Visits')->find($this->patient->getId());
        $em->persist($this->visit);
        
        $em->flush();
        $result = 'success';
        $action = $this->generateUrl('visits-show', ['visit_id'=>$this->visit->getId()]);
        
        $response = json_encode(array('status'=>$result, 'action'=>$action));
        return new Response($response);
    }
    
    
    /**
     * @Route("/visits/remove", name="visits-remove")
     */
    public function removeVisitAction(Request $request){
        $visits_array = $request->request->get('visits_array');
        $current_day = $request->request->get('current_day');
        $result = 'error';
        
        foreach ($visits_array as $visit_id){
            try {
                $this->delete_visit($visit_id);
                $result = 'success';
                $action = $this->generateUrl('visits-list', array('day'=>$current_day));
            } catch (NotFoundHttpException $e){
                $logger->error($e->getMessage());
                $result = 'error';
                $action = "Could not remove visit with id $visit_id, try again later!";
            }
        }

        $response = json_encode(array('status'=>$result, 'action'=>$action));
        return new Response($response);
    }
    
    /**
     * @Route("/visits/show/{visit_id}", name="visits-show")
    */
    public function showVisitsAction($visit_id){
        $visit_data =  $this->get_visit($visit_id);
        
        if($visit_data){
            list($visit, $visit_patient) = $visit_data;
            
            $html = $this->render(
                'visits/show_visits.html.twig', array(
                    'error' => $this->error,
                    'error_message' => $this->error_message,
                    'visit' => $visit,
                    'visit_patient_name' => $visit_patient,
                    'is_section' =>true,
                    'sections' => [
                        ['url'=>$this->generateUrl('visits-list'), 'name'=>$this->getTranslatedSectionName()],
                        ['url'=>'#', 'name'=>$visit->getVisitDate()],
                    ],
                )
            );
        } else {
            $html = $this->listVisitsAction();
        }
        
        return $html;
    }
    
    /**
     * @Route("/visits/edit/{visit_id}", name="visits-edit")
     */
    public function editVisitAction($visit_id)
    {
        $this->logger = $this->get('logger');
        
        list($visit, $visit_patient) =  $this->get_visit($visit_id);
        $patients = $this->get_all_visits_patients();
                
        return $this->render(
            'visits/edit_visits.html.twig', array(
                'all_patients' => $patients,
                'visit' => $visit,
                'visit_patient_name' => $visit_patient,
                'error' => $this->error,
                'error_message' => $this->error_message,                
                'is_section' =>true,
                'sections' => [
                    ['url'=>$this->generateUrl('visits-list'), 'name'=>$this->getTranslatedSectionName()],
                    ['url'=>'#', 'name'=>$visit->getVisitDate()],
                ],
            )
        );
    }
    
    /**
     * @Route("/visits/save/edit", name="visits-save-edit")
     */
    public function saveEditVisitAction(Request $request){
        $result = 'error';
        $action = "No modification made";
        $changes = false;

        $em = $this->getDoctrine()->getManager();

        $this->build_visit_entity($request);
        $visit_to_update = $em->getRepository('AppBundle:Visits')->find($this->visit->getId());
        
        $newPatient = $this->visit->getPatient();
        if($visit_to_update->getPatient() != $newPatient){
            $visit_to_update->setPatient($newPatient);
            $changes = true;
        }
        $newPhysiotherapist = $this->visit->getPhysiotherapist();
        if($visit_to_update->getPhysiotherapist() != $newPhysiotherapist){
            $visit_to_update->setPhysiotherapist($newPhysiotherapist);
            $changes = true;
        }
        $newDuration = $this->visit->getDuration();
        if($visit_to_update->getDuration() != $newDuration){
            $visit_to_update->setDuration($newDuration);
            $changes = true;
        }
        $newReason = $this->visit->getReason();
        if($visit_to_update->getReason() != $newReason){
            $visit_to_update->setReason($newReason);
            $changes = true;
        }
        $newComments = $this->visit->getComments();
        if($visit_to_update->getComments() != $newComments){
            $visit_to_update->setComments($newComments);
            $changes = true;
        }
        $newVisitDate = $this->visit->getVisitDate();
        if($visit_to_update->getVisitDate() != $newVisitDate){
            $visit_to_update->setVisitDate($newVisitDate);
            $changes = true;
        }
        $newReservationDate = $this->visit->getReservationDate();
        if($visit_to_update->getReservationDate() != $newReservationDate){
            $visit_to_update->setReservationDate($newReservationDate);
            $changes = true;
        }
        
        if($changes){
            $em->flush();
            $result = 'success';
            $action = $this->generateUrl('visits-show', ['visit_id'=>$this->visit->getId()]);
        } else {
            $action = "No changes found!";
        }
        
        $response = json_encode(array('status'=>$result, 'action'=>$action));
        return new Response($response);
    }
    
    /**
     * @Route("/visits/fetch/allVisitDates/", name="visits-fetch-allVisitDates")
     */
    public function fetch_allVisitDatesAction(Request $request){
        $result = 'error';
        $action = 'No visits found';

        $date = $request->request->get('dateTime');
        if($date != "" && $date !== FALSE && $date != null){
            $format = 'Y-m-d H:i:s';
            $date = \DateTime::createFromFormat($format, $date);

            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery(
                "SELECT v
                FROM AppBundle:Visits v
                WHERE v.visitDate > :start_today
                AND v.visitDate < :end_today
                AND v.user < :user_id
                ORDER BY v.visitDate ASC"
            )->setParameter('start_today', $date->format('Y-m-d 00:00:00'))
            ->setParameter('end_today', $date->format('Y-m-d 23:59:59'))
            ->setParameter('user_id', $this->get_logged_User_id());

            $visits = $query->getResult();

            if($visits){
                $result = 'success';
                $action = array();
                foreach ($visits as $visit){
                    array_push($action, $visit->getVisitDate()->format('Y-m-d H:i:s'));
                }
            }
        }
        
        $response = json_encode(array('status'=>$result, 'action'=>$action));
        return new Response($response);
    }
    
    //================ PRIVATE METHODS ==================
    
    private function build_visit_entity($request){
        $this->visit = new Visits($this->get_logged_User_id());
        if($request->request->get('visit_id') != null){
            $this->visit->setId($request->request->get('visit_id'));            
        }
        $this->visit->setPhysiotherapist(1);
        $this->visit->setDuration(60);
        $this->visit->setPatient($request->request->get('patient'));
        if($request->request->get('visit_date') != null){        
            $date = $request->request->get('visit_date');
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
            $this->visit->setVisitDate($date);
        }
        if($request->request->get('reservation_date') != null){
            $date = $request->request->get('reservation_date');
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
            $this->visit->setReservationDate($date);
        }        
        $this->visit->setReason($request->request->get('reason'));
        $this->visit->setComments($request->request->get('comments'));
        $this->visit->setFee(0);
        $this->visit->setInvoice(0);
    }
    
    private function get_all_visits($page){
        $limit = $this->maxResults;
        $offset = $this->maxResults * ($page-1);

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT v
            FROM AppBundle:Visits v
            ORDER BY v.visitDate ASC'
        )->setFirstResult($offset)->setMaxResults($limit);

        $paginator = new Paginator($query);

        return [$paginator->count(), $paginator->getIterator()];
    }
    
    private function get_day_visits($day){
        $format = 'Y-m-d';
        $logger = $this->get('logger');
        $logger->info($day);
        $date = \DateTime::createFromFormat($format, $day);
        
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            "SELECT v
            FROM AppBundle:Visits v
            WHERE v.visitDate > :from_day
            AND v.visitDate < :to_day
            AND v.user = :user_id
            ORDER BY v.visitDate ASC"
        )->setParameter('from_day', $date->format('Y-m-d 00:00:00'))
        ->setParameter('to_day', $date->format('Y-m-d 23:59:59'))
        ->setParameter('user_id', $this->get_logged_User_id());
                
        $visits = $query->getResult();
        
        return $visits;
    }
    
    private function get_visit($visit_id){
        $result = false;
        $patient_name = false;
        
        $visits_repository = $this->getDoctrine()->getRepository('AppBundle:Visits');
        $visit = $visits_repository->findOneBy(
            array('id'=>$visit_id, 'user'=>$this->get_logged_User_id())
        );
        
        if($visit){
            $patient_name = $this->get_visit_patient_name($visit->getPatient());
            $result = [$visit, $patient_name];
        }
        
        return $result;
    }
    
    private function get_visit_patient_name($patient_id){
        $patient_name = '';
        $patients_repository = $this->getDoctrine()->getRepository('AppBundle:Patients');
        $patient = $patients_repository->find($patient_id);

        if (!$patient) {
            $patient_name = 'No patient yet';
        } else {
            $patient_name = $patient->getName() . " " . $patient->getSurname();
        }
        
        return $patient_name;
    }
    
    /**
     * Methods to remove a visit
     * 
     * @param Integer $visit_id Containing visit id
     * @return void
     * @throws Exception NotFoundException
     */
    private function delete_visit($visit_id){
        $em = $this->getDoctrine()->getManager();
        $visit = $em->getRepository('AppBundle:Visits')->find($visit_id);

        if (!$visit) {
            throw $this->createNotFoundException(
                'No visit found for id ' . $visit_id
            );
        }

        $em->remove($visit);
        $em->flush();
    }
    
    private function get_logged_User_id(){
        $user = $this->getUser();
        return $user->getId();        
    }
    
    private function get_all_visits_patients(){
        $patient_name = '';
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT p
            FROM AppBundle:Patients p
            ORDER BY p.name ASC'
        );
                
        $patients = $query->getResult();
        
        return $patients;
    }
}