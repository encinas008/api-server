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

class Datos extends MY_Controller
{
    protected $baseurl;
    //protected $db_recaudaciones;
    //protected $estados = array(0 => array('key' => 'AC', 'value' => 'Activo'), 1 => array('key' => 'IN', 'value' => 'Inactivo'));

    public function __construct()
    {
        parent::__construct();
        $baseurl = base_url();

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST");
        header("Access-Control-Allow-Headers: *");

        $this->load->model('Datos_model');
        $this->load->model('Usuario_model');
        $this->load->model('Country_model');

        //$this->load->library('email');
        //$this->load->helper('file');
        //$this->load->library('Util');

        $this->load->library('Authorization_Token');
    }

    /**
    * Get Data User
    * --------------------------
    * @param: array datos
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/datos-usuario/get
    */
    public function getByUser_get(){

        $this->security->xss_clean($this->input->get());  # XSS filtering

        $token = $this->input->get('auth', TRUE);   //token de authentication
        $is_valid_token = $this->authorization_token->validateToken($token);

        if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
            $usuario = $this->Usuario_model->getByTokenJWT($is_valid_token['data']);
            $datos = $this->Datos_model->getByIdUser($usuario->id);

            if(null !== $datos ){
                $pais = $this->Country_model->getById($datos->id_country);
                $this->response([
                    'status' => true,
                    'message' => $this->lang->line('request_complete'),
                    $this->lang->line('datos_usuario') => $datos,
                    $this->lang->line('pais') => $pais
                ], REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    'status' => false,
                    'message' => $this->lang->line('not_item_were_found'),
                    $this->lang->line('datos_usuario') => null
                ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
            }
        }else{
            $this->response([
                'status' => false,
                'message' => $this->lang->line('token_is_invalid'),
            ], REST_Controller::HTTP_NETWORK_AUTHENTICATION_REQUIRED); // NOT_FOUND (400) being the HTTP response code
        }
    }

    /**
    * Create a new data user
    * --------------------------
    * @param: array datos
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/datos-usuario/create
    */
    public function create_post(){

        $this->form_validation->set_rules('datos[name]', 'Nombre', 'required|trim|strtoupper');
        $this->form_validation->set_rules('datos[apellido_paterno]', 'Apellido Paterno', 'required|trim|strtoupper');
        $this->form_validation->set_rules('datos[ci]', 'Documento de Identificación', 'required|trim|strtoupper');
        $this->form_validation->set_rules('datos[address]', 'Dirección', 'required|trim|strtoupper');
        $this->form_validation->set_rules('datos[id_country]', 'Pais', 'required');
        $this->form_validation->set_rules('datos[phone]', 'Teléfono', 'required|trim');

		if( $this->form_validation->run() == true){

            $data = $this->security->xss_clean($this->input->post(), TRUE);  # XSS filtering

            $token = $data['auth'];   //token de authentication
            $is_valid_token = $this->authorization_token->validateToken($token);

            if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){

                $usuario = $this->Usuario_model->getByTokenJWT($is_valid_token['data']);
                $datos_db = $this->Datos_model->getByIdUser($usuario->id);

                if(null !== $datos_db){
                    //actualizar
                    $datos = $data["datos"];
                    $datos_db->name = $datos['name'];
                    $datos_db->apellido_paterno = $datos['apellido_paterno'];
                    $datos_db->ci = $datos['ci'];
                    $datos_db->address = $datos['address'];
                    $datos_db->id_country = $datos['id_country'];
                    $datos_db->phone = $datos['phone'];

                    if( array_key_exists('apellido_materno', $datos) && !empty($datos['apellido_materno']) )
                        $datos_db->apellido_materno = $datos['apellido_materno'];
                    if( array_key_exists('company', $datos) && !empty($datos['company']) )
                        $datos_db->company = $datos['company'];

                    $out_datos = $this->Datos_model->update( $datos_db );
                }else{
                    $datos = $data["datos"];
                
                    $datos['id_usuario'] = $usuario->id;
                    $out_datos = $this->Datos_model->create( $datos );
                }

                if($out_datos != null){
                    $this->response([
                        'status' => true,
                        'message' => $this->lang->line('item_was_has_add'),
                        $this->lang->line('datos_usuario') => $out_datos
                    ], REST_Controller::HTTP_OK);
                }else{
                    $this->response([
                        'status' => true,
                        'usuario' => $insert_usuario,
                        'message' => $this->lang->line('item_not_was_has_add')
                    ], REST_Controller::HTTP_OK); //200
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
 
}


