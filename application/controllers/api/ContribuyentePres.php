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
class ContribuyentePres extends MY_Controller {

    public function __construct()
	{
		parent::__construct();

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, OPTIONS, POST");
        header("Access-Control-Allow-Headers: *");
        
        $this->load->model('Prescripcion_model');
        $this->load->model('PresRepresentanteLegal_model');
        $this->load->model('TipoActividadEconomica_model');
        $this->load->model('Solicitante_model');
        $this->load->model('Persona_model');
        $this->load->model('DatosJuridicos_model');
        $this->load->model('RepresentanteLegal_model');
        
        $this->load->library('Authorization_Token');
    }
/*

public function getContribuyenteByToken_get($token){
        $token = $this->security->xss_clean($token_dj);  # XSS filtering

        $token_verified = self::getTokenVerified($token);

        $token = $this->security->xss_clean($this->input->get('auth', TRUE));   //token de authentication
        $is_valid_token = $this->authorization_token->validateToken($token);

        if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
            $out_dj = $this->Prescripcion_model->getByToken($token_verified);

            print_r($out_dj)
            if($out_dj !== null){
                $solicitante = $this->Solicitante_model->getById($out_dj->id_solicitante);
                $representante_legal = $this->RepresentanteLegal_model->getByIdSolicitud($solicitante->id);
                $actividad_economica = $this->ActividadEconomica_model->getById($out_dj->id_actividad_economica);
                $tipo_actividad_economica = $this->TipoActividadEconomica_model->getById($actividad_economica->id_tipo_actividad);

                $datos_juridicos = null;
                $persona = null;
                if(null !== $representante_legal){
                    $persona = $this->Persona_model->getById($representante_legal->id_persona);
                    if( !is_null($solicitante->id_datos_juridicos) ){
                        $datos_juridicos = $this->DatosJuridicos_model->getById($solicitante->id_datos_juridicos);
                    }
                }

                $this->response([
                    'status' => true,
                    'message' => $this->lang->line('item_was_found'),
                    $this->lang->line('declaracion_jurada') => $out_dj,
                    $this->lang->line('solicitante') => $solicitante,
                    $this->lang->line('persona') => $persona,
                    $this->lang->line('datos_juridicos') => $datos_juridicos,
                    $this->lang->line('actividad_economica') => $actividad_economica,
                    $this->lang->line('tipo_actividad_economica') => $tipo_actividad_economica
                ], REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => $this->lang->line('not_item_were_found')
                ], REST_Controller::HTTP_NO_CONTENT); // NOT_FOUND (404) being the HTTP response code
            }
        }else{
            $this->response([
                'status' => false,
                'message' => $this->lang->line('token_is_invalid'),
            ], REST_Controller::HTTP_NETWORK_AUTHENTICATION_REQUIRED); // NOT_FOUND (400) being the HTTP response code
        }

        $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_CREATED);
    }
*/

    public function getContribuyenteByToken_get($token){
        $token = $this->security->xss_clean($token_dj);  # XSS filtering

        $token_verified = self::getTokenVerified($token);
       print_r($token);
         $token = $this->security->xss_clean($this->input->get('auth', TRUE));   //token de authentication
        $is_valid_token = $this->authorization_token->validateToken($token);


    }


    private function getTokenVerified($token){
        if(!is_null($token) && !empty($token) ){
            return $token;
        }else{
            $this->response([
                'status' => false,
                'message' => $this->lang->line('identifier_is_required')
            ], REST_Controller::HTTP_NO_CONTENT); // NOT_FOUND (404) being the HTTP response code
        }
    }

}