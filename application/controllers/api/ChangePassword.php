<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Controller for user, this controller extends for MY_Controller
 *
 * @package         restServerAtm
 * @subpackage      Contact
 * @category        Controller
 * @author          Ronald Acha Ramos
 * @license         MIT
 */

class ChangePassword extends MY_Controller
{
    protected $baseurl;

    public function __construct()
    {
        parent::__construct();
        $baseurl = base_url();

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST");
        header("Access-Control-Allow-Headers: *");

        //$this->load->model('Datos_model');
        $this->load->model('Usuario_model');
        $this->load->model('ChangePassword_model');

        $this->load->library('email');
        $this->load->helper('file');
        $this->load->library('Util');

        $this->load->library('Authorization_Token');
    }

    /**
    * Request change password
    * --------------------------
    * @param: array usuario
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/change-password/request-token
    */
    public function requestToken_post(){

        //$this->form_validation->set_rules('usuario[token]', 'Token de Usuario', 'required');
        $this->form_validation->set_rules('usuario[username]', 'Correo Electronico', 'required');
        //$this->form_validation->set_rules('contact[comment]', 'Comentario', 'required');

		if( $this->form_validation->run() == true){
            
            $data = $this->security->xss_clean($this->input->post());  # XSS filtering
            $in_usuario = $data["usuario"];

            $usuario = $this->Usuario_model->getByUsername($in_usuario['username']);
            $change_password['id_usuario'] = $usuario->id;
            $change_password = $this->ChangePassword_model->create( $change_password );  //usuario[username]
            
            $message = read_file(APPPATH."controllers/template_email/forgot_password.html");  //sprintf($formato, $num, $ubicación); 

            $replace_email = array('$usuario', '$userEmail', '$urlConfirmEmail', '$page_cliente');
            $new_item = array($usuario->username, $usuario->username, 
            URL_BASE_CLIENTE.'/change-contrasenia?token='.$usuario->token.'&tokencp='.$change_password->token.'&username='.$usuario->username, URL_BASE_CLIENTE);

            $message = str_replace($replace_email, $new_item, $message);

            $this->email->set_newline("\r\n");
            $this->email->from('notificaciones@cochabamba.bo', $this->lang->line('administracion_tributaria_municipal'));  
            $this->email->to($usuario->username);
            $this->email->cc('notificaciones@cochabamba.bo');
            $this->email->subject($this->lang->line('change_of_password'));  
            $this->email->message($message);

            if( $this->email->send() ){

                if($change_password != null){
                    $this->response([
                        'status' => true,
                        'message' => $this->lang->line('request_change_password_successfull'),
                        $this->lang->line('change_password_class') => $change_password
                    ], REST_Controller::HTTP_OK);
                }else{
                    $this->response([
                        'status' => true,
                        'usuario' => $insert_usuario,
                        'message' => $this->lang->line('user_register_successfull_mail_not_send')
                    ], REST_Controller::HTTP_OK); //200
                }
            }else{
                $this->response([
                    'status' => true,
                    'usuario' => $insert_usuario,
                    'message' => $this->lang->line('user_register_successfull_mail_not_send')
                ], REST_Controller::HTTP_OK); //200
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
    * Change password
    * --------------------------
    * @param: array usuario
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/change-password/change
    */
    public function changePassword_post(){
        $this->form_validation->set_rules('usuario[token]', 'Token de Usuario', 'required');
        $this->form_validation->set_rules('usuario[username]', 'Correo Electronico', 'required');
        $this->form_validation->set_rules('change_password[token]', 'Token Change Password', 'required');

		if( $this->form_validation->run() == true){
            $data = $this->security->xss_clean($this->input->post());  # XSS filtering
            $in_usuario = $data["usuario"];
            $in_change_password = $data["change_password"];

            $usuario = $this->Usuario_model->getByTokenAndTokenChangePassword($in_usuario['token'], $in_change_password['token']);
            
            if(!is_null( $usuario)){
                $change_password = $this->ChangePassword_model->getByToken( $in_change_password['token'] );

                if(!is_null( $change_password)){ 
                    $current_date = strtotime(date(DATETIME_FORMAT));
                    $expiret_at = strtotime($change_password->expires_at);

                    if($expiret_at >= $current_date  ){ 

                        $usuario->password = $in_usuario['password'];
                        $this->Usuario_model->update( $usuario );
                        
                        $change_password->activo = false;
                        $change_password = $this->ChangePassword_model->update( $change_password );

                        if(!is_null($change_password)){
                            $this->response([
                                'status' => true,
                                'message' => $this->lang->line('changed_password_updated'),
                            ], REST_Controller::HTTP_OK);
                        }else{
                            $this->response([
                                'status' => false,
                                'message' => $this->lang->line('changed_password_not_updated'),
                            ], REST_Controller::HTTP_OK);
                        }
                    }else{
                        $this->response([
                            'status' => false,
                            'message' => $this->lang->line('token_change_password_expiret') 
                        ], REST_Controller::HTTP_OK);
                    }
                }else{
                    $this->response([
                        'status' => false,
                        'message' => $this->lang->line('token_change_password_not_found') 
                    ], REST_Controller::HTTP_OK);
                }
            }else{
                $this->response([
                    'status' => false,
                    'message' => $this->lang->line('request_changed_password_not_found') 
                ], REST_Controller::HTTP_OK);
            }
        }else{
            $this->response([
                'status' => false,
                'message' => validation_errors()
            ], REST_Controller::HTTP_ACCEPTED); 
		}
        $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_RESET_CONTENT); // CREATED (201) being the HTTP response code
    }
 
}


