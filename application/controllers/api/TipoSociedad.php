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
class TipoSociedad extends MY_Controller {

    public function __construct()
	{
		parent::__construct();
        $this->load->model('TipoSociedad_model');
        header("Access-Control-Allow-Origin: *");
    }

    public function getAll_get()
    {
        $list = $this->TipoSociedad_model->getAll();
        $this->response([
            'status' => true,
            'message' => $this->lang->line('item_has_found'),
            $this->lang->line('tipo_sociedad') => $list
        ], REST_Controller::HTTP_OK);
    }
}
