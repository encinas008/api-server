<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller for user, this controller extends for MY_Controller
 *
 * @package         restServerAtm
 * @subpackage      TipoDocumento
 * @category        Controller
 * @author          Ronald Acha Ramos
 * @license         MIT
 */
class TipoDocumento extends MY_Controller {

    public function __construct()
	{
		parent::__construct();
        $this->load->model('TipoDocumento_model');
    }

    public function getAll_get()
    {
        header("Access-Control-Allow-Origin: *");
        $list = $this->TipoDocumento_model->getAll();
        $this->response([
            'status' => true,
            'message' => $this->lang->line('item_has_found'),
            $this->lang->line('tipo_documento') => $list
        ], REST_Controller::HTTP_OK);
    }

    public function getAllPrescripcion_get()
    {
         header("Access-Control-Allow-Origin: *");

         $list = $this->TipoDocumento_model->getAllPrescripcion();
          $this->response([
            'status' => true,
            'message' => $this->lang->line('item_has_found'),
            $this->lang->line('tipo_documento') => $list
        ], REST_Controller::HTTP_OK);

    }
}

