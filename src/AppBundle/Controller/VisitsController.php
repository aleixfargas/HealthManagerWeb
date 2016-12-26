<?php
// src/AppBundle/Controller/CalendarController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Form\PostType;

use AppBundle\Entity\Patients;
use AppBundle\Entity\Visits;

use Doctrine\ORM\Tools\Pagination\Paginator;

class VisitsController extends Controller{
    private $section_name = 'base.global_section_visits';
    private $maxResults = 6;

    private $error = false;
    private $error_message = '';

    private function getTranslatedSectionName(){
        return $this->get('translator')->trans($this->section_name, array(), 'base');
    }
    
    /**
     * @Route("/visits/list/{page}", name="visits-list")
    */
    public function listVisitsAction($page = 1){
        $patients_names = array();
        
        list($total_visits, $visits_list) = $this->get_all_visits($page);
        $pages = ((int)($total_visits/$this->maxResults))+(($total_visits%$this->maxResults)==0? 0 : 1);
        
        foreach($visits_list as $visit){
            array_push($patients_names, $this->get_visit_patient_name($visit->getPatient()));
        }
        
        return $this->render(
            'visits/list_visits.html.twig', array(
                'error' => $this->error,
                'error_message' => $this->error_message,
                'pages' => $pages,
                'current_page' => $page,
                'visits_list' => $visits_list,
                'visits_patients_name' => $patients_names,
                'is_section' =>true,
                'sections' => [
                    ['url'=>$this->generateUrl('visits-list'), 'name'=>$this->getTranslatedSectionName()]
                ]
            )
        );
    }
    
    public function get_all_visits($page){
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
    
    public function get_visit_patient_name($patient_id){
        $patient_name = '';
        $patients_repository = $this->getDoctrine()->getRepository('AppBundle:Patients');
        $patient = $patients_repository->find($patient_id);

        if (!$patient) {
            $patient_name = 'No patient found';
        } else {
            $patient_name = $patient->getName() . " " . $patient->getSurname();
        }
        
        return $patient_name;
    }
}