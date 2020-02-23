<?php


use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller for user, this controller extends for MY_Controller
 *
 * @package         restServerAtm
 * @subpackage      ContadorIntento
 * @category        Controller
 * @author          Ronald Acha Ramos
 * @license         MIT
 */
class ContadorIntento extends MY_Controller {

    public function __construct()
	{
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, OPTIONS, POST");
        header("Access-Control-Allow-Headers: *");

        header("Content-type:application/json");;

		parent::__construct();
        $this->load->model('ContadorIntento_model');
        $this->load->model('Usuario_model');
        $this->load->library('Authorization_Token');
    }

    /**
    * Count the number intents
    * --------------------------
    * @param: $name
    * --------------------------
    * @method: GET
    * @author: RAR
    * @link: api/contador-intento/licencia-actividad-economica
    */
    public function getTotalLicenciaActividadEconomica_get(){

        //$page = $this->security->xss_clean($page);  # XSS filtering
        //$per_page = $this->security->xss_clean($per_page);  # XSS filtering

        $token = $this->security->xss_clean($this->input->get('auth', TRUE));   //token de authentication
            $is_valid_token = $this->authorization_token->validateToken($token);

            if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
                $usuario = $this->Usuario_model->getByTokenJWT($is_valid_token['data']);
                //print_r($usuario);

                $total = $this->ContadorIntento_model->getTotalLicenciaActividadEconomica($usuario->id);

                $this->response([
                    'status' => true,
                    'message' =>  $this->lang->line('item_has_found'),
                    $this->lang->line('total') => $total
                ], REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    'status' => false,
                    'message' => $this->lang->line('token_is_invalid'),
                ], REST_Controller::HTTP_NETWORK_AUTHENTICATION_REQUIRED); // NOT_FOUND (400) being the HTTP response code
            }
    }
}