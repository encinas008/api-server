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
class Persona extends MY_Controller {

    public function __construct()
	{
		parent::__construct();

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST");
        header("Access-Control-Allow-Headers: *");

        $this->load->model('Persona_model');
        $this->load->model('DeclaracionJurada_model');
        $this->load->model('Solicitante_model');
        $this->load->model('DatosJuridicos_model');
        $this->load->model('RepresentanteLegal_model');
        $this->load->model('Usuario_model');

        $this->load->library('Authorization_Token');

        $this->form_validation->set_error_delimiters('', '');

        $this->form_validation->set_message('required', 'El campo %s es obligatorio');
        $this->form_validation->set_message('integer', 'El campo %s deve poseer solo numeros enteros');
        $this->form_validation->set_message('is_unique', 'El campo %s ya esta registrado');
        $this->form_validation->set_message('max_length', 'El Campo %s debe tener un Maximo de %d Caracteres');
    }

    /**
    * Create a new persona asociate with declaracion jurada
    * --------------------------
    * @param: array persona
    * @param: declaracion jurada
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/persona/create
    */
    public function create_post(){

        $this->form_validation->set_rules('persona[id_tipo_documento]', 'Tipo Documento', 'required|trim|strtoupper');
        $this->form_validation->set_rules('persona[expedido_en]', 'Lugar Donde fue expedido el CI', 'required|trim|strtoupper');  //esto controlar, solo para tipo documento CI
        $this->form_validation->set_rules('persona[numero_documento]', 'Número Documento', 'required|trim|strtoupper');
        $this->form_validation->set_rules('persona[nacionalidad]', 'Nacionalidad', 'required|trim|strtoupper');
        $this->form_validation->set_rules('persona[nombre]', 'Nombre', 'required|trim|strtoupper');
        $this->form_validation->set_rules('persona[apellido_paterno]', 'Apellido Paterno', 'required|trim|strtoupper');
        $this->form_validation->set_rules('persona[estado_civil]', 'Estado Civil', 'required|trim|strtoupper');
        $this->form_validation->set_rules('persona[genero]', 'Género', 'required|trim|strtoupper');
        $this->form_validation->set_rules('persona[fecha_nacimiento]', 'Fecha de Nacimiento', 'required|trim|strtoupper');

        if (array_key_exists('datos_juridicos', $this->input->post())) {
            $this->form_validation->set_rules('datos_juridicos[nit]', 'NIT', 'required|trim|strtoupper');
            $this->form_validation->set_rules('datos_juridicos[razon_social]', 'Razón Social', 'required|trim|strtoupper');
            $this->form_validation->set_rules('datos_juridicos[fecha_constitucion]', 'Fecha de Constitución', 'required|trim|strtoupper');
            $this->form_validation->set_rules('datos_juridicos[id_tipo_sociedad]', 'Tipo Sociedad', 'required|trim|strtoupper');
        }

		if( $this->form_validation->run() == true){
            $data = $this->security->xss_clean($this->input->post());  # XSS filtering

            $token = $this->security->xss_clean($this->input->post('auth', TRUE));   //token de authentication
            $is_valid_token = $this->authorization_token->validateToken($token);

            if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
                $int_declaracion_jurada = $data['declaracion_jurada'];
                $usuario = $this->Usuario_model->getByTokenJWT($is_valid_token['data']);
            
                $declaracion_jurada = $this->DeclaracionJurada_model->getByToken($int_declaracion_jurada['token']);
                if($declaracion_jurada != null){

                    $persona = $data["persona"];
                    $persona["id_usuario"] = $usuario->id;
                    $out_persona = $this->Persona_model->create( $persona );

                    if($out_persona != null){
                        $out_datos_juridicos = $this->saveDatosJuridicos($data, $usuario);
        
                        $out_solicitante = $this->saveSolicitante($data, $out_persona, $out_datos_juridicos, $declaracion_jurada);
        
                        if(null !== $out_solicitante){
                            $out_rep_legal = $this->saveRepresentatenLegal($out_persona, $out_solicitante, $usuario);
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
    * Update a persona asociate with declaracion jurada
    * --------------------------
    * @param: array persona
    * @param: declaracion jurada
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/persona/update
    */
    public function update_post(){
        $this->form_validation->set_rules('persona[id_tipo_documento]', 'Tipo Documento', 'required|trim|strtoupper');
        $this->form_validation->set_rules('persona[expedido_en]', 'Lugar Donde fue expedido el CI', 'required|trim|strtoupper');  //esto controlar, solo para tipo documento CI
        $this->form_validation->set_rules('persona[numero_documento]', 'Número Documento', 'required|trim|strtoupper');
        $this->form_validation->set_rules('persona[nacionalidad]', 'Nacionalidad', 'required|trim|strtoupper');
        $this->form_validation->set_rules('persona[nombre]', 'Nombre', 'required|trim|strtoupper');
        $this->form_validation->set_rules('persona[apellido_paterno]', 'Apellido Paterno', 'required|trim|strtoupper');
        $this->form_validation->set_rules('persona[estado_civil]', 'Estado Civil', 'required|trim|strtoupper');
        $this->form_validation->set_rules('persona[genero]', 'Género', 'required|trim|strtoupper');
        $this->form_validation->set_rules('persona[fecha_nacimiento]', 'Fecha de Nacimiento', 'required|trim|strtoupper');

        if (array_key_exists('datos_juridicos', $this->input->post())) {
            $this->form_validation->set_rules('datos_juridicos[nit]', 'NIT', 'required|trim|strtoupper');
            $this->form_validation->set_rules('datos_juridicos[razon_social]', 'Razón Social', 'required|trim|strtoupper');
            $this->form_validation->set_rules('datos_juridicos[fecha_constitucion]', 'Fecha de Constitución', 'required|trim|strtoupper');
            $this->form_validation->set_rules('datos_juridicos[id_tipo_sociedad]', 'Tipo Sociedad', 'required|trim|strtoupper');
        }

		if( $this->form_validation->run() == true){
            $data = $this->security->xss_clean($this->input->post());  # XSS filtering

            $token = $this->security->xss_clean($this->input->post('auth', TRUE));   //token de authentication
            $is_valid_token = $this->authorization_token->validateToken($token);

            if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
                $in_declaracion_jurada = $data['declaracion_jurada'];
                $in_persona = $data['persona'];
                $usuario = $this->Usuario_model->getByTokenJWT($is_valid_token['data']);
                
                $declaracion_jurada = $this->DeclaracionJurada_model->getByToken($in_declaracion_jurada['token']);
                if($declaracion_jurada != null){
                    $solicitante = $this->Solicitante_model->getById($declaracion_jurada->id_solicitante);
                    
                    if(null !== $solicitante){
                        $representante_legal = $this->RepresentanteLegal_model->getByIdSolicitud($solicitante->id);
                        if(null === $representante_legal){
                            $in_persona = $data["persona"];
                            $in_persona["id_usuario"] = $usuario->id;
                            $persona = $this->Persona_model->create( $in_persona );
                            $representante_legal = $this->saveRepresentatenLegal($persona, $solicitante, $usuario);

                            $out_datos_juridicos = $this->saveDatosJuridicos($data, $usuario);

                            //actualizar solicitante
                            if(!is_null($out_datos_juridicos)){
                                $solicitante->id_datos_juridicos = $out_datos_juridicos->id;
                            }
                            $solicitante = $this->Solicitante_model->update( $solicitante );

                            $this->response([
                                'status' => true,
                                'message' => $this->lang->line('item_was_has_add'),
                                $this->lang->line('persona') => $persona,
                                $this->lang->line('solicitante') => $solicitante,
                                $this->lang->line('datos_juridicos') => $out_datos_juridicos
                            ], REST_Controller::HTTP_OK);
                        }else{
                            $persona = $this->Persona_model->getById($representante_legal->id_persona);

                            if(null !== $persona){
                                $persona->id_tipo_documento  = $in_persona['id_tipo_documento'];
                                $persona->numero_documento = $in_persona['numero_documento'];
                                $persona->expedido_en  = $in_persona['expedido_en'];
                                $persona->nacionalidad  = $in_persona['nacionalidad'];
                                $persona->nombre  = $in_persona['nombre'];
                                $persona->apellido_paterno  = $in_persona['apellido_paterno'];
                                $persona->apellido_materno  = $in_persona['apellido_materno'];
                                if( array_key_exists('apellido_casada', $in_persona) )
                                    $persona->apellido_casada  = $in_persona['apellido_casada'];
                                $persona->estado_civil  = $in_persona['estado_civil'];
                                $persona->genero  = $in_persona['genero'];
                                $persona->fecha_nacimiento  = $in_persona['fecha_nacimiento'];
    
                                $out_persona = $this->Persona_model->update( $persona );
    
                                $out_datos_juridicos = $this->updateDatosJuridicos($data, $solicitante);
    
                                $this->response([
                                    'status' => true,
                                    'message' => $this->lang->line('item_was_has_add'),
                                    $this->lang->line('persona') => $out_persona,
                                    $this->lang->line('solicitante') => $solicitante,
                                    $this->lang->line('datos_juridicos') => $out_datos_juridicos
                                ], REST_Controller::HTTP_OK);
                            }else{
                                $this->response([
                                    'status' => false,
                                    'message' => $this->lang->line('no_item_was_has_add')
                                ], REST_Controller::HTTP_CREATED);
                            }
                        }
                    }else{
                        $this->response([
                            'status' => false,
                            'message' => $this->lang->line('no_item_was_has_add')
                        ], REST_Controller::HTTP_CREATED);
                    }
                    var_dump($declaracion_jurada);
                    exit;
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

    private function saveSolicitante($data, $persona, $datos_juridicos, $declaracion_jurada){

        if ( null !== $declaracion_jurada) {

            $db_solicitante = $this->Solicitante_model->getById($declaracion_jurada->id_solicitante);
 
            if( null !== $datos_juridicos  )
                $db_solicitante->id_datos_juridicos = $datos_juridicos->id;
            
            return $this->Solicitante_model->update( $db_solicitante );
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

    private function updateDatosJuridicos($data, $solicitante){
        if(array_key_exists('datos_juridicos', $data)){
            $in_datos_juridicos = $data['datos_juridicos'];

            $datos_juridicos = $this->DatosJuridicos_model->getById($solicitante->id_datos_juridicos);
            $datos_juridicos->razon_social = $in_datos_juridicos['razon_social'];
            $datos_juridicos->fecha_constitucion = $in_datos_juridicos['fecha_constitucion'];
            $datos_juridicos->id_tipo_sociedad = $in_datos_juridicos['id_tipo_sociedad'];

            return $this->DatosJuridicos_model->update($datos_juridicos);
        }
        return null;
    }

    private function saveDatosJuridicos($data, $usuario){

        if (array_key_exists('datos_juridicos', $data)) {
            $datos_juridicos = $data["datos_juridicos"];
            $datos_juridicos["id_usuario"] = $usuario->id;
            return $this->DatosJuridicos_model->create( $datos_juridicos );
        }
        return null;
    }

    private function saveRepresentatenLegal($persona, $solicitante, $usuario){

        $representante_legal['id_persona'] = $persona->id;
        $representante_legal['id_solicitante'] = $solicitante->id;
        $representante_legal["id_usuario"] = $usuario->id;
        return $this->RepresentanteLegal_model->create( $representante_legal );
    }
}
