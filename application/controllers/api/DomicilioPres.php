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
class DomicilioPres extends MY_Controller {

    public function __construct($config = 'rest'){
		parent::__construct($config);

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, OPTIONS, POST");
        header("Access-Control-Allow-Headers: *");

        $this->load->model('Persona_model');
        $this->load->model('Domicilio_model');
        $this->load->model('Prescripcion_model');
        //$this->load->model('Solicitante_model');
        //$this->load->model('RepresentanteLegal_model');
        //$this->load->model('Usuario_model');

        $this->load->library('Authorization_Token');

       /*$this->load->library('Util');

        $this->form_validation->set_error_delimiters('', '');

        $this->form_validation->set_message('required', 'El campo %s es obligatorio');
        $this->form_validation->set_message('integer', 'El campo %s deve poseer solo numeros enteros');
        $this->form_validation->set_message('is_unique', 'El campo %s ya esta registrado');
        $this->form_validation->set_message('max_length', 'El Campo %s debe tener un Maximo de %d Caracteres');*/
    }

public function update_post (){

        
}
   

   public function getByToken_get($token){

      $tokenp = $this->security->xss_clean($token);  # XSS filtering

         //$token_verified = self::getTokenVerified($token);
         //$token = $this->security->xss_clean($this->input->get('auth', TRUE));   //token de authentication
         //print_r($tokenp);
         //$is_valid_token = $this->authorization_token->validateToken($token);

        if(null !== $tokenp){
            
            $prescripcion = $this->Prescripcion_model->getByToken($tokenp);
           // $this->Prescripcion_model->create($prescripcion);
            //exit;

            if($prescripcion !== null){
                //print_r("hola mundo");
                //exit;
                 $this->response([
                                'status' => true,
                                'message' => $this->lang->line('item_was_has_add'),
                                $this->lang->line('prescripcion') => $prescripcion
                    //'message' => $this->lang->line('not_item_were_found')
                ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
            }
        }else{
            $this->response([
                'status' => false,
                'message' => $this->lang->line('token_is_invalid'),
            ], REST_Controller::HTTP_NETWORK_AUTHENTICATION_REQUIRED); // NOT_FOUND (400) being the HTTP response code
        }

        $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }


    public function createDireccion_post(){

        //$this->form_validation->set_rules('domicilio[latitud]', 'Latitud del Domicilio', 'required|trim');
        //$this->form_validation->set_rules('domicilio[longitud]', 'Longitud del Domicilio', 'required|trim');
        //$this->form_validation->set_rules('domicilio[image]', 'Imagen del Mapa', 'required|trim');
         header("Access-Control-Allow-Origin: *");

          $this->form_validation->set_rules('pres_prescripcion[fur]', 'Número Fur', 'required');
        //echo "llego aka doreccion";
        if( $this->form_validation->run() == true){

            $data = $this->security->xss_clean($this->input->post());  # XSS filtering
            
               $prescripcion = $data["pres_prescripcion"];

               $prescripcion_out = $this->Prescripcion_model->getByToken($prescripcion['token']);

               $persona = $this->Persona_model->getByIdPrescripcion( $prescripcion_out->id);
              
               $detalle = $data["domicilio"];
            
                    $detalle['id_persona'] = $persona->id;
                    //print_r($domicilio['id_persona']);
                    $detalle = $this->Domicilio_model->create($detalle);

                    $this->response([
                        'status' => true,
                        'message' => $this->lang->line('item_was_has_add'),
                        $this->lang->line('domicilio') => $detalle
                    ], REST_Controller::HTTP_OK);
           }else{
            $this->response([
                'status' => false,
                'message' => validation_errors()
            ], REST_Controller::HTTP_ACCEPTED); 
        }
        $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_RESET_CONTENT);
    }
 


    public function updatePrescripcion_post()
    {
     
       $this->form_validation->set_rules('domicilio[zona]', 'Zona o Barrio Valido', 'required|trim|strtoupper');
        $this->form_validation->set_rules('domicilio[direccion]', 'Calle o Avenida valido', 'required|trim|strtoupper');

     echo "direccion ya......";
     if( $this->form_validation->run() )
      {     
         $data = $this->security->xss_clean($this->input->post());  # XSS filtering 
         print_r($data);
         $domicilio_in = $data['domicilio'];
         $prescripcion_in = $data['prescripcion'];
        // print_r($prescripcion_in);
        $prescripcion = $this->Prescripcion_model->getByToken($prescripcion_in['token']);
        //print_r($prescripcion);
        
        
         if(!is_null($prescripcion)){
           // $domicilio = $this->PresRepresentanteLegal_model->getByIdPrescripcion($prescripcion->id); //$prescripcion->id
            $persona = $this->Persona_model->getByIdPrescripcion($prescripcion->id);
            $domicilio = $this->Domicilio_model->getByIdPersona($persona->id);
           // print_r($domicilio);
            $domicilio->zona = $domicilio_in['zona'];
            $domicilio->direccion =  $domicilio_in['direccion'];
            $domicilio = $this->Domicilio_model->update($domicilio);
         
           $this->response([
            'status' => true,
            'message' => 'proceso realizado con exito',
          ], REST_Controller::HTTP_OK);
         
         }else{
          //mensaje de error donde nose encontr la prescripcion aqui tu mensaje
            $this->response([
              'status' => false,
              'message' => 'Prescripcion no encontrada',
            ], REST_Controller::HTTP_OK);
         }
      
      }else{
          $this->response([
              'status' => false,
              'message' => validation_errors()
          ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
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
