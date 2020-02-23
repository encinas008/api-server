<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller for user, this controller extends for MY_Controller
 *
 * @package         restServerAtm
 * @subpackage      restServerAtm
 * @category        Controller
 * @author          Ronald Acha Ramos
 * @license         MIT
 */
class Usuario extends MY_Controller {

    function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
        header("Access-Control-Allow-Headers: *");

        parent::__construct();
        $this->load->model('Usuario_model');
        $this->load->model('Estado_model');
        $this->load->model('Rol_model');
        $this->load->model('RolUsuario_model');
        $this->load->model('ConfirmarUsuario_model');
        $this->load->model('Datos_model');
        $this->load->model('Country_model');

        $this->load->library('email');
        $this->load->helper('file');
        $this->load->library('Util');

        $this->load->library('Authorization_Token'); 

        $this->form_validation->set_error_delimiters('', '');
/*
        $this->form_validation->set_message('required', 'El campo %s es obligatorio');
        $this->form_validation->set_message('integer', 'El campo %s debe poseer solo números enteros');
        $this->form_validation->set_message('is_unique', 'El campo %s ya esta registrado');
        $this->form_validation->set_message('max_length', 'El Campo %s debe tener un Máximo de %d Caracteres');
        */
    }

    public function users_get()
    {
        //header("Access-Control-Allow-Origin: *");
        $result = $this->Usuario_model->getAll();
        $data["result"] = $result;
        $this->response($data, REST_Controller::HTTP_OK);
    }

    /**
    * User by username
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/usuario/get-by-username
    */
    public function getByUsername_post(){
        $this->security->xss_clean($this->input->post()); # XSS filtering

        $this->form_validation->set_rules('usuario[username]', 'Cuenta de usuario', 'required|trim');

        if($this->form_validation->run() == FALSE){
            $this->response([
                'status' => false,
                'message' => $this->form_validation->error_array()
            ], REST_Controller::HTTP_NOT_FOUND); //400
        }else{
            $data = $this->input->post(); 
            $usuario = $data["usuario"];
            $usuario_db = $this->Usuario_model->getByUsername( $usuario['username'] );

            if($usuario_db != null ){
                unset($usuario_db->password);
                unset($usuario_db->id_rol);
                unset($usuario_db->id_estado);

                $datos_db = $this->Datos_model->getByIdUser( $usuario_db->id );
                if(!is_null($datos_db)){
                    unset($datos_db->image);

                    $image_user_thumb = file_get_contents( FCPATH.'public/uploads/usuario/'. $datos_db->thumbail);
                    $image_user_thumb = base64_encode($image_user_thumb);
                    $datos_db->thumbail = 'data:application/png;base64,'.$image_user_thumb;

                    $this->response([
                        'status' => true,
                        'message' =>  $this->lang->line('account_found'),  
                        'usuario' => $usuario_db,
                        'account' => $datos_db
                    ], REST_Controller::HTTP_OK); //200
                }else{
                    $this->response([
                        'status' => true,
                        'message' =>  $this->lang->line('account_found'),  
                        'usuario' => $usuario_db
                    ], REST_Controller::HTTP_OK); //200
                }
            }else{
                $this->response([
                    'status' => false,
                    'message' => $this->lang->line('account_not_found')
                ], REST_Controller::HTTP_CREATED); //400
            }
        }
    }

    /**
    * User Register
    * --------------------------
    * @param: username
    * @param: password
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/usuario/create
    */
    public function create_post(){

        $this->form_validation->set_rules($this->Usuario_model->rules());
        
        if($this->form_validation->run() === FALSE){;
            $this->response([
                'status' => false,
                'message' => $this->form_validation->error_array()
            ], REST_Controller::HTTP_ACCEPTED); //400
        }else{
            $data = $this->security->xss_clean($this->input->post());  # XSS filtering
            $usuario = $data["usuario"];
            $estado = $this->Estado_model->getByCode('PENDIENTE_APROBACION');

            $usuario['id_estado'] = $estado->id;
            $insert_usuario = $this->Usuario_model->create( $usuario );

            if($insert_usuario != null ){

                if(array_key_exists('rol', $data))
                    $rol = $data['rol'];
                else
                    $rol = $this->Rol_model->getRolContribuyente();

                #asociamos el rol al usuario
                $rol_usuario['id_rol'] = $rol->id;
                $rol_usuario['id_usuario'] = $insert_usuario->id;
                $rol_usuario_out = $this->RolUsuario_model->create($rol_usuario);

                #generamos el token de confirmacion
                $confirmar_usuario['id_usuario'] = $insert_usuario->id;
                $confirmar_usuario = $this->ConfirmarUsuario_model->create($confirmar_usuario);

                unset($insert_usuario->password);   #quitamos el password

                $message = read_file(APPPATH."controllers/template_email/register_user.html");  //sprintf($formato, $num, $ubicación); 

                $replace_email = array('$usuario', '$userEmail', '$urlConfirmEmail', '$page_cliente');
                $new_item = array($insert_usuario->username, $insert_usuario->username, 
                URL_BASE_CLIENTE.'/usuario-confirmar-cuenta?token='.$insert_usuario->token.'&tokenc='.$confirmar_usuario->token, URL_BASE_CLIENTE);

                $message = str_replace($replace_email, $new_item, $message);

                $this->email->set_newline("\r\n");
                $this->email->from('notificaciones@cochabamba.bo', $this->lang->line('administracion_tributaria_municipal'));  
                $this->email->to($insert_usuario->username);
                $this->email->cc('notificaciones@cochabamba.bo');
                $this->email->subject($this->lang->line('account_activation_user'));  
                $this->email->message($message);

                if($this->email->send()){
                    if(strlen( substr( $insert_usuario->username, 0, strpos($insert_usuario->username, '@') )  ) >= 3){
                        $user_email = substr($insert_usuario->username, 0, 3)."...".substr($insert_usuario->username, strpos($insert_usuario->username, '@'), strlen($insert_usuario->username));
                    }else {
                        $user_email = $insert_usuario->username;
                    }
                    
                    $this->response([
                        'status' => true,
                        'usuario' => $insert_usuario,
                        'message' => str_replace("%s", $user_email, $this->lang->line('user_registration_successful'))
                    ], REST_Controller::HTTP_OK); //200
                }else{
                    //echo $this->email->print_debugger();
                    $this->response([
                        'status' => true,
                        'usuario' => $insert_usuario,
                        'message' => $this->lang->line('user_register_successfull_mail_not_send')
                    ], REST_Controller::HTTP_OK); //200
                }
            }else{
                $this->response([
                    'status' => true,
                    'message' => $this->lang->line('not_register_account')
                ], REST_Controller::HTTP_OK); //400
            }
        }
    }

    /**
    * User login https://github.com/firebase/php-jwt
    * --------------------------
    * @param: username or email address
    * @param: password
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/usuario/login
    */
    public function login_post(){

        $this->load->helper('date');

        # Form Validation
        $this->form_validation->set_rules($this->Usuario_model->rules(true));

        if($this->form_validation->run() == FALSE){
            $this->response([
                'status' => false,
                'message' => $this->form_validation->error_array()
            ], REST_Controller::HTTP_NOT_FOUND); //400
        }else{
            $data = $this->security->xss_clean($this->input->post());  # XSS filtering
            $usuario = $data["usuario"];

            $usuario_output = $this->Usuario_model->login( $usuario['username'], $usuario['password'] );

            if($usuario_output != null ){
                $estado = $this->Estado_model->getById($usuario_output->id_estado);

                if($estado->code === 'ACTIVO'){
                    $newtime = time() + TOKEN_EXPIRE_TIME_DAY; // hours; 60 mins; 60secs

                    $datos = $this->Datos_model->getByIdUser($usuario_output->id_usuario);
                
                    unset($usuario_output->password);
                    unset($usuario_output->id_rol);
                    unset($usuario_output->id_estado);
                    $usuario_output->time = $newtime;
                    $usuario_output->ip_client = $this->util->get_client_ip_env();
                    $usuario_output->expiration = date('Y-m-d H:i:s', $newtime);  //fecha de expiracion de la session
                    $user_token = $this->authorization_token->generateToken($usuario_output);

                    $this->response([
                        'status' => true,
                        'message' =>  $this->lang->line('user_login_successfull'),  
                        'token' => $user_token
                    ], REST_Controller::HTTP_OK); //200
                }else{
                    $this->response([
                        'status' => false,
                        'message' =>  $this->lang->line('user_not_active_check_email_activacion'),
                    ], REST_Controller::HTTP_OK); //200
                }
            }else{
                $this->response([
                    'status' => false,
                    'message' => $this->lang->line('invalid_username_password')
                ], REST_Controller::HTTP_CREATED); //400
            }
        }
    }

    /**
    * User Confirmar Account 
    * --------------------------
    * @param: username or email address
    * @param: password
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/usuario/confirmar-cuenta/(:any)/(:any)
    */
    public function confirmAccount_get($token_usr, $token_confirm){

        $this->security->xss_clean($this->input->get());  # XSS filtering parameter get

        $token_usr = $this->security->xss_clean($token_usr);  # XSS filter
        $token_confirm = $this->security->xss_clean($token_confirm);  # XSS filter

        $usuario = $this->Usuario_model->getByTokenAndTokenConfirm($token_usr, $token_confirm);

        if(null !== $usuario){

            $estado = $this->Estado_model->getByCode('ACTIVO');

            #actualizamos el estado del usuario
            $usuario->id_estado = $estado->id;
            $out_user = $this->Usuario_model->update( $usuario, false );

            #actualizamos el token 
            $confirm_user = $this->ConfirmarUsuario_model->getByToken($token_confirm);
            $confirm_user->activo = false;
            $out_confirm_user = $this->ConfirmarUsuario_model->update( $confirm_user );

            $current_date = strtotime(date(DATETIME_FORMAT));
            $expiret_at = strtotime($out_confirm_user->expires_at);

            if(null !== $out_confirm_user && $expiret_at >= $current_date  ){  //cuenta activa con exito
                $this->response([
                    'status' => true,
                    'message' => $this->lang->line('account_succesfull_active'),
                ], REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    'status' => false,
                    'message' => $this->lang->line('account_not_active') 
                ], REST_Controller::HTTP_OK);
            }
        }else{
            $this->response([
                'status' => false,
                'message' => $this->lang->line('account_not_active') 
            ], REST_Controller::HTTP_OK);
        }
    }

    public function username_unique($username)
    {
        $usuario_output = $this->Usuario_model->getByUsername($username);

        if (null !== $usuario_output)
        {
            $this->form_validation->set_message('username_unique', 'El {field} ya esta registrado, por favor ingrese otro correo electronico');
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }

    /**
    * User by token
    * --------------------------
    * @param: token
    * --------------------------
    * @method: GET
    * @author: RAR
    * @link: api/usuario/get-by-token/(:any)
    */
    public function getByToken_get($token){

        $token = $this->security->xss_clean($token);  # XSS filtering

        $token_verified = self::getTokenVerified($token);

        $token = $this->security->xss_clean($this->input->get('auth', TRUE));   //token de authentication
        $is_valid_token = $this->authorization_token->validateToken($token);

        if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
            $usuario = $this->Usuario_model->getByToken($token_verified);
            if($usuario !== null){
                $data[$this->lang->line('usuario')] = $usuario;

                $datos = $this->Datos_model->getByIdUser($usuario->id);
                if(null !== $datos){
                    $pais = $this->Country_model->getById($datos->id_country);

                    unset($usuario->password);
                    unset($usuario->id_estado);

                    if(!is_null($datos->image) && !empty($datos->image) ){
                        $image_user = file_get_contents( FCPATH.'public/uploads/usuario/'. $datos->image);
                        $image_user = base64_encode($image_user);

                        $datos->image = 'data:application/png;base64,'.$image_user;
                    }

                    if(!is_null($datos->thumbail) && !empty($datos->thumbail) ){
                        $image_user_thumb = file_get_contents( FCPATH.'public/uploads/usuario/'. $datos->thumbail);
                        $image_user_thumb = base64_encode($image_user_thumb);

                        $datos->thumbail = 'data:application/png;base64,'.$image_user_thumb;
                    }

                    $data[$this->lang->line('datos_usuario')] = $datos;
                    $data[$this->lang->line('pais')] = $pais;
                }

                $this->response([
                    'status' => true,
                    'message' =>  $this->lang->line('item_has_found'),
                    'data' => $data
                ], REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => $this->lang->line('not_item_were_found')
                ], REST_Controller::HTTP_NO_CONTENT); // NOT_FOUND (404) being the HTTP response code
            }
        }
    }

    /**
    * User by token
    * --------------------------
    * @param: token
    * --------------------------
    * @method: GET
    * @author: RAR
    * @link: api/usuario/get-thumbail
    */
    public function getImageThumbail_get(){

        //$token = $this->security->xss_clean($token);  # XSS filtering

        //$token_verified = self::getTokenVerified($token);
        $this->security->xss_clean($this->input->get()); 

        $token = $this->input->get('auth', TRUE);   //token de authentication
        $is_valid_token = $this->authorization_token->validateToken($token);

        if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){

            //$usuario = $this->Usuario_model->getByToken($token_verified);
            $usuario = $this->Usuario_model->getByTokenJWT($is_valid_token['data']);
            if($usuario !== null){

                $datos = $this->Datos_model->getByIdUser($usuario->id);
                
                unset($usuario->password);
                unset($usuario->id_estado);

                if($datos !== null && $datos->thumbail !== null ){
                    $image_user_thumb = file_get_contents( FCPATH.'public/uploads/usuario/'. $datos->thumbail);
                    $image_user_thumb = base64_encode($image_user_thumb);

                    $this->response([
                        'status' => true,
                        'message' =>  $this->lang->line('item_has_found'),
                        $this->lang->line('thumbail') => 'data:application/png;base64,'.$image_user_thumb
                    ], REST_Controller::HTTP_OK);
                }else{
                    $this->response([
                        'status' => true,
                        'message' =>  $this->lang->line('item_has_found'),
                        $this->lang->line('thumbail') => ''
                    ], REST_Controller::HTTP_OK);
                }
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => $this->lang->line('not_item_were_found')
                ], REST_Controller::HTTP_NO_CONTENT); // NOT_FOUND (404) being the HTTP response code
            }
        }
    }


    /**
    * Change password
    * --------------------------
    * @param: form change password
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/usuario/change-password-members
    */
    public function changePasswordMembers_post(){
        $this->form_validation->set_rules('usuario[password]', 'Password', 'required');

		if( $this->form_validation->run() == true){
            $data = $this->security->xss_clean($this->input->post());  # XSS filtering

            $token = $data['auth'];   //token de authentication
            $is_valid_token = $this->authorization_token->validateToken($token);

            if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
                $usuario = $this->Usuario_model->getByTokenJWT($is_valid_token['data']);

                $password_new = $data["usuario"];
                $login = $this->Usuario_model->login($usuario->username, $password_new['password_old']);
                if(null !== $login){
                    if($password_new['password'] === $password_new['password_repeat'] ){
                        $usuario = $this->Usuario_model->getById($usuario->id);
                        $usuario->password = $password_new['password'];
                        $out_usuario = $this->Usuario_model->update( $usuario );

                        if($out_usuario !== null){
                            $this->response([
                                'status' => true,
                                'message' => $this->lang->line('password_changed')
                            ], REST_Controller::HTTP_OK);
                        }else{
                            $this->response([
                                'status' => false,
                                'message' => $this->lang->line('date_not_updated')
                            ], REST_Controller::HTTP_CREATED);
                        }
                    }else{
                        $this->response([
                            'status' => false,
                            'message' => $this->lang->line('password_not_equals')
                        ], REST_Controller::HTTP_CREATED);
                    }
                }else{
                    $this->response([
                        'status' => false,
                        'message' => $this->lang->line('user_not_found')
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
    }

    /**
    * Change image
    * --------------------------
    * @param: form image
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/usuario/change-image
    */
    public function changeImage_post(){
        $this->form_validation->set_rules('usuario[image]', 'Image', 'required');
        //$this->form_validation->set_rules('userfile', 'Photo', 'callback_checkPostedFiles');

		if( $this->form_validation->run() == true){
            $data = $this->security->xss_clean($this->input->post());  # XSS filtering

            $token = $data['auth'];   //token de authentication
            $is_valid_token = $this->authorization_token->validateToken($token);

            if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
                $usuario = $this->Usuario_model->getByTokenJWT($is_valid_token['data']);

                if(null !== $usuario){
                    $image = $data["usuario"];
                    
                    $datos = $this->Datos_model->getByIdUser($usuario->id);
		    
                    $this->util->base64ToImage($image['image'], $usuario->username);
                    $this->util->base64ToImageResize($image['image'], $usuario->username.".thumb", 100,100);

                    if(null !== $datos ){
                        
                        $datos->image = $usuario->username.".png";
                        $datos->thumbail = $usuario->username.".thumb.png";
                        $out_datos = $this->Datos_model->update( $datos );

                        if($out_datos !== null){
                            $this->response([
                                'status' => true,
                                'message' => $this->lang->line('data_updated')
                            ], REST_Controller::HTTP_OK);
                        }else{
                            $this->response([
                                'status' => false,
                                'message' => $this->lang->line('data_not_updated')
                            ], REST_Controller::HTTP_CREATED);
                        }
                    }else{

                        $datos['image'] = $usuario->username.".png";
                        $datos['thumbail'] = $usuario->username.".thumb.png";
                        $datos['id_usuario'] = $usuario->id;
                        $out_datos = $this->Datos_model->create( $datos );

                        if(null !==  $out_datos){
                            $this->response([
                                'status' => true,
                                'message' => $this->lang->line('data_updated')
                            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
                        }else{
                            $this->response([
                                'status' => false,
                                'message' => $this->lang->line('user_not_found')
                            ], REST_Controller::HTTP_CREATED); // NOT_FOUND (404) being the HTTP response code
                        }
                    }
                }else{
                    $this->response([
                        'status' => false,
                        'message' => $this->lang->line('user_not_found')
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

    /** validation image */
    public function checkPostedFiles()
    {
        $file_name = trim(basename(stripslashes($_FILES['userfile']['name'])), ".\x00..\x20");
        $file_name = str_replace(" ", "", $file_name);
         if (isset($_FILES['userfile']) && !empty($file_name)) {
            $allowedExts = array("jpeg", "jpg", "png"); // use as per your requirement
            $extension = end(explode(".", $file_name));
            if (in_array($extension, $allowedExts)) {
                 return true;
            } else {
                $this->form_validation->set_message('checkPostedFiles', "You can upload jpg or png image only");
                return false;
            }
         } else {
             $this->form_validation->set_message('checkPostedFiles', "You must upload an image!");
             return false;
         }
    }
}