<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Controller for user, this controller extends for MY_Controller
 *
 * @package         restServerAtm
 * @subpackage      Cobros
 * @category        Controller
 * @author          Ronald Acha Ramos
 * @license         MIT
 */

class Recaudaciones extends MY_Controller
{
    private $param;
    protected $baseurl;
    //protected $db_recaudaciones;
    //protected $estados = array(0 => array('key' => 'AC', 'value' => 'Activo'), 1 => array('key' => 'IN', 'value' => 'Inactivo'));

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        !$this->load->library('form_validation') ? $this->load->library('form_validation') : false;

        $baseurl = base_url();

        !$this->db_recaudaciones = $this->load->database('recaudaciones_db', TRUE) ? $this->db_recaudaciones = $this->load->database('recaudaciones_db', TRUE) : false;

        //$this->db_recaudaciones = $this->load->database('recaudaciones_db', FALSE);
    }

    /**
    * Search Persona By Ci
    * --------------------------
    * @param: ci
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/recaudaciones/persona-by-ci/$1/
    */
    public function getPersonaByCi_get($ci){
        header("Access-Control-Allow-Origin: *");

       //echo ".....";
       //echo "$ci";
        if(!is_null($ci) && !empty($ci)  ){
            //echo "hola";

            $this->db_recaudaciones->select('*');
            $this->db_recaudaciones->from('persona_natural');
            $this->db_recaudaciones->where('ci', $ci);
            $this->db_recaudaciones->where('estado', 'AC'); 
            $query = $this->db_recaudaciones->get();
       
            $persona_natural = $query->row();
               
            if(null !== $persona_natural){
                $this->response([
                    'status' => true,
                    'message' =>  $this->lang->line('item_has_found'),
                    'persona_natural' => $query->row()
                ], REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    'status' => false,
                    'message' => $this->lang->line('not_item_were_found')
                ], REST_Controller::HTTP_OK);
            }
        }else{
            $this->response([
                'status' => false,
                'message' => $this->lang->line('not_item_were_found')
            ], REST_Controller::HTTP_CREATED);
        }

        $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }

    /**
    * Search Persona By Nit
    * --------------------------
    * @param: nit
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/recaudaciones/persona-by-nit
    */
    public function getPersonaByNit_get($nit){
        header("Access-Control-Allow-Origin: *");

        if(!is_null($nit) && !empty($nit)  ){

            $this->db_recaudaciones->select('persona_juridica.*');
            $this->db_recaudaciones->from('persona_juridica');
            $this->db_recaudaciones->join('persona_natural', 'persona_natural.ci = persona_juridica.representante_legal');
            $this->db_recaudaciones->where('nit', $nit);
            $this->db_recaudaciones->where('persona_natural.estado', 'AC'); 
            $query_juridico = $this->db_recaudaciones->get();

            $persona_juridica = $query_juridico->row();

            if(null !== $persona_juridica){
                $this->db_recaudaciones->select('*');
                $this->db_recaudaciones->from('persona_natural');
                $this->db_recaudaciones->where('ci', $persona_juridica->representante_legal);
                $this->db_recaudaciones->where('persona_natural.estado', 'AC'); 
                $query_natural = $this->db_recaudaciones->get();

                $representante_legal = $query_natural->row();

                $this->response([
                    'status' => true,
                    'message' =>  $this->lang->line('item_has_found'),
                    'persona_juridica' => $persona_juridica,
                    'representante_legal' => $representante_legal
                ], REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    'status' => false,
                    'message' => $this->lang->line('not_item_were_found')
                ], REST_Controller::HTTP_CREATED);
            }
        }else{
            $this->response([
                'status' => false,
                'message' => $this->lang->line('not_item_were_found')
            ], REST_Controller::HTTP_CREATED);
        }

        $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }
}


