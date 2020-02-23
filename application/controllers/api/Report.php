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
class Report extends MY_Controller {

    public function __construct()
	{
		parent::__construct();

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST");
        header("Access-Control-Allow-Headers: *");

        $this->load->model('DeclaracionJurada_model');
        $this->load->model('ActividadEconomica_model');
        $this->load->model('Estado_model');
        $this->load->model('Solicitante_model');
        $this->load->model('Prescripcion_model'); 
        $this->load->model('Persona_model');
        $this->load->library('Authorization_Token');

        $this->config->load('configCiJasper'); //Carga el configurador. Tambien puedes cargarlo automaticamente en el archivo autoload.php con $autoload['config'] = array('configCiJasper');
        $this->load->library('PhpJasperReport/CiJasper');

        $this->load->library('Ciqrcode');
    }

    public function test_get(){

        //$file = $this->cijasper->run('Invoice.jrxml', null,['pdf']);
        $file = $this->cijasper->run('Invoice.jrxml', ['id_dj'=>3],['pdf']);
    
        //$img = file_get_contents( $file);
        //$base64 = base64_encode($img);
    }

    /**
    * Create a new report for declaracion jurada
    * --------------------------
    * @param: token of declaracion_jurada
    * --------------------------
    * @method: get
    * @author: RAR
    * @link: api/report/declaracion-jurada/$1
    */
    /*public function declaracionJurada_get($token){
        
        $token_dj = $this->security->xss_clean($token);

        $token = $this->security->xss_clean($this->input->get('auth', TRUE));   //token de authentication
        $is_valid_token = $this->authorization_token->validateToken($token);

        if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){

            $user = $is_valid_token['data'];
            if(!is_null($token_dj) && !empty($token_dj) ){
                $declaracion_jurada = $this->DeclaracionJurada_model->getByToken($token_dj);
    
                if(null !== $declaracion_jurada){

                    $estado = $this->Estado_model->getById($declaracion_jurada->id_estado);
                    $actividad_economica = $this->ActividadEconomica_model->getById( $declaracion_jurada->id_actividad_economica );

                    if($estado->code === "APROBADO"){

                        #contruimos el codigo QR
                        //"N-"+$F{numero_dj}+"--F-"+$F{fur_dj}+"--RC-"+$F{act_eco_rotulo_comercial}+"--CI-"+$F{numero_documento}
                        $params['data'] = "N-".$declaracion_jurada->numero."--F-".$declaracion_jurada->fur."--RC-".$actividad_economica->rotulo_comercial."--CI-123456";
                        $params['level'] = 'H';
                        $params['size'] = 150;
                        $params['savename'] = FCPATH.'public/'. $declaracion_jurada->token.".png";
                        $this->ciqrcode->generate($params);

                        $img_qr = file_get_contents( FCPATH.'public/'. $declaracion_jurada->token.".png");
                        $base64_qr = base64_encode($img_qr);

                        $param_pdf['id_dj'] = (int)$declaracion_jurada->id;
                        $param_pdf['img_qr'] = strval($base64_qr);
                        $param_pdf['username'] = strval($user->username.substr($user->username, 0, strpos($user->username, '@')));

                        $solicitante = $this->Solicitante_model->getById($declaracion_jurada->id_solicitante);
                        if((int)$solicitante->contribuyente === (int)NATURAL)
                            $file = $this->cijasper->run('licencia_natural.jrxml', $param_pdf,['pdf']);
                        else
                            $file = $this->cijasper->run('licencia_juridica.jrxml', $param_pdf,['pdf']);
    
                        $img = file_get_contents( $file);
                        $base64 = base64_encode($img);

                        $this->response([
                            'status' => true,
                            'message' => $this->lang->line('item_has_found'),
                            'base64' => "data:application/pdf;base64,".$base64,
                            $this->lang->line('declaracion_jurada') => $declaracion_jurada,
                            $this->lang->line('actividad_economica') => $actividad_economica
                        ], REST_Controller::HTTP_OK);
                    }else{
                        if($estado->code === "COMPLETADO"){
                            $solicitante = $this->Solicitante_model->getById($declaracion_jurada->id_solicitante);
                            if((int)$solicitante->contribuyente === (int)NATURAL)
                                $file = $this->cijasper->run('licencia_natural_invalid.jrxml', ['id_dj'=>(int)$declaracion_jurada->id],['pdf']);
                            else
                                $file = $this->cijasper->run('licencia_juridica_invalid.jrxml', ['id_dj'=>(int)$declaracion_jurada->id],['pdf']);

                            $img = file_get_contents( $file);
                            $base64 = base64_encode($img);

                            $this->response([
                                'status' => true,
                                'message' => $this->lang->line('item_has_found'),
                                'base64' => "data:application/pdf;base64,".$base64,
                                $this->lang->line('declaracion_jurada') => $declaracion_jurada,
                                $this->lang->line('actividad_economica') => $actividad_economica
                            ], REST_Controller::HTTP_OK);
                        }else{
                            //estado en proceso
                            $this->response([
                                'status' => true,
                                'message' => $this->lang->line('not_item_were_found'),
                            ], REST_Controller::HTTP_OK);
                        }
                    }

                    //$this->cijasper->removeFile($file); //eliminamos el archivo
                }else{
                    $this->response([
                        'status' => false,
                        'message' => $this->lang->line('identifier_is_required')
                    ], REST_Controller::HTTP_NO_CONTENT); 
                }
            }else{
                $this->response([
                    'status' => false,
                    'message' => $this->lang->line('identifier_is_required')
                ], REST_Controller::HTTP_NO_CONTENT); // NOT_FOUND (404) being the HTTP response code
            }
        }else{
            $this->response([
                'status' => false,
                'message' => $this->lang->line('token_is_invalid'),
            ], REST_Controller::HTTP_NETWORK_AUTHENTICATION_REQUIRED); // NOT_FOUND (400) being the HTTP response code
        }
    }*/

    /**
    * Create a new report for declaracion jurada
    * --------------------------
    * @param: token of declaracion_jurada
    * --------------------------
    * @method: get
    * @author: RAR
    * @link: api/report/declaracion-jurada/$1
    */
    public function declaracionJurada_get($token){
        
        $token_dj = $this->security->xss_clean($token);

        $token = $this->security->xss_clean($this->input->get('auth', TRUE));   //token de authentication
        $is_valid_token = $this->authorization_token->validateToken($token);

        if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){

            $user = $is_valid_token['data'];
            if(!is_null($token_dj) && !empty($token_dj) ){
                $declaracion_jurada = $this->DeclaracionJurada_model->getByToken($token_dj);
    
                if(null !== $declaracion_jurada){

                    $estado = $this->Estado_model->getById($declaracion_jurada->id_estado);
                    $actividad_economica = $this->ActividadEconomica_model->getById( $declaracion_jurada->id_actividad_economica );

                    if($estado->code === "APROBADO"){

                        #contruimos el codigo QR
                        //"N-"+$F{numero_dj}+"--F-"+$F{fur_dj}+"--RC-"+$F{act_eco_rotulo_comercial}+"--CI-"+$F{numero_documento}
                        $params['data'] = "N-".$declaracion_jurada->numero."--F-".$declaracion_jurada->fur."--RC-".$actividad_economica->rotulo_comercial."--CI-123456";
                        $params['level'] = 'H';
                        $params['size'] = 150;
                        $params['savename'] = FCPATH.'public/'. $declaracion_jurada->token.".png";
                        $this->ciqrcode->generate($params);

                        $img_qr = file_get_contents( FCPATH.'public/'. $declaracion_jurada->token.".png");
                        $base64_qr = base64_encode($img_qr);

                        $param_pdf['id_dj'] = (int)$declaracion_jurada->id;
                        $param_pdf['img_qr'] = strval($base64_qr);
                        $param_pdf['username'] = strval($user->username.substr($user->username, 0, strpos($user->username, '@')));

                        $solicitante = $this->Solicitante_model->getById($declaracion_jurada->id_solicitante);
                        if((int)$solicitante->contribuyente === (int)NATURAL)
                            $file = $this->cijasper->run('licencia_natural.jrxml', $param_pdf,['pdf']);
                        else
                            $file = $this->cijasper->run('licencia_juridica.jrxml', $param_pdf,['pdf']);
    
                        $img = file_get_contents( $file);
                        $base64 = base64_encode($img);

                        $this->response([
                            'status' => true,
                            'message' => $this->lang->line('item_has_found'),
                            'base64' => "data:application/pdf;base64,".$base64,
                            $this->lang->line('declaracion_jurada') => $declaracion_jurada,
                            $this->lang->line('actividad_economica') => $actividad_economica
                        ], REST_Controller::HTTP_OK);
                    }else{
                        if($estado->code === "COMPLETADO"){
                            $solicitante = $this->Solicitante_model->getById($declaracion_jurada->id_solicitante);
                            if((int)$solicitante->contribuyente === (int)NATURAL)
                                $file = $this->cijasper->run('licencia_natural_invalid.jrxml', ['id_dj'=>(int)$declaracion_jurada->id],['pdf']);
                            else
                                $file = $this->cijasper->run('licencia_juridica_invalid.jrxml', ['id_dj'=>(int)$declaracion_jurada->id],['pdf']);

                            $img = file_get_contents( $file);
                            $base64 = base64_encode($img);

                            $this->response([
                                'status' => true,
                                'message' => $this->lang->line('item_has_found'),
                                'base64' => "data:application/pdf;base64,".$base64,
                                $this->lang->line('declaracion_jurada') => $declaracion_jurada,
                                $this->lang->line('actividad_economica') => $actividad_economica
                            ], REST_Controller::HTTP_OK);
                        }else{
                            //estado en proceso
                            $this->response([
                                'status' => true,
                                'message' => $this->lang->line('not_item_were_found'),
                            ], REST_Controller::HTTP_OK);
                        }
                    }
                    /*$file = $this->cijasper->run('licencia_natural.jrxml', ['id_dj'=>(int)$declaracion_jurada->id],['pdf']);
    
                    $img = file_get_contents( $file);
                    $base64 = base64_encode($img);*/
    
                    //$this->cijasper->removeFile($file); //eliminamos el archivo
                }else{
                    $this->response([
                        'status' => false,
                        'message' => $this->lang->line('identifier_is_required')
                    ], REST_Controller::HTTP_NO_CONTENT); 
                }
            }else{
                $this->response([
                    'status' => false,
                    'message' => $this->lang->line('identifier_is_required')
                ], REST_Controller::HTTP_NO_CONTENT); // NOT_FOUND (404) being the HTTP response code
            }
        }else{
            $this->response([
                'status' => false,
                'message' => $this->lang->line('token_is_invalid'),
            ], REST_Controller::HTTP_NETWORK_AUTHENTICATION_REQUIRED); // NOT_FOUND (400) being the HTTP response code
        }

        
    }
     /// public function prescripcion_get($id,$nit){
     public function prescripcionPdf_get($token_p){

     //print_r($token_p);
        $prescripcion = $this->Prescripcion_model->getByToken($token_p);
        $persona = $this->Persona_model->getByIdPrescripcion($prescripcion->id);

          $param_pdf['id'] = $persona->id;

            $file = $this->cijasper->run('natural.jrxml', $param_pdf,['pdf']); 

            $img = file_get_contents($file);
            $base64 = base64_encode($img);

            $this->response([
                'status' => true,
               'message' => $this->lang->line('item_has_found'),
                'base64' => "data:application/pdf;base64,".$base64
            ], REST_Controller::HTTP_OK);
        
 
    }
}
