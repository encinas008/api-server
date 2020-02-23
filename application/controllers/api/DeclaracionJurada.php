<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller for user, this controller extends for MY_Controller
 *
 * @package         restServerAtm
 * @subpackage      DeclaracionJurada
 * @category        Controller
 * @author          Ronald Acha Ramos
 * @license         MIT
 */
class DeclaracionJurada extends MY_Controller {

    public function __construct($config = 'rest')
	{
        parent::__construct($config);
        
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, OPTIONS, POST");
        header("Access-Control-Allow-Headers: *");

        $this->load->model('DeclaracionJurada_model');
        $this->load->model('Persona_model');
        $this->load->model('Solicitante_model');
        $this->load->model('DatosJuridicos_model');
        $this->load->model('Estado_model');
        $this->load->model('Domicilio_model');
        $this->load->model('ActividadEconomica_model');
        $this->load->model('DomicilioActividadEconomica_model');
        $this->load->model('RepresentanteLegal_model');
        $this->load->model('TipoActividadEconomica_model');
        $this->load->model('Nacionalidad_model');
        $this->load->model('EstadoCivil_model');
        $this->load->model('Usuario_model');
        $this->load->model('Genero_model');
        $this->load->model('TipoDocumento_model');
        $this->load->model('Cobros_model');
        
        $this->load->library('Authorization_Token');

        $this->methods['getByPage_get']['limit'] = 4; // 500 requests per hour per user/key

        $this->form_validation->set_error_delimiters('', '');

        $this->form_validation->set_message('required', 'El campo %s es obligatorio');
        $this->form_validation->set_message('integer', 'El campo %s deve poseer solo numeros enteros');
        $this->form_validation->set_message('is_unique', 'El campo %s ya esta registrado');
        $this->form_validation->set_message('max_length', 'El Campo %s debe tener un Maximo de %d Caracteres');
    }

    public function getAll_get()
    {
        header("Access-Control-Allow-Origin: *");
        $list = $this->DeclaracionJurada_model->getAll();

        $data["result"] = $list;
        $this->response($data, REST_Controller::HTTP_OK);
    }

