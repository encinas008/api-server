<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller for user, this controller extends for MY_Controller
 *
 * @package         restServerAtm
 * @subpackage      Genericos
 * @category        Controller
 * @author          Ronald Acha Ramos
 * @license         MIT
 */
class Genericos extends MY_Controller {

    public function __construct()
	{
		parent::__construct();
        $this->load->model('EstadoCivil_model');
        $this->load->model('Nacionalidad_model');
        $this->load->model('Genero_model');
        $this->load->model('CiExpedido_model');
    }

    public function getAllEstadoCivil_get()
    {
        header("Access-Control-Allow-Origin: *");
        
        $list = $this->EstadoCivil_model->getAll();

        $this->response([
            'status' => true,
            'message' => $this->lang->line('item_has_found'),
            'estadoCivil' => $list
            //$this->lang->line('estado_civil') => $result
        ], REST_Controller::HTTP_OK);
    }

    public function getAllNacionalidad_get()
    {
        header("Access-Control-Allow-Origin: *");
        
        $list = $this->Nacionalidad_model->getAll();

        $this->response([
            'status' => true,
            'message' => $this->lang->line('item_has_found'),
            'nacionalidad' => $list
            //$this->lang->line('estado_civil') => $result
        ], REST_Controller::HTTP_OK);
    }

    public function getAllGenero_get()
    {
        header("Access-Control-Allow-Origin: *");
        
        $list = $this->Genero_model->getAll();

        $this->response([
            'status' => true,
            'message' => $this->lang->line('item_has_found'),
            'genero' => $list
            //$this->lang->line('estado_civil') => $result
        ], REST_Controller::HTTP_OK);
    }

    public function getAllCiExpedido_get()
    {
        header("Access-Control-Allow-Origin: *");
        
        $list = $this->CiExpedido_model->getAll();

        $this->response([
            'status' => true,
            'message' => $this->lang->line('item_has_found'),
            'ci_expedido' => $list
        ], REST_Controller::HTTP_OK);
    }
}