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

class Contact extends MY_Controller
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

        $this->load->model('Contact_model');
        $this->load->model('TypeContact_model');

        $this->load->library('email');
        $this->load->helper('file');
        $this->load->library('Util');

        $this->form_validation->set_error_delimiters('', '');

        $this->form_validation->set_message('required', 'El campo %s es obligatorio');
        $this->form_validation->set_message('integer', 'El campo %s deve poseer solo numeros enteros');
        $this->form_validation->set_message('is_unique', 'El campo %s ya esta registrado');
        $this->form_validation->set_message('max_length', 'El Campo %s debe tener un Maximo de %d Caracteres');
    }

    /**
    * Create a new contact
    * --------------------------
    * @param: array contact
    * --------------------------
    * @method: POST
    * @author: RAR
    * @link: api/contact/create
    */
    public function create_post(){

        $this->form_validation->set_rules('contact[name]', 'Nombre', 'required|trim|strtoupper');
        $this->form_validation->set_rules('contact[email]', 'Correo Electronico', 'required|valid_email|trim|strtoupper');
        $this->form_validation->set_rules('contact[comment]', 'Comentario', 'required|trim|strtoupper');

		if( $this->form_validation->run() == true){
            
            $data = $this->security->xss_clean($this->input->post());  # XSS filtering

            $type_contact = $this->TypeContact_model->getByCode('DEFAULT');

            $contact = $data["contact"];
            $message = "<strong>Tipo de Observación: </strong>".$type_contact->name." <br/><br/> <strong>Nombre: </strong>".$contact['name']."<br/> <strong>Email: </strong>".$contact['email']." <br/> <strong>Comentario: </strong>".$contact['comment'];

            $this->email->set_newline("\r\n");
            $this->email->from('contactanos@cochabamba.bo', $this->lang->line('administracion_tributaria_municipal'));  
            $this->email->to($contact['email']);
            $this->email->cc('contactanos@cochabamba.bo');
            $this->email->subject($this->lang->line('contactus_user'));  
            $this->email->message($message);

            if( $this->email->send() ){
                $contact['id_type_contact'] = $type_contact->id;
                $out_contact = $this->Contact_model->create( $contact );

                if($out_contact != null){
                    $this->response([
                        'status' => true,
                        'message' => $this->lang->line('comentario_received_satisfactory_mail_send'),
                        $this->lang->line('contact') => $out_contact
                    ], REST_Controller::HTTP_OK);
                }else{
                    $this->response([
                        'status' => true,
                        'usuario' => $insert_usuario,
                        'message' => $this->lang->line('comentario_received_satisfactory_mail_not_send')
                    ], REST_Controller::HTTP_OK); //200
                }
            }else{
                $this->response([
                    'status' => true,
                    'usuario' => $insert_usuario,
                    'message' => $this->lang->line('comentario_received_satisfactory_mail_not_send')
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
 
}