    public function getByPage_get($page, $per_page, $tipo_search)
    {
        $page = $this->security->xss_clean($page);  # XSS filtering
        $per_page = $this->security->xss_clean($per_page);  # XSS filtering

        if(is_numeric($page) && is_numeric($per_page)){

            $token = $this->security->xss_clean($this->input->get('auth', TRUE));   //token de authentication
            $is_valid_token = $this->authorization_token->validateToken($token);

            if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){

                $user = $is_valid_token['data'];

                $total = $this->DeclaracionJurada_model->getCount($user->token, $tipo_search);
                $offset = ($page - 1) * $per_page;
                $list = $this->DeclaracionJurada_model->getByPage($per_page, $offset, $user->token, $tipo_search );

                if(null !== $list ){
                    $this->response([
                        'status' => true,
                        'message' => $this->lang->line('item_has_found'),
                        "page" => (int)$page,
                        "per_page"=> (int)$per_page,
                        "total"=> (int)$total,
                        "total_pages"=> ceil($total/$per_page),
                        'data' => $list,
                        'en_proceso' => $this->DeclaracionJurada_model->getCount($user->token, 1),
                        'completados' => $this->DeclaracionJurada_model->getCount($user->token, 2),
                        'aprobados' => $this->DeclaracionJurada_model->getCount($user->token, 3)
                    ], REST_Controller::HTTP_OK);
                }else{
                    $this->response([
                        'status' => false,
                        'message' => $this->lang->line('not_item_were_found')
                    ], REST_Controller::HTTP_NO_CONTENT); // NOT_FOUND (404) being the HTTP response code
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
                'message' => $this->lang->line('parameter_is_not_valid')
            ], REST_Controller::HTTP_NO_CONTENT); // NOT_FOUND (400) being the HTTP response code
        }
    }
    

    /**
     * this method permit to get and validate fur code
     */
    public function getByToken_get($token){

        $token = $this->security->xss_clean($token);  # XSS filtering

        $token_verified = self::getTokenVerified($token);

        $token = $this->security->xss_clean($this->input->get('auth', TRUE));   //token de authentication
        $is_valid_token = $this->authorization_token->validateToken($token);

        if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
            
            $declaracion_jurada = $this->DeclaracionJurada_model->getByToken($token_verified);

            if($declaracion_jurada !== null){
                $estado_dj = $this->Estado_model->getById($declaracion_jurada->id_estado);
                if($estado_dj->code !== ESTADO_CANCELADO){
                    $data[$this->lang->line('declaracion_jurada')] = $declaracion_jurada;
                
                    $solicitante = $this->Solicitante_model->getById( $declaracion_jurada->id_solicitante );
                    $actividad_economica = $this->ActividadEconomica_model->getById( $declaracion_jurada->id_actividad_economica );

                    $data[$this->lang->line('actividad_economica')] = $actividad_economica ;

                    $tipo_actividad_economica = $this->TipoActividadEconomica_model->getById( $actividad_economica->id_tipo_actividad );
                    $data[$this->lang->line('tipo_actividad_economica')] = $tipo_actividad_economica;

                    $persona = null;
                    if(null !== $solicitante){
                        $representante_legal = $this->RepresentanteLegal_model->getByIdSolicitud($solicitante->id);

                        if(null !== $representante_legal){
                            $persona = $this->Persona_model->getById( $representante_legal->id_persona ); 

                            if(null != $persona){
                                $domicilio = $this->Domicilio_model->getByIdPersona( $persona->id );
                                $data[$this->lang->line('domicilio')] = $domicilio;
                            }

                            $data[$this->lang->line('persona')] = $persona;
                        }

                        $datos_juridicos = $this->DatosJuridicos_model->getById( $solicitante->id_datos_juridicos );

                        $data[$this->lang->line('solicitante')] = $solicitante;
                        $data[$this->lang->line('datos_juridicos')] = $datos_juridicos;
                    }
                    $domicilio_actividad_economica = $this->DomicilioActividadEconomica_model->getByIdActividadEconomica( $actividad_economica->id );

                    $data[$this->lang->line('domicilio_actividad_economica')] = $domicilio_actividad_economica;
                    $data[$this->lang->line('declaracion_jurada')] = $declaracion_jurada;
                    $data[$this->lang->line('estado')] = $this->Estado_model->getById($declaracion_jurada->id_estado);

                    if(null !== $persona){
                        $data[$this->lang->line('nacionalidad')] = $this->Nacionalidad_model->getById($persona->nacionalidad);
                        $data[$this->lang->line('estado_civil')] = $this->EstadoCivil_model->getById($persona->estado_civil);
                        $data[$this->lang->line('genero')] = $this->Genero_model->getById($persona->genero);
                        $data[$this->lang->line('tipo_documento')] = $this->TipoDocumento_model->getById($persona->id_tipo_documento);
                    }

                    $this->response([
                        'status' => true,
                        'message' =>  $this->lang->line('item_has_found'),
                        'data' => $data
                    ], REST_Controller::HTTP_OK);
                }else {
                    # code...
                    $this->response([
                        'status' => FALSE,
                        'message' => $this->lang->line('not_item_were_found')
                    ], REST_Controller::HTTP_CREATED); // NOT_FOUND (404) being the HTTP response code
                }
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

        $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }


    /**
    * Add new Declaracion Jurada
    * --------------------------
    * @param: array declaracion jurada
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/declaracion-jurada/create
    */
    //pendiente la verificaion del fur nuevamente
    public function create_post(){

        $this->form_validation->set_rules('declaracion_jurada[fur]', 'Número Fur', 'required|trim|strtoupper|callback_fur_unique');  //callback_username_unique
        $this->form_validation->set_rules('actividad_economica[id_tipo_actividad]', 'Actividad Economica', 'required|strtoupper');
        $this->form_validation->set_rules('solicitante[contribuyente]', 'Contribuyente', 'required|strtoupper');

		if( $this->form_validation->run() == true){
            $data = $this->security->xss_clean($this->input->post());  # XSS filtering

            $token = $this->security->xss_clean($this->input->post('auth', TRUE));   //token de authentication
            $is_valid_token = $this->authorization_token->validateToken($token);

            if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){

                $estado = $this->Estado_model->getByCode('EN_PROCESO');
                $usuario = $this->Usuario_model->getByTokenJWT($is_valid_token['data']);

                $solicitante = $data["solicitante"];
                $solicitante['id_usuario'] = $usuario->id;
                $out_solicitante = $this->Solicitante_model->create( $solicitante );
                if($out_solicitante != null){
                    $estado_act_eco = $this->Estado_model->getByCode('ACTIVO');
                    $actividad_economica = $data["actividad_economica"];
                    $actividad_economica['id_usuario'] = $usuario->id;
                    $actividad_economica['id_estado'] = $estado_act_eco->id;
                    $out_actividad_economica = $this->ActividadEconomica_model->create( $actividad_economica );

                    if(null !== $out_actividad_economica){
                        $declaracion_jurada = $data["declaracion_jurada"];
                        $declaracion_jurada['id_estado'] = $estado->id;
                        $declaracion_jurada['id_solicitante'] = $out_solicitante->id;
                        $declaracion_jurada['id_actividad_economica'] = $out_actividad_economica->id;
                        $declaracion_jurada['id_usuario'] = $usuario->id;
                        $out_dj = $this->DeclaracionJurada_model->create( $declaracion_jurada );

                        if($out_dj != null){
                            $this->response([
                                'status' => true,
                                'message' => $this->lang->line('item_was_has_add'),
                                $this->lang->line('declaracion_jurada') => $out_dj,
                                $this->lang->line('solicitante') => $out_solicitante,
                                $this->lang->line('actividad_economica') => $out_actividad_economica
                            ], REST_Controller::HTTP_OK);
                        }else{
                            $this->response([
                                'status' => false,
                                'message' => $this->lang->line('not_item_was_has_add')
                            ], REST_Controller::HTTP_CREATED);
                        }
                    }else{
                        $this->response([
                            'status' => false,
                            'message' => $this->lang->line('not_item_was_has_add')
                        ], REST_Controller::HTTP_CREATED);
                    }
                }else{
                    $this->response([
                        'status' => false,
                        'message' => $this->lang->line('not_item_was_has_add')
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
     * this method permit to get update a item by $id
     */
    public function update_post(){

        $this->form_validation->set_rules('declaracion_jurada[fur]', 'Número Fur', 'required|trim|strtoupper');
        $this->form_validation->set_rules('actividad_economica[id_tipo_actividad]', 'Actividad Economica', 'required|strtoupper');
        $this->form_validation->set_rules('solicitante[contribuyente]', 'Contribuyente', 'required|strtoupper');

		if( $this->form_validation->run() == true){
            
            $data = $this->security->xss_clean($this->input->post());  # XSS filtering

            $in_declaracion_jurada = $data['declaracion_jurada'];
            $in_solicitante = $data['solicitante'];

            $declaracion_jurada = $this->DeclaracionJurada_model->getByToken( $in_declaracion_jurada['token'] );
            // var_dump($declaracion_jurada);
            // exit;
            if(null !== $declaracion_jurada ){
                $actividad_economica = $this->ActividadEconomica_model->getById($declaracion_jurada->id_actividad_economica  );
                $actividad_economica->id_tipo_actividad = $this->input->post("actividad_economica[id_tipo_actividad]");  //actividad_economica[id_tipo_actividad]
                $out_act_eco = $this->ActividadEconomica_model->update( $actividad_economica );
                
                $solicitud = $this->Solicitante_model->getById( $declaracion_jurada->id_solicitante ); 
                $solicitud->contribuyente = $in_solicitante['contribuyente'];
                $out_sol = $this->Solicitante_model->update( $solicitud );

                if($out_act_eco !== null && $out_sol !== null){
                    $this->response([
                        'status' => true,
                        'message' => $this->lang->line('item_was_has_add'),
                        $this->lang->line('declaracion_jurada') => $declaracion_jurada,
                        $this->lang->line('solicitante') => $out_sol,
                        $this->lang->line('actividad_economica') => $out_act_eco
                    ], REST_Controller::HTTP_OK);
                }else{
                    $this->response([
                        'status' => false,
                        'message' => $this->lang->line('no_item_was_has_add')
                    ], REST_Controller::HTTP_ACCEPTED); 
                }
            }else{
                $this->response([
                    'status' => false,
                    'message' => $this->lang->line('no_item_was_has_add')
                ], REST_Controller::HTTP_ACCEPTED); 
            }
		}else{
            $this->response([
                'status' => false,
                'message' => validation_errors()
            ], REST_Controller::HTTP_ACCEPTED); 
		}
        $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_NOT_FOUND); // CREATED (201) being the HTTP response code
    }

    /**
    * Change de state for declaracion jurada
    * --------------------------
    * @param: array declaracion jurada
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/declaraciones-juradas/complete
    */
    public function complete_post(){

        $this->form_validation->set_rules('declaracion_jurada[token]', 'Token Declaracion Jurada', 'required');

        if( $this->form_validation->run() == true){

            $data = $this->security->xss_clean($this->input->post());  # XSS filtering

            $in_declaracion_jurada = $data["declaracion_jurada"];

            $token = $this->security->xss_clean($this->input->post('auth', TRUE));   //token de authentication
            $is_valid_token = $this->authorization_token->validateToken($token);

            if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
                $in_declaracion_jurada = $data['declaracion_jurada'];
                $in_solicitante = $data['solicitante'];

                $declaracion_jurada = $this->DeclaracionJurada_model->getByToken( $in_declaracion_jurada['token'] );
                
                if(null !== $declaracion_jurada ){
                    $actividad_economica = $this->ActividadEconomica_model->getById($declaracion_jurada->id_actividad_economica  );
                    $actividad_economica->id_tipo_actividad = $this->input->post("actividad_economica[id_tipo_actividad]");  //actividad_economica[id_tipo_actividad]
                    $out_act_eco = $this->ActividadEconomica_model->update( $actividad_economica );
                    
                    $solicitud = $this->Solicitante_model->getById( $declaracion_jurada->id_solicitante ); 
                    $solicitud->contribuyente = $in_solicitante['contribuyente'];
                    $out_sol = $this->Solicitante_model->update( $solicitud );

                    if($out_act_eco !== null && $out_sol !== null){
                        $this->response([
                            'status' => true,
                            'message' => $this->lang->line('item_was_has_add'),
                            $this->lang->line('declaracion_jurada') => $declaracion_jurada,
                            $this->lang->line('solicitante') => $out_sol,
                            $this->lang->line('actividad_economica') => $out_act_eco
                        ], REST_Controller::HTTP_OK);
                    }else{
                        $this->response([
                            'status' => false,
                            'message' => $this->lang->line('no_item_was_has_add')
                        ], REST_Controller::HTTP_ACCEPTED); 
                    }
                }else{
                    $this->response([
                        'status' => false,
                        'message' => $this->lang->line('no_item_was_has_add')
                    ], REST_Controller::HTTP_ACCEPTED); 
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
        $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_NOT_FOUND); // CREATED (201) being the HTTP response code
    }

    /**
    * Change de state for declaracion jurada
    * --------------------------
    * @param: array declaracion jurada
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/declaraciones-juradas/complete
    */
    public function complete_post(){

        $this->form_validation->set_rules('declaracion_jurada[token]', 'Token Declaracion Jurada', 'required|trim');

        if( $this->form_validation->run() == true){

            $data = $this->security->xss_clean($this->input->post());  # XSS filtering

            $in_declaracion_jurada = $data["declaracion_jurada"];

            $token = $this->security->xss_clean($data["auth"]);   //token de authentication
            $is_valid_token = $this->authorization_token->validateToken($token);

            if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){

                $declaracion_jurada = $this->DeclaracionJurada_model->getByToken($in_declaracion_jurada['token']);
                $estado_aprobado = $this->Estado_model->getByCode(ESTADO_APROBADO);

                if($declaracion_jurada->id_estado !== $estado_aprobado->id ){
                    $estado = $this->Estado_model->getByCode('COMPLETADO');

                    //si el estado no esta completado, se procede a completarlo
                    if($declaracion_jurada->id_estado !== $estado->id ){  
                        if($declaracion_jurada !== null){
                            $solicitante = $this->Solicitante_model->getById( $declaracion_jurada->id_solicitante );
        
                            $is_complete_data =  $this->DeclaracionJurada_model->isComleteData($declaracion_jurada->id, $solicitante->contribuyente);
                            if($is_complete_data){
                                $declaracion_jurada->id_estado = $estado->id;
                                $out_dj = $this->DeclaracionJurada_model->update( $declaracion_jurada );
        
                                if($out_dj != null){
                                    $this->response([
                                        'status' => true,
                                        'message' => $this->lang->line('item_was_has_add'),
                                        $this->lang->line('declaracion_jurada') => $out_dj,
                                        $this->lang->line('estado') => $this->Estado_model->getById($out_dj->id_estado),
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
                                    'message' => $this->lang->line('data_not_is_complete')
                                ], REST_Controller::HTTP_CREATED);
                            }
                        }else{
                            $this->response([
                                'status' => FALSE,
                                'message' => $this->lang->line('not_item_were_found')
                            ], REST_Controller::HTTP_NO_CONTENT); // NOT_FOUND (404) being the HTTP response code
                        }
                    }else{
                        $this->response([
                            'status' => true,
                            'message' => $this->lang->line('verificar_actividad_economica'),
                            $this->lang->line('declaracion_jurada') => $declaracion_jurada,
                            $this->lang->line('estado') => $this->Estado_model->getById( $declaracion_jurada->id_estado)
                        ], REST_Controller::HTTP_OK);
                    }
                }else{
                    $this->response([
                        'status' => true,
                        'message' => $this->lang->line('verificar_actividad_economica'),
                        $this->lang->line('declaracion_jurada') => $declaracion_jurada,
                        $this->lang->line('estado') => $this->Estado_model->getById( $declaracion_jurada->id_estado)
                    ], REST_Controller::HTTP_OK);
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
    * Permit check the declaracion jurada
    * --------------------------
    * @param: array declaracion jurada
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/declaracione-jurada/check/(:any)
    */
    public function check_get($token){

        $this->security->xss_clean($this->input->get());  # XSS filtering parameter get

        $token = $this->security->xss_clean($token);  # XSS filtering parameter $token
        $token_verified = self::getTokenVerified($token);

        $token = $this->security->xss_clean($this->input->get('auth', TRUE));   //token de authentication
        $is_valid_token = $this->authorization_token->validateToken($token);

        if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
            $declaracion_jurada = $this->DeclaracionJurada_model->getByToken($token_verified);
            $usuario = $this->Usuario_model->getByTokenJWT($is_valid_token['data']);
            if($declaracion_jurada !== null){
                $estado_dj = $this->Estado_model->getById($declaracion_jurada->id_estado);
                if($estado_dj->code === ESTADO_COMPLETADO){

                    $jsonFur = $this->Cobros_model->confirmarFur($declaracion_jurada->fur, $usuario->username);

                    if($jsonFur !== null && $jsonFur->status === true){
                        $estado = $this->Estado_model->getByCode('APROBADO');

                        $declaracion_jurada->id_estado = $estado->id;
                        $declaracion_jurada->fecha_aprobacion = date(DATETIME_FORMAT);
                        $out_dj = $this->DeclaracionJurada_model->update( $declaracion_jurada );

                        if($out_dj != null ){

                            //unset($usuario_output->password);
                            $this->response([
                                'status' => true,
                                'message' => $this->lang->line('data_confirm_licencia_actividad_economica'), //licencia de actividad aprobada y fur confirma
                                $this->lang->line('estado') => $estado,
                                $this->lang->line('declaracion_jurada') => $out_dj
                            ], REST_Controller::HTTP_OK);
                        }else{
                            $this->response([
                                'status' => false,
                                'message' => $this->lang->line('data_not_confirm_licencia_actividad_economica')  //licencia de actividad no actualizada
                            ], REST_Controller::HTTP_OK);
                        }
                    }else{
                        $this->response([
                            'status' => false,
                            'message' => $jsonFur->data  //fur no confirmado
                        ], REST_Controller::HTTP_OK);
                    }
                }else{  //la declaracion jurada tiene otro estado
                    if($estado_dj->code === ESTADO_APROBADO ){
                        $this->response([
                            'status' => false,
                            'message' => $this->lang->line('licencia_aprobado')
                        ], REST_Controller::HTTP_OK);
                    }

                    if($estado_dj->code === ESTADO_EN_PROCESO ){
                        $this->response([
                            'status' => false,
                            'message' => $this->lang->line('licencia_in_process')
                        ], REST_Controller::HTTP_OK);
                    }
                }
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

        $this->set_response($message, REST_Controller::HTTP_CREATED);
    }

    /**
    * Permit uncheck the declaracion jurada, cambia el estado a completado
    * --------------------------
    * @param: string token licencia actividades economicas
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/declaracione-jurada/uncheck/(:any)
    */
    public function uncheck_get($token){

        $this->security->xss_clean($this->input->get());  # XSS filtering parameter get

        $token = $this->security->xss_clean($token);  # XSS filtering parameter $token

        $token_verified = self::getTokenVerified($token);

        $token = $this->security->xss_clean($this->input->get('auth', TRUE));   //token de authentication
        $is_valid_token = $this->authorization_token->validateToken($token);

        if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
            $declaracion_jurada = $this->DeclaracionJurada_model->getByToken($token_verified);
            if($declaracion_jurada !== null){
                $estado_dj = $this->Estado_model->getById($declaracion_jurada->id_estado);
                if($estado_dj->code === 'APROBADO' ){
                    $estado = $this->Estado_model->getByCode('COMPLETADO');

                    $declaracion_jurada->id_estado = $estado->id;
                    $out_dj = $this->DeclaracionJurada_model->update( $declaracion_jurada );

                    if($out_dj != null){
                        $this->response([
                            'status' => true,
                            'message' => $this->lang->line('item_was_has_add'),
                            $this->lang->line('declaracion_jurada') => $out_dj
                        ], REST_Controller::HTTP_OK);
                    }else{
                        $this->response([
                            'status' => false,
                            'message' => $this->lang->line('no_item_was_has_add')
                        ], REST_Controller::HTTP_OK);
                    }
                }else{  //la declaracion jurada no se completo
                    $this->response([
                        'status' => false,
                        'message' => $this->lang->line('licencia_in_process')
                    ], REST_Controller::HTTP_OK);
                }
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

        $this->set_response($message, REST_Controller::HTTP_CREATED);
    }

    /**
    * Permit uncheck the declaracion jurada, cambia el estado a completado
    * --------------------------
    * @param: string token licencia actividades economicas
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/licencia-actividad-economica/search
    */
    public function search_post(){

        $this->security->xss_clean($this->input->post());

        $token = $this->input->post('auth', TRUE);   //token de authentication
        $is_valid_token = $this->authorization_token->validateToken($token);

        if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){

            $usuario = $this->Usuario_model->getByTokenJWT($is_valid_token['data']);
            //print_r($usuario);
            $text = $this->input->post('search', TRUE);
            $type = $this->input->post('type', TRUE);
            $list = $this->DeclaracionJurada_model->search($text, $type,  $usuario->token );

            $total = (int)sizeof($list);

            if(null !== $list && $total > 0){
                $this->response([
                    'status' => true,
                    'message' => $this->lang->line('item_has_found'),
                    "page" => 1,
                    "per_page"=> 10,
                    "total"=> $total,
                    "total_pages"=> ceil($total/10),
                    'data' => $list
                ], REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    'status' => true,
                    'message' => $this->lang->line('not_item_were_found'),
                    "page" => 1,
                    "per_page"=> 10,
                    "total"=> $total,
                    "total_pages"=> ceil($total/10),
                    'data' => $list
                ], REST_Controller::HTTP_OK);
            }
        }
    }

  

    /**
    * Delete Licencia de funcionamiento
    * --------------------------
    * @param: string token licencia actividades economicas
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/licencia-actividad-economica/delete
    */
    public function delete_get($token){

        $this->security->xss_clean($this->input->get());

        $token = $this->security->xss_clean($token);  # XSS filtering

        $token_verified = self::getTokenVerified($token);

        $token = $this->input->get('auth', TRUE);   //token de authentication
        $is_valid_token = $this->authorization_token->validateToken($token);

        if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
            
            $declaracion_jurada = $this->DeclaracionJurada_model->getByToken($token_verified );
            $estado_aprobado = $this->Estado_model->getByCode(ESTADO_APROBADO);
 
            if($declaracion_jurada->id_estado !== $estado_aprobado->id){
                if(null !== $declaracion_jurada){
                    $estado = $this->Estado_model->getByCode(ESTADO_CANCELADO);
                    $declaracion_jurada->id_estado = $estado->id;
                    $delete_dj = $this->DeclaracionJurada_model->update($declaracion_jurada );
    
                    if(null !== $delete_dj){
    
                        $this->response([
                            'status' => true,
                            'message' =>  $this->lang->line('data_updated'),
                            $this->lang->line('declaracion_jurada') => $delete_dj
                        ], REST_Controller::HTTP_OK);
                    }else{
                        $this->response([
                            'status' => false,
                            'message' => $this->lang->line('data_not_updated')
                        ], REST_Controller::HTTP_ACCEPTED); 
                    }
                }else{
                    $this->response([
                        'status' => FALSE,
                        'message' => $this->lang->line('not_item_were_found')
                    ], REST_Controller::HTTP_NO_CONTENT); // NOT_FOUND (404) being the HTTP response code
                }
            }else{
                //esta aprobado
                $this->response([
                    'status' => false,
                    'message' =>  $this->lang->line('imposible_delete_licence_confimated'),
                ], REST_Controller::HTTP_OK);
            }
        }else{
            $this->response([
                'status' => false,
                'message' => $this->lang->line('token_is_invalid'),
            ], REST_Controller::HTTP_NETWORK_AUTHENTICATION_REQUIRED); // NOT_FOUND (400) being the HTTP response code
        }
    }

    /*****************************************************************************************/
    #metodo privados del controlador
    /**
     * this method permit verified and validate $id
     */
    private function getIdVerified($id){
        if(!is_null($id) && !empty($id) && is_numeric($id) ){
            return $id;
        }else{
            $this->response([
                'status' => false,
                'message' => $this->lang->line('identifier_is_required')
            ], REST_Controller::HTTP_NO_CONTENT); // NOT_FOUND (404) being the HTTP response code
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

    private function savePersona($data){

        if (array_key_exists('persona', $data)) {
            return $this->Persona_model->create( $data["persona"] );
        }
        return null;
    }

    private function saveSolicitante($data, $insert_persona, $datos_juridicos){

        if (array_key_exists('solicitante', $data)) {

            $solicitante = $data['solicitante'];
            if(null != $insert_persona )
                $solicitante['id_persona'] =  $insert_persona->id;

            if(null != $datos_juridicos )
                $solicitante['id_datos_juridicos'] =  $datos_juridicos->id;
            
            return $this->Solicitante_model->create( $solicitante );
        }

        return null;
    }

    private function saveDatosJuridicos($data){

        if (array_key_exists('datos_juridicos', $data)) {
            return $this->DatosJuridicos_model->create( $data["datos_juridicos"] );
        }
        return null;
    }

    private function saveDomicilioPersona($data, $persona){

        if (array_key_exists('domicilio', $data)) {
            $domicilio = $data['domicilio'];
            if(null != $persona ){
                $domicilio['id_persona'] = (string)$persona->id;
                return $this->Domicilio_model->create( $domicilio);
            }
        }
        return null;
    }

    private function saveDomicilioActividadEconomicaPersona($data, $declaracion_jurada){

        if (array_key_exists('domicilio_actividad_economica', $data)) {
            $domicilio = $data['domicilio_actividad_economica'];
            if(null != $declaracion_jurada ){
                $domicilio['id_declaracion_jurada'] = $declaracion_jurada->id;
                return $this->DomicilioActividadEconomica_model->create( $domicilio );
            }
        }
        return null;
    }

    /**
    * Return state of diferent part of declaracion jurada
    * Datos de Contribuyente
    * Datos del domicilio del contribuyente
    * --------------------------
    * @param: array declaracion jurada
    * --------------------------
    * @method: GET
    * @author: RAR
    * @link: api/licencia-actividad-economica/get-estado-datos
    */
    public function getEstateData_get($token){

        $token = $this->security->xss_clean($token);  # XSS filtering
        $this->security->xss_clean($this->input->get());  # XSS filtering

        $token_verified = self::getTokenVerified($token);

        $token = $this->input->get('auth', TRUE);   //token de authentication
        $is_valid_token = $this->authorization_token->validateToken($token);

        if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
            $declaracion_jurada = $this->DeclaracionJurada_model->getByToken($token_verified);

            if( !is_null($declaracion_jurada) ){
                $solicitante = $this->Solicitante_model->getById( $declaracion_jurada->id_solicitante );

                if( !is_null($solicitante) ){
                    $is_complete_data_contribuyente =  $this->DeclaracionJurada_model->isDataContribuyenteComplete($declaracion_jurada->id, $solicitante->contribuyente);
                    $is_complete_data_domicilio =  $this->DeclaracionJurada_model->isDataDomicilioComplete($declaracion_jurada->id);
                    $is_complete_data_domicilio_actividad = $this->DeclaracionJurada_model->isDataDomicilioActividadEconomicaComplete($declaracion_jurada->id);
                    
                    $this->response([
                        'status' => true,
                        'message' => $this->lang->line('item_has_found'),
                        $this->lang->line('is_complete_data_contribuyente') => $is_complete_data_contribuyente,
                        $this->lang->line('is_complete_data_domicilio') => $is_complete_data_domicilio,
                        $this->lang->line('is_complete_data_domicilio_actividad_economica') => $is_complete_data_domicilio_actividad,
                    ], REST_Controller::HTTP_OK);
                }else{
                    $this->response([
                        'status' => FALSE,
                        'message' => $this->lang->line('not_item_were_found')
                    ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
                }
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => $this->lang->line('not_item_were_found')
                ], REST_Controller::HTTP_CREATED); // NOT_FOUND (404) being the HTTP response code
            }
        }else{
            $this->response([
                'status' => false,
                'message' => validation_errors()
            ], REST_Controller::HTTP_ACCEPTED); 
        }
        $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_RESET_CONTENT);
    }

    /*
    * verificación del fur
    */
    public function fur_unique($fur)
    {
        $dj_output = $this->DeclaracionJurada_model->getByFur($fur);

        if (null !== $dj_output)
        {
            //$this->form_validation->set_message('fur_unique', 'El {field} ya esta registrado, por favor ingrese otro FUR');
            $this->form_validation->set_message('fur_unique',  str_replace("%fur", $dj_output->fur, str_replace("%num_orden", $dj_output->numero, $this->lang->line('num_orden_whit_fur'))));
            return FALSE;
        }
        else
            return TRUE;
    }
    /*
    private function aprobarFur($fur){
        $curl = curl_init();
        $url = "http://192.168.104.117/cb-dev/web/index.php?r=cobros-varios/confirmar-fur&fur=".$fur;

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        
        $result = curl_exec($curl);
        curl_close($curl);

        $jsonFur = json_decode($result);
    }*/
}
