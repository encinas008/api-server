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
class Domicilio extends MY_Controller {

    public function __construct()
	{
		parent::__construct();

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, OPTIONS, POST");
        header("Access-Control-Allow-Headers: *");

        $this->load->model('Persona_model');
        $this->load->model('Domicilio_model');
        $this->load->model('DeclaracionJurada_model');
        $this->load->model('Solicitante_model');
        $this->load->model('RepresentanteLegal_model');
        $this->load->model('Usuario_model');

        $this->load->library('Authorization_Token');

        $this->load->library('Util');

        $this->form_validation->set_error_delimiters('', '');

        $this->form_validation->set_message('required', 'El campo %s es obligatorio');
        $this->form_validation->set_message('integer', 'El campo %s deve poseer solo numeros enteros');
        $this->form_validation->set_message('is_unique', 'El campo %s ya esta registrado');
        $this->form_validation->set_message('max_length', 'El Campo %s debe tener un Maximo de %d Caracteres');
    }

    /**
    * get Domicilio by token declaracion jurada
    * --------------------------
    * @param: array persona
    * @param: declaracion jurada
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/domicilio/token-lic/(:any)
    */
    public function getDomicilioByTokenDJ_get($token_dj){
        $token = $this->security->xss_clean($token_dj);  # XSS filtering

        $token_verified = self::getTokenVerified($token);

        $token = $this->security->xss_clean($this->input->get('auth', TRUE));   //token de authentication
        $is_valid_token = $this->authorization_token->validateToken($token);

        if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
            $out_dj = $this->DeclaracionJurada_model->getByToken($token_verified);
            if($out_dj !== null){
                $solicitante = $this->Solicitante_model->getById($out_dj->id_solicitante);
                $representante_legal = $this->RepresentanteLegal_model->getByIdSolicitud($solicitante->id);

                $persona = null;
                if($representante_legal){
                    $persona = $this->Persona_model->getById($representante_legal->id_persona);
                    $domicilio = $this->Domicilio_model->getByIdPersona($persona->id);
                }else{
                    $persona = null;
                    $domicilio = null;
                }
		
		        $this->response([
	               'status' => true,
	               'message' => $this->lang->line('item_was_found'),
	               $this->lang->line('declaracion_jurada') => $out_dj,
	               $this->lang->line('solicitante') => $solicitante,
	               $this->lang->line('persona') => $persona,
	               $this->lang->line('domicilio') => $domicilio
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

    /**
    * Create a new domicilio with persona
    * --------------------------
    * @param: identificador de persona
    * @param: array domicilio
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/domicilio/create
    */
    public function create_post(){

        $this->form_validation->set_rules('domicilio[latitud]', 'Latitud del Domicilio', 'required|trim');
        $this->form_validation->set_rules('domicilio[longitud]', 'Longitud del Domicilio', 'required|trim');
        $this->form_validation->set_rules('domicilio[image]', 'Imagen del Mapa', 'required|trim');

		if( $this->form_validation->run() == true){
            
            $data = $this->security->xss_clean($this->input->post());  # XSS filtering

            $token = $this->security->xss_clean($this->input->post('auth', TRUE));   //token de authentication
            $is_valid_token = $this->authorization_token->validateToken($token);

            if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
                $usuario = $this->Usuario_model->getByTokenJWT($is_valid_token['data']);
                $domicilio = $data["domicilio"];

                if (array_key_exists('persona', $data)) {
                    $domicilio['id_persona'] = $data["persona"]['id'];
                }
                $domicilio['id_usuario'] = $usuario->id;
                $out_domicilio = $this->Domicilio_model->create( $domicilio );

                if($out_domicilio != null){

                    $this->response([
                        'status' => true,
                        'message' => $this->lang->line('item_was_has_add'),
                        $this->lang->line('domicilio') => $out_domicilio
                    ], REST_Controller::HTTP_OK);
                }else{
                    $this->response([
                        'status' => false,
                        'message' => $this->lang->line('no_item_was_has_add')
                    ], REST_Controller::HTTP_CREATED);
                }
            }else{
                $this->response([
                    'status' => false,
                    'message' => $this->lang->line('token_is_invalid'),
                ], REST_Controller::HTTP_NETWORK_AUTHENTICATION_REQUIRED); // NOT_FOUND (400) being the HTTP response code
            }
		}else{
            $this->response([
                'status' => false,
                'message' => validation_errors()
            ], REST_Controller::HTTP_ACCEPTED); 
		}
        $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_RESET_CONTENT);
    }

    /**
    * Update domicilio with persona
    * --------------------------
    * @param: array domicilio
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/domicilio/update
    */
    public function update_post(){

        $this->form_validation->set_rules('domicilio[latitud]', 'Latitud', 'required|trim');
        $this->form_validation->set_rules('domicilio[longitud]', 'Longitud', 'required|trim');
        $this->form_validation->set_rules('domicilio[coordinate]', 'Coordenadas', 'required|trim|strtoupper');
        $this->form_validation->set_rules('domicilio[image]', 'Imagen Ubicación de Domicilio', 'required|trim|callback_image_update_validate');

		if( $this->form_validation->run() == true){
            
            $data = $this->security->xss_clean($this->input->post());  # XSS filtering

            $token = $this->security->xss_clean($this->input->post('auth', TRUE));   //token de authentication
            $is_valid_token = $this->authorization_token->validateToken($token);

            if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
                $usuario = $this->Usuario_model->getByTokenJWT($is_valid_token['data']);

                $in_declaracion_jurada = $data['declaracion_jurada'];
                $in_domicilio = $data['domicilio'];
                
                $declaracion_jurada = $this->DeclaracionJurada_model->getByToken($in_declaracion_jurada['token']);
                if($declaracion_jurada != null){

                    $solicitante = $this->Solicitante_model->getById($declaracion_jurada->id_solicitante);
                    
                    if(null !== $solicitante){
                        $representante_legal = $this->RepresentanteLegal_model->getByIdSolicitud($solicitante->id);
                        if( !is_null($representante_legal ) ){
                            $persona = $this->Persona_model->getById($representante_legal->id_persona);

                            $domicilio = $this->Domicilio_model->getByIdPersona($persona->id);

                            if(null === $domicilio){
                                $domicilio = $data["domicilio"];
                                $domicilio['id_persona'] = $persona->id;
                                $domicilio['id_usuario'] = $usuario->id;
                                $domicilio = $this->Domicilio_model->create( $domicilio );

                                $this->response([
                                    'status' => true,
                                    'message' => $this->lang->line('item_was_has_add'),
                                    $this->lang->line('persona') => $persona,
                                    $this->lang->line('domicilio') => $out_domicilio
                                ], REST_Controller::HTTP_OK);
                            }else{
                                $domicilio->latitud  = $in_domicilio['latitud'];
                                $domicilio->longitud = $in_domicilio['longitud'];
                                $domicilio->coordinate  = $in_domicilio['coordinate'];

                                if( array_key_exists('image', $in_domicilio) && !empty($in_domicilio['image']) )
                                    $domicilio->image = $in_domicilio ['image'];

                                $out_domicilio = $this->Domicilio_model->update( $domicilio );

                                $this->response([
                                    'status' => true,
                                    'message' => $this->lang->line('item_was_has_add'),
                                    $this->lang->line('persona') => $persona,
                                    $this->lang->line('domicilio') => $out_domicilio
                                ], REST_Controller::HTTP_OK);
                            }
                        }else{
                            $this->response([
                                'status' => false,
                                'message' => $this->lang->line('date_contribuyente_not_found')
                            ], REST_Controller::HTTP_OK);
                        }
                    }
                }else{
                    $this->response([
                        'status' => false,
                        'message' => $this->lang->line('no_item_was_has_add')
                    ], REST_Controller::HTTP_CREATED);
                }
            }else{
                $this->response([
                    'status' => false,
                    'message' => $this->lang->line('token_is_invalid'),
                ], REST_Controller::HTTP_NETWORK_AUTHENTICATION_REQUIRED);
            }
		}else{
            $this->response([
                'status' => false,
                'message' => validation_errors()
            ], REST_Controller::HTTP_ACCEPTED); 
		}
        $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_RESET_CONTENT);
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

    public function image_update_validate($image)
    {
        if(!is_null($image) && !empty($image) ){

            if(!$this->util->is_base64($image)){
                $this->form_validation->set_message('image_validate', 'La imagen enviada no es valida, se requiere una imagen en formato Base64 Valida');
                return FALSE;
            }
        }

        return TRUE;
    }
}
