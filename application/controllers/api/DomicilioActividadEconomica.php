<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller for Domicilio Activiad Economica, this controller extends for MY_Controller
 *
 * @package         restServerAtm
 * @subpackage      TipoDocumento
 * @category        Controller
 * @author          Ronald Acha Ramos
 * @license         MIT
 */
class DomicilioActividadEconomica extends MY_Controller {

    public function __construct()
	{
		parent::__construct();

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, OPTIONS, POST");
        header("Access-Control-Allow-Headers: *");

        $this->load->model('DomicilioActividadEconomica_model');
        $this->load->model('ActividadEconomica_model');
        $this->load->model('DeclaracionJurada_model');
        $this->load->library('Authorization_Token');
        $this->load->model('Usuario_model');

        $this->load->library('Util');

        $this->form_validation->set_error_delimiters('', '');

        $this->form_validation->set_message('required', 'El campo %s es obligatorio');
        $this->form_validation->set_message('integer', 'El campo %s deve poseer solo numeros enteros');
        $this->form_validation->set_message('is_unique', 'El campo %s ya esta registrado');
        $this->form_validation->set_message('max_length', 'El Campo %s debe tener un Maximo de %d Caracteres');
    }

    /**
    * Get Domicilio Actividad by token licencia funcionamiento
    * --------------------------
    * @param: identificador de persona
    * @param: array domicilio
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/domicilio-actividad-economica/get-by-tokey-lic
    */
    public function getByTokenDJ_get($token_dj){
        $token = $this->security->xss_clean($token_dj);  # XSS filtering

        $token_verified = self::getTokenVerified($token);

        $token = $this->security->xss_clean($this->input->get('auth', TRUE));   //token de authentication
        $is_valid_token = $this->authorization_token->validateToken($token);

        if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
            $out_dj = $this->DeclaracionJurada_model->getByToken($token_verified);
            if($out_dj !== null){
                $actividad_economica = $this->ActividadEconomica_model->getById($out_dj->id_actividad_economica);
                $domicilio_actividad_economica = $this->DomicilioActividadEconomica_model->getByIdActividadEconomica($actividad_economica->id);
                
                $this->response([
                    'status' => true,
                    'message' => $this->lang->line('item_was_found'),
                    $this->lang->line('declaracion_jurada') => $out_dj,
                    $this->lang->line('actividad_economica') => $actividad_economica,
                    $this->lang->line('domicilio_actividad_economica') => $domicilio_actividad_economica
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
    * Create a new domicilio actividad economica with persona
    * --------------------------
    * @param: identificador de persona
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/domicilio-actividad-economica/create
    */
    public function create_post(){

        $this->form_validation->set_rules('domicilio_actividad_economica[direccion]', 'Direccion', 'required|trim|strtoupper');
        $this->form_validation->set_rules('domicilio_actividad_economica[celular]', 'Teléfono Móvil', 'required|trim|strtoupper');
        $this->form_validation->set_rules('domicilio_actividad_economica[direccion]', 'Direccion', 'required|trim|strtoupper');
        $this->form_validation->set_rules('domicilio_actividad_economica[zona]', 'Zona Tributaria', 'required|trim|strtoupper');
        $this->form_validation->set_rules('domicilio_actividad_economica[image]', 'Imagen ubicación de la Actividad Economica', 'required|trim');

        $this->form_validation->set_rules('actividad_economica[fecha_inicio]', 'Fecha de Inicio', 'required|trim|strtoupper');
        $this->form_validation->set_rules('actividad_economica[rotulo_comercial]', 'Rotulo Comercial', 'required|trim|strtoupper');
        $this->form_validation->set_rules('actividad_economica[superficie]', 'Superficie', 'required|trim|strtoupper');
        $this->form_validation->set_rules('actividad_economica[num_inmueble]', 'Número Ruat', 'required|trim|strtoupper');
        $this->form_validation->set_rules('actividad_economica[comuna]', 'Comuna', 'required|trim|strtoupper');
        $this->form_validation->set_rules('actividad_economica[distrito]', 'Distrito', 'required|trim|strtoupper');
        $this->form_validation->set_rules('actividad_economica[sub_distrito]', 'Sub-Distrito', 'required|trim|strtoupper');

		if( $this->form_validation->run() == true){
            
            $data = $this->security->xss_clean($this->input->post());  # XSS filtering

            $token = $this->security->xss_clean($this->input->post('auth', TRUE));   //token de authentication
            $is_valid_token = $this->authorization_token->validateToken($token);

            if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){

                $usuario = $this->Usuario_model->getByTokenJWT($is_valid_token['data']);
                $domicilio_act_eco = $data["domicilio_actividad_economica"];

                if (array_key_exists('declaracion_jurada', $data)) {
                    $declaracion_jurada = $this->DeclaracionJurada_model->getByToken($data["declaracion_jurada"]['token']);
                    if(null !== $declaracion_jurada){
                        $actividad_economica = $this->ActividadEconomica_model->getById($declaracion_jurada->id_actividad_economica);

                        $out_act_economica = self::saveActividadEconomica($data, $actividad_economica);

                        $domicilio_act_eco['id_actividad_economica'] = $actividad_economica->id;
                        $domicilio_act_eco['id_usuario'] = $usuario->id;
                        $out_domicilio_act_eco = $this->DomicilioActividadEconomica_model->create( $domicilio_act_eco );

                        if($out_domicilio_act_eco != null){

                            $this->response([
                                'status' => true,
                                'message' => $this->lang->line('item_was_has_add'),
                                $this->lang->line('domicilio_actividad_economica') => $out_domicilio_act_eco,
                                $this->lang->line('actividad_economica') => $out_act_economica
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
                            'message' => $this->lang->line('no_item_was_has_add')
                        ], REST_Controller::HTTP_CREATED);
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
                ], REST_Controller::HTTP_NETWORK_AUTHENTICATION_REQUIRED); // NOT_FOUND (400) being the HTTP response code
            }
		}else{
            $this->response([
                'status' => false,
                'message' => validation_errors()
            ], REST_Controller::HTTP_ACCEPTED); 
		}
        $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_RESET_CONTENT); // CREATED (201) being the HTTP response code
    }

    /**
    * Create a new domicilio actividad economica with persona
    * --------------------------
    * @param: identificador de persona
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/domicilio-actividad-economica/update
    */
    public function update_post(){
        $this->form_validation->set_rules('domicilio_actividad_economica[direccion]', 'Direccion', 'required|trim|strtoupper');
        $this->form_validation->set_rules('domicilio_actividad_economica[celular]', 'Teléfono Móvil', 'required|trim|strtoupper');
        $this->form_validation->set_rules('domicilio_actividad_economica[direccion]', 'Direccion', 'required|trim|strtoupper');
        $this->form_validation->set_rules('domicilio_actividad_economica[zona]', 'Zona Tributaria', 'required|trim|strtoupper');
        $this->form_validation->set_rules('domicilio_actividad_economica[image]', 'Imagen ubicación de la Actividad Economica', 'trim|callback_image_update_validate');
        //$this->form_validation->set_rules('domicilio_actividad_economica[image]', 'Imagen ubicación de la Actividad Economica', 'required|callback_image_update_validate['.$this->input->post()["declaracion_jurada"]["token"].']');

        $this->form_validation->set_rules('actividad_economica[fecha_inicio]', 'Fecha de Inicio', 'required|trim|strtoupper');
        $this->form_validation->set_rules('actividad_economica[rotulo_comercial]', 'Rotulo Comercial', 'required|trim|strtoupper');
        $this->form_validation->set_rules('actividad_economica[superficie]', 'Superficie', 'required|trim|strtoupper');
        $this->form_validation->set_rules('actividad_economica[num_inmueble]', 'Número Ruat', 'required|trim|strtoupper');
        $this->form_validation->set_rules('actividad_economica[comuna]', 'Comuna', 'required|trim|strtoupper');
        $this->form_validation->set_rules('actividad_economica[distrito]', 'Distrito', 'required|trim|strtoupper');
        $this->form_validation->set_rules('actividad_economica[sub_distrito]', 'Sub-Distrito', 'required|trim|strtoupper');

		if( $this->form_validation->run() == true){
            $data = $this->security->xss_clean($this->input->post());  # XSS filtering

            $token = $this->security->xss_clean($this->input->post('auth', TRUE));   //token de authentication
            $is_valid_token = $this->authorization_token->validateToken($token);

            if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
                $usuario = $this->Usuario_model->getByTokenJWT($is_valid_token['data']);
                $in_declaracion_jurada = $data['declaracion_jurada'];
                $in_actividad_economica = $data['actividad_economica'];
                $in_domicilio_actividad_economica = $data['domicilio_actividad_economica'];
                
                $declaracion_jurada = $this->DeclaracionJurada_model->getByToken($in_declaracion_jurada['token']);
                if($declaracion_jurada != null){

                    $out_actividad_economica =  self::updateActividadEconomica($declaracion_jurada, $in_actividad_economica);

                    if(null !==  $out_actividad_economica ){
                        
                        $out_domiclio_act_eco = self::updateDomicilioActividadEconomica($data, $out_actividad_economica, $in_domicilio_actividad_economica, $usuario);

                        $this->response([
                            'status' => true,
                            'message' => $this->lang->line('item_was_has_add'),
                            $this->lang->line('actividad_economica') => $out_actividad_economica,
                            $this->lang->line('domicilio_actividad_economica') => $out_domiclio_act_eco
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

    function saveActividadEconomica($data, $actividad_economica){

        if(array_key_exists( 'domicilio_actividad_economica', $data) ){
            $in_actividad_economica = $data["actividad_economica"];

            $actividad_economica->superficie = $in_actividad_economica['superficie'];
            $actividad_economica->fecha_inicio = $in_actividad_economica['fecha_inicio'];
            $actividad_economica->predio = $in_actividad_economica['predio'];
            $actividad_economica->catastro = $in_actividad_economica['catastro'];
            $actividad_economica->rotulo_comercial = $in_actividad_economica['rotulo_comercial'];
            $actividad_economica->num_inmueble = $in_actividad_economica['num_inmueble'];
            $actividad_economica->comuna = $in_actividad_economica['comuna'];
            $actividad_economica->distrito = $in_actividad_economica['distrito'];
            $actividad_economica->sub_distrito = $in_actividad_economica['sub_distrito'];

            return $this->ActividadEconomica_model->update( $actividad_economica );
        }
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

    private function updateActividadEconomica($declaracion_jurada, $in_actividad_economica){
        $actividad_economica = $this->ActividadEconomica_model->getById($declaracion_jurada->id_actividad_economica);
                
        $actividad_economica->rotulo_comercial = $in_actividad_economica ['rotulo_comercial'];
        $actividad_economica->superficie = $in_actividad_economica ['superficie'];
        $actividad_economica->fecha_inicio = $in_actividad_economica ['fecha_inicio'];
        $actividad_economica->predio = $in_actividad_economica ['predio'];
        $actividad_economica->catastro = $in_actividad_economica ['catastro'];
        $actividad_economica->num_inmueble = $in_actividad_economica ['num_inmueble']; 
        $actividad_economica->comuna = $in_actividad_economica ['comuna'];
        $actividad_economica->distrito = $in_actividad_economica ['distrito'];
        $actividad_economica->sub_distrito = $in_actividad_economica ['sub_distrito'];

        return $this->ActividadEconomica_model->update( $actividad_economica );
    }

    private function updateDomicilioActividadEconomica($data, $actividad_economica, $in_domicilio_actividad_economica, $usuario){
        $domicilio_actividad_economica = $this->DomicilioActividadEconomica_model->getByIdActividadEconomica( $actividad_economica->id);

        if( null !== $domicilio_actividad_economica){
            $domicilio_actividad_economica->avenida =  array_key_exists('avenida', $in_domicilio_actividad_economica) ? $in_domicilio_actividad_economica['avenida'] : false;
            $domicilio_actividad_economica->calle =  array_key_exists('calle', $in_domicilio_actividad_economica) ? $in_domicilio_actividad_economica['calle'] : false;
            $domicilio_actividad_economica->pasaje =  array_key_exists('pasaje', $in_domicilio_actividad_economica) ? $in_domicilio_actividad_economica['pasaje'] : false;
            $domicilio_actividad_economica->plaza_plazuela =  array_key_exists('plaza_plazuela', $in_domicilio_actividad_economica) ? $in_domicilio_actividad_economica['plaza_plazuela'] : false;
            $domicilio_actividad_economica->direccion = $in_domicilio_actividad_economica ['direccion'];
            $domicilio_actividad_economica->numero = $in_domicilio_actividad_economica ['numero'];
            $domicilio_actividad_economica->zona = $in_domicilio_actividad_economica ['zona'];
            $domicilio_actividad_economica->edificio = $in_domicilio_actividad_economica ['edificio'];

            $domicilio_actividad_economica->bloque = array_key_exists('bloque', $in_domicilio_actividad_economica) ? $in_domicilio_actividad_economica['bloque'] : $domicilio_actividad_economica->bloque;
            $domicilio_actividad_economica->piso = array_key_exists('piso', $in_domicilio_actividad_economica) ? $in_domicilio_actividad_economica['piso'] : $domicilio_actividad_economica->piso;
            $domicilio_actividad_economica->dpto_of_local = array_key_exists('dpto_of_local', $in_domicilio_actividad_economica) ? $in_domicilio_actividad_economica['dpto_of_local'] : $domicilio_actividad_economica->piso;
            $domicilio_actividad_economica->telefono = $in_domicilio_actividad_economica ['telefono'];
            $domicilio_actividad_economica->celular = $in_domicilio_actividad_economica ['celular'];

            $domicilio_actividad_economica->latitud = $in_domicilio_actividad_economica ['latitud'];
            $domicilio_actividad_economica->longitud = $in_domicilio_actividad_economica ['longitud'];
            $domicilio_actividad_economica->coordinate = $in_domicilio_actividad_economica ['coordinate'];

            if( array_key_exists('image', $in_domicilio_actividad_economica) && !empty($in_domicilio_actividad_economica ['image']) )
                $domicilio_actividad_economica->image = $in_domicilio_actividad_economica ['image'];

            return $this->DomicilioActividadEconomica_model->update( $domicilio_actividad_economica );
        }else{
            $domicilio_act_eco = $data["domicilio_actividad_economica"];
            $domicilio_act_eco['id_actividad_economica'] = $actividad_economica->id;
            $domicilio_act_eco['id_usuario'] = $usuario->id;
            return $this->DomicilioActividadEconomica_model->create( $domicilio_act_eco );
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
