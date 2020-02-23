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
class ObjectoTributario extends MY_Controller {

    function __construct()
    {
        parent::__construct();

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST");
        header("Access-Control-Allow-Headers: *");

        $this->load->library('Util');
        //$this->load->library('Authorization_Token');
        //$this->load->library('Authorization_Token');
         $this->load->database();
        $this->load->model('PresObjetoTributario_model');
        $this->load->model('Prescripcion_model');

       // !$this->db_recaudaciones = $this->load->database('recaudaciones_db', TRUE) ? $this->db_recaudaciones = $this->load->database('recaudaciones_db', TRUE) : false;
    }

    /**
     * this method permit to get a item by $id
     */
    public function getObjeto_post(){

        //$fur_verified = self::getFurVerified($id);
        echo"entor aka";
            
        $arrayValor = array('numero' => '3158HZA' ,'submit' => 'placa' );
          
        $url = "http://192.168.104.117/servicio/consulta-servicios/webservice.php";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $arrayValor);
       
        $result = curl_exec($curl);
        curl_close($curl);

        $jsonFur = json_decode($result);
      
        print_r($result);
        var_dump($jsonFur);
        
    }

    public function createObjTributario_post(){

         header("Access-Control-Allow-Origin: *");
         $this->form_validation->set_rules('pres_prescripcion[fur]', 'Número Fur', 'required');
          if( $this->form_validation->run() == true){
               echo "hola funciona bien ";
                $data = $this->security->xss_clean($this->input->post());
                //print_r($data);
                 $prescripcion = $data["pres_prescripcion"];
                  $prescripcion_out = $this->Prescripcion_model->getByToken($prescripcion['token']);
                // print_r($prescripcion_out);
                //$insert_prescripcion = $this->Prescripcion_model->create($prescripcion);

              //  $persona['id_prescripcion'] = $insert_prescripcion->id;

                 $pres_objeto_tributario=$this->input->post()['pres_objeto_tributario'];
                 $pres_objeto_gestion=$this->input->post()['pres_objeto_gestion'];

                 //$insert_gestion=$this->PresObjetoTributario_model->create($)
                //print_r($pres_objeto_tributario);
                //print_r($pres_objeto_gestion);
                foreach ($pres_objeto_gestion as $key => $value) 
                {
                    $pres_objeto_tributario["gestion"] = $value["gestion"];
                    $pres_objeto_tributario["id_prescripcion"] = $prescripcion_out->id;
                    $insert_objetoTributario = $this->PresObjetoTributario_model->create($pres_objeto_tributario);
                }

                $this->response([
                    'status' => true,
                    'message' => $this->lang->line('item_was_has_add'),
                    'prescripcion' =>  $insert_objetoTributario
                ], REST_Controller::HTTP_OK);
            }else{
                 $this->response([
                    'status' => false,
                    'message' => validation_errors()
                ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            }
            
            $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP 
    }

    public function updateObjTributario_post()
    {
       
        $this->form_validation->set_rules('pres_objeto_tributario[numero]', 'Numero o licencia', 'required|trim|strtoupper');
         //$this->form_validation->set_rules('pres_objeto_tributario[gestion]', 'Gestiones', 'required|trim|strtoupper');

  //echo"hola ";
      if( $this->form_validation->run() )
      {     
         $data = $this->security->xss_clean($this->input->post());  # XSS filtering 
         //print_r($data);
          $prescripcion_in = $data['prescripcion'];
          $prescripcion = $this->Prescripcion_model->getByToken($prescripcion_in['token']  );
          //print_r($prescripcion_in);
          $pres_objeto_tributario = $this->PresObjetoTributario_model->getByIdObjetoTributario($prescripcion->id);
          $pres_objeto_gestion_in = $data['pres_objeto_gestion'];
          $pres_objeto_tributario_in = $data['pres_objeto_tributario'];
          //print_r($pres_objeto_gestion_in);
         // print_r($pres_objeto_tributario);
            if(!is_null($pres_objeto_tributario)){
              foreach ($pres_objeto_tributario as $clave ) {
                $this->PresObjetoTributario_model->delete($clave);
              }
            //print_r($pres_objeto_gestion);
            var_dump($prescripcion);
              for ($i = 0; $i< sizeof($pres_objeto_gestion_in); $i++ ) {
                $pres_objeto_gestion['id_tipo_objeto_tributario'] = $pres_objeto_tributario_in['id_tipo_objeto_tributario'];
                $pres_objeto_gestion['numero'] = $pres_objeto_tributario_in['numero'];
                $pres_objeto_gestion['gestion'] = $pres_objeto_gestion_in[$i]['gestion'];
                $pres_objeto_gestion['id_prescripcion'] = $prescripcion->id;
                $pres_objeto_gestion_out = $this->PresObjetoTributario_model->create($pres_objeto_gestion );
              }
            }
        

      }
    }

}                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               