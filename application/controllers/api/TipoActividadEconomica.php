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
class TipoActividadEconomica extends MY_Controller {

    public function __construct()
	{
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, OPTIONS, POST");
        header("Access-Control-Allow-Headers: *");

        header("Content-type:application/json");;

		parent::__construct();
        $this->load->model('TipoActividadEconomica_model');
        $this->load->library('Authorization_Token');
    }

    /**
    * Search Actividad Economica
    * --------------------------
    * @param: $name
    * --------------------------
    * @method: GET
    * @author: RAR
    * @link: api/tipo-actividad-economica/search-by-name/(any)/(any)
    */
    public function getSearchByName_get($name, $derecho_admision){

        $name = $this->security->xss_clean($name);  # XSS filtering
        
        $token = $this->security->xss_clean($this->input->get('auth', TRUE));
        $is_valid_token = $this->authorization_token->validateToken($token);

        if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
            
            $list = $this->TipoActividadEconomica_model->getSearchByName($name, $derecho_admision);

            $this->response([
                'status' => true,
                'message' => $this->lang->line('item_has_found'),
                'data' => $list
            ], REST_Controller::HTTP_OK);
        }else{
            $this->response([
                'status' => false,
                'message' => $this->lang->line('token_is_invalid'),
            ], REST_Controller::HTTP_NETWORK_AUTHENTICATION_REQUIRED); // NOT_FOUND (400) being the HTTP response code
        }
    }
}

