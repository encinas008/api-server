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
class RepresentanteLegal extends MY_Controller {

    public function __construct()
	{
		parent::__construct();

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, OPTIONS, POST");
        header("Access-Control-Allow-Headers: *");

        $this->load->model('Persona_model');
        $this->load->model('DeclaracionJurada_model');
        $this->load->model('Solicitante_model');
        $this->load->model('RepresentanteLegal_model');
        $this->load->model('DatosJuridicos_model');

        $this->load->library('Authorization_Token');
    }

    /**
    * Create a new persona asociate with declaracion jurada
    * --------------------------
    * @param: array representante_legal, datos_juridicos
    * @param: declaracion jurada
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/representante-legal/create
    */
    public function create_post(){
        
        $this->form_validation->set_rules('datos_juridicos[nit]', 'Nit', 'required|trim');

		if( $this->form_validation->run() == true){
            
            $data = $this->security->xss_clean($this->input->post());  # XSS filtering

            $representante_legal = $data["representante_legal"];
            //$persona['persona[id_tipo_documento]'] = $representante_legal['id_tipo_documento'];

            //unset($representante_legal['id_tipo_documento']);
            $out_repre_legal = $this->RepresentanteLegal_model->create( $representante_legal );

            //echo "*********";

            $datos_juridicos = $data["datos_juridicos"];
            $out_datos_juridicos = $this->DatosJuridicos_model->create( $datos_juridicos );

            if($out_repre_legal != null && null !== $out_datos_juridicos){
                //$out_solicitante = $this->saveSolicitante($data, $out_persona);

                $this->response([
                    'status' => true,
                    'message' => $this->lang->line('item_was_has_add'),
                    $this->lang->line('representante_legal') => $out_repre_legal,
                    $this->lang->line('datos_juridicos') => $out_datos_juridicos
                ], REST_Controller::HTTP_OK);

                /*if(null != $out_solicitante){
                    $out_dj = $this->updateDeclaracionJurada($data, $out_solicitante);
                    $this->response([
                        'status' => true,
                        'message' => $this->lang->line('item_was_has_add'),
                        $this->lang->line('persona') => $out_persona,
                        $this->lang->line('solicitante') => $out_solicitante
                    ], REST_Controller::HTTP_OK);
                }else{
                    $this->response([
                        'status' => false,
                        'message' => $this->lang->line('no_item_was_has_add')
                    ], REST_Controller::HTTP_CREATED);
                }*/
            }else{
                $this->response([
                    'status' => false,
                    'message' => $this->lang->line('no_item_was_has_add')
                ], REST_Controller::HTTP_CREATED);
            }
		}else{
            $this->response([
                'status' => false,
                'message' => validation_errors()
            ], REST_Controller::HTTP_ACCEPTED); 
		}
        $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_RESET_CONTENT); // CREATED (201) being the HTTP response code
    }

    private function saveSolicitante($data, $persona){

        if (array_key_exists('solicitante', $data)) {

            $solicitante = $data['solicitante'];
            if(null != $persona )
                $solicitante['id_persona'] =  $persona->id;

            /*if(array_key_exists('datos_juridicos', $data)  )
                $solicitante['id_datos_juridicos'] =  $datos_juridicos->id;*/
            
            return $this->Solicitante_model->create( $solicitante );
        }

        return null;
    }

    private function updateDeclaracionJurada($data, $solicitante){
        if (array_key_exists('declaracion_jurada', $data)) {
            $declaracion_jurada = $data["declaracion_jurada"];
            $db_dj = $this->DeclaracionJurada_model->getByToken($declaracion_jurada['token']);
            $db_dj->id_solicitante = $solicitante->id;
            $out_dj = $this->DeclaracionJurada_model->update( $db_dj );
        }
    }
}