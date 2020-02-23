<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller for user, this controller extends for MY_Controller
 *
 * @package         restServerAtm
 * @subpackage      Cobros
 * @category        Controller
 * @author          Ronald Acha Ramos
 * @license         MIT
 */
class Cobros extends MY_Controller {

    function __construct()
    {
        parent::__construct();

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST");
        header("Access-Control-Allow-Headers: *");

        $this->load->library('Util');
        $this->load->model('Cobros_model');
        $this->load->model('ContadorIntento_model');
        $this->load->model('Usuario_model');
        $this->load->model('DeclaracionJurada_model');

        $this->load->library('Authorization_Token');

        !$this->db_recaudaciones = $this->load->database('recaudaciones_db', TRUE) ? $this->db_recaudaciones = $this->load->database('recaudaciones_db', TRUE) : false;
    }

    /**
     * this method permit to get a item by $id
     */
    public function getFur_get($id){

        $this->security->xss_clean($this->input->get());

        $token = $this->input->get('auth', TRUE);   //token de authentication
        $is_valid_token = $this->authorization_token->validateToken($token);

        if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){
            $usuario = $this->Usuario_model->getByTokenJWT($is_valid_token['data']);
            if(null !== $usuario){
                $fur_verified = self::getFurVerified($id);

                if(self::validateIntent()){

                    $jsonFur = $this->Cobros_model->getFurValidate($fur_verified);

                    if(!is_null($jsonFur) ){
                        if($jsonFur->status === true){

                            $declaracion_jurada = $this->DeclaracionJurada_model->getByFur($fur_verified);

                            if( is_null($declaracion_jurada) ){
                                $this->db_recaudaciones->select('*');
                                $this->db_recaudaciones->from('mt_tramites');
                                $this->db_recaudaciones->where('tram_id', $jsonFur->data->tram_id);
                                $respuesta_consulta =  $this->db_recaudaciones->get();
                                $row = $respuesta_consulta->row();

                                $derecho_admision = "";
                                if($row->codigo_admision === 'DA88')    //DERECHO DE ADMISION
                                    $derecho_admision = 'PERMANENTE'; 
                                if($row->codigo_admision === 'DA89')
                                    $derecho_admision = 'TEMPORAL';

                                $this->response([
                                    'status' => true,
                                    'message' =>  $this->lang->line('item_has_found'),
                                    $this->lang->line('fur') => $jsonFur->data,
                                    'derecho_admision' => $derecho_admision,
                                    $this->lang->line('total_intentos') => MAXIMO_INTENTOS_LICENCIA_ACTIVIDAD_ECONOMICA
                                ], REST_Controller::HTTP_OK);
                            }else{
                                $this->response([
                                    'status' => false,
                                    'message' => str_replace("%fur", $declaracion_jurada->fur, str_replace("%num_orden", $declaracion_jurada->numero, $this->lang->line('num_orden_whit_fur'))),
                                    $this->lang->line('fur') => $jsonFur->data,
                                    $this->lang->line('total_intentos') => MAXIMO_INTENTOS_LICENCIA_ACTIVIDAD_ECONOMICA
                                ], REST_Controller::HTTP_OK);
                            }
                        }else{
                            if(!is_null($jsonFur->data)){
                                $this->response([
                                    'status' => false,
                                    'message' => $jsonFur->data,
                                    $this->lang->line('total_intentos') => MAXIMO_INTENTOS_LICENCIA_ACTIVIDAD_ECONOMICA
                                ], REST_Controller::HTTP_OK);
                            }else{
                                $this->response([
                                    'status' => false,
                                    'message' => $this->lang->line('fur_validate'),
                                    $this->lang->line('total_intentos') => MAXIMO_INTENTOS_LICENCIA_ACTIVIDAD_ECONOMICA
                                ], REST_Controller::HTTP_OK);
                            }
                        }
                    }else{
                        $this->response([
                            'status' => false,
                            'message' => $this->lang->line('not_item_were_found'),
                            $this->lang->line('total_intentos') => MAXIMO_INTENTOS_LICENCIA_ACTIVIDAD_ECONOMICA
                        ], REST_Controller::HTTP_NO_CONTENT);
                    }
                }else{
                    $this->response([
                        'status' => false,
                        'message' => $this->lang->line('number_intent_limit'),
                        $this->lang->line('total_intentos') => MAXIMO_INTENTOS_LICENCIA_ACTIVIDAD_ECONOMICA
                    ], REST_Controller::HTTP_OK);
                }
            }else{
                $this->response([
                    'status' => false,
                    'message' => $this->lang->line('user_not_found'),
                ], REST_Controller::HTTP_OK);
            }
        }else{
            $this->response([
                'status' => false,
                'message' => $this->lang->line('token_is_invalid'),
            ], REST_Controller::HTTP_NETWORK_AUTHENTICATION_REQUIRED); // NOT_FOUND (400) being the HTTP response code
        }

        $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }

/**
 * verifacion de fur de prescritpcion
 */
    function getFurPrescripcion_get($id){
        header("Access-Control-Allow-Origin: *");

        $fur_verified = self::getFurVerified($id);

        $curl = curl_init();
        $url = "http://192.168.104.117/cb-dev/web/index.php?r=cobros-varios/validar-fur&fur=".$fur_verified;

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        
        $result = curl_exec($curl);
        curl_close($curl);

        $jsonFur = json_decode($result);

        if(!is_null($jsonFur)){
            if($jsonFur->status == true){
                if($jsonFur->data->tram_id === 107)
                    $jsonFur->data->contribuyente = 'NATURAL';
                if($jsonFur->data->tram_id === 109 || $jsonFur->data->tram_id === 108)
                    $jsonFur->data->contribuyente = 'JURIDICO';
                $this->response([
                    'status' => true,
                    'message' =>  $this->lang->line('item_has_found'),
                    $this->lang->line('fur') => $jsonFur->data
                ], REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    'status' => false,
                    'message' => $this->lang->line('fur_validate')
                ], REST_Controller::HTTP_OK);
            }
        }else{
            $this->response([
                'status' => false,
                'message' => $this->lang->line('not_item_were_found')
            ], REST_Controller::HTTP_NO_CONTENT);
        }

        $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }

 /*
  * nuevo servicio  
  * de vehiculo patentes, etc
  */
 
   
    function licenciaPublicidad_post(){

        $data = $this->security->xss_clean($this->input->post());

        if(!is_null($data) ){
            $object = $this->Cobros_model->patentePublicidad($data['codigo_licencia']);
            $this->response([
                    'status' => true,
                    'message' =>  $this->lang->line('item_has_found'),
                    $this->lang->line('licenciaPublicidad') => $object

            ], REST_Controller::HTTP_OK);
        }else{
            $this->response([
                'status' => false,
                'message' => $this->lang->line('not_item_were_found')
            ], REST_Controller::HTTP_NO_CONTENT);
        }
         $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }


    function licenciaEconomica_post(){

        $data = $this->security->xss_clean($this->input->post());

        if(!is_null($data) ){
            $object = $this->Cobros_model->patenteActividadEconomica($data['numero_actividad']);
            $this->response([
                    'status' => true,
                    'message' =>  $this->lang->line('item_has_found'),
                    $this->lang->line('actividadEcon') => $object
            ], REST_Controller::HTTP_OK);
        }else{
            $this->response([
                'status' => false,
                'message' => $this->lang->line('not_item_were_found')
            ], REST_Controller::HTTP_NO_CONTENT);
        }
         $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }


    function licenciaSitiosMunicipales_post(){

        $data = $this->security->xss_clean($this->input->post());

        if(!is_null($data) ){
            $object = $this->Cobros_model->patenteSitiosMunicipales($data['codigo_licencia']);
            $this->response([
                    'status' => true,
                    'message' =>  $this->lang->line('item_has_found'),
                    $this->lang->line('sitios') => $object
            ], REST_Controller::HTTP_OK);
        }else{
            $this->response([
                'status' => false,
                'message' => $this->lang->line('not_item_were_found')
            ], REST_Controller::HTTP_NO_CONTENT);
        }
         $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }
// preuba de vehiculo 

    function vehiculo_post(){

        $data = $this->security->xss_clean($this->input->post());
       // $placa = $this->input->post('vehiculo', TRUE);

        if(!is_null($data) ){
            $object = $this->Cobros_model->vehiculo(trim($data['numero']));
            $this->response([
                    'status' => true,
                    'message' =>  $this->lang->line('item_has_found'),
                    $this->lang->line('vehiculo') => $object
            ], REST_Controller::HTTP_OK);
        }else{
            $this->response([
                'status' => false,
                'message' => $this->lang->line('not_item_were_found')
            ], REST_Controller::HTTP_NO_CONTENT);
        }
         $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }




    /**
     * this method permit verified and validate $fur
     */
    private function getFurVerified($fur){
        if(!is_null($fur) && !empty($fur) && is_numeric($fur) ){
            return $fur;
        }else{
            $this->response([
                'status' => false,
                'message' => $this->lang->line('fur_required')
            ], REST_Controller::HTTP_ACCEPTED); // NOT_FOUND (404) being the HTTP response code
        }
    }

    private function validateIntent(){
        $contador_intento_db = $this->ContadorIntento_model->getByIdUsuario(1);  //buscamos por id_usuario

        if(null !== $contador_intento_db ){
            if($this->ContadorIntento_model->validar($contador_intento_db->id)){
                //echo "actualizando";
                $contador_intento_db->count = $contador_intento_db->count +1;
                $contador_intento = $this->ContadorIntento_model->update($contador_intento_db);
            }else{
                //echo "intentos superados, comuniquese con ATM para poder solicitar desbloqueo";
                return false;
            }
        }else{
            //echo "nuevo elemento";
            $contador_intento['count'] = 1;
            $contador_intento['id_usuario'] = 1;
            $contador_intento['count'] = 1;
            $contador_intento['declaracion_jurada'] = true;
            $contador_intento_db = $this->ContadorIntento_model->create($contador_intento);
        }
        return true;
    }
}
