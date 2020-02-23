  <?php
//header('Access-Control-Allow-Origin: *');
//header("Access-Control-Allow-Methods: GET, OPTIONS, POST");
//header("Access-Control-Allow-Headers: *");

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
class Prescripcion extends MY_Controller {

    public function __construct($config = 'rest')
	{
       // header('Access-Control-Allow-Origin: *, http://localhost:3000');
       // header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        //header("Access-Control-Allow-Headers: Authorization");
        parent::__construct($config);
        
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: *"); 
        //$this->load->model('DeclaracionJurada_model');

       // $this->load->model('DeclaracionJurada_model');

        $this->load->database();
        $this->load->model('PresObjetoTributario_model');
        //$this->load->model('presTipObjetoTriburario_model');
        $this->load->model('Prescripcion_model');
        $this->load->model('Persona_model');
        $this->load->model('Solicitante_model');
        $this->load->model('DatosJuridicos_model');
        $this->load->model('PresRepresentanteLegal_model');
        $this->load->model('Estado_model');
        $this->load->model('Domicilio_model');
        $this->load->model('Usuario_model');
        $this->load->model('DomicilioActividadEconomica_model');

        $this->load->library('Authorization_Token');
        $this->form_validation->set_error_delimiters('', '');

        $this->form_validation->set_message('required', 'El campo %s es obligatorio');
        $this->form_validation->set_message('integer', 'El campo %s deve poseer solo numeros enteros');
        $this->form_validation->set_message('is_unique', 'El campo %s ya esta registrado');
        $this->form_validation->set_message('max_length', 'El Campo %s debe tener un Maximo de %d Caracteres');
        
    }

    public function obtenerObjetoTributario_get()
    {
         header("Access-Control-Allow-Origin: *");
         $query = $this->db->query('SELECT name, id FROM pres_tipo_objeto_tributario');
          $data=$query->result();
        $this->response([
                        'status' => true,
                        'message' => $this->lang->line('successfully getting'),
                        'data' => $data
                    ], REST_Controller::HTTP_OK);
        }


    public function create_post()
    {

            header("Access-Control-Allow-Origin: *");
            if (array_key_exists('persona', $this->input->post())){
            $this->form_validation->set_rules('pres_prescripcion[fur]', 'Número Fur', 'required');
            $this->form_validation->set_rules('persona[expedido_en]', 'Lugar Donde fue expedido el CI', 'required|trim|strtoupper');
            $this->form_validation->set_rules('persona[nombre]', 'Nombre del contribuyente', 'required|trim|strtoupper');
            $this->form_validation->set_rules('persona[apellido_paterno]', 'apellido del contribuyente', 'required|trim|strtoupper');
            }
          if (array_key_exists('pres_representante_legal', $this->input->post())) {
             echo "hola funciona";

            $this->form_validation->set_rules('pres_representante_legal[razon_social]', 'NIT', 'required');
            // $usuario = $this->Usuario_model->getByTokenJWT($is_valid_token['data']);
          }
            if( $this->form_validation->run() == true)
            {       

                $data = $this->security->xss_clean($this->input->post());  # XSS filtering 
                 //print_r($data);
                 //echo "dato";
                $prescripcion = $data["pres_prescripcion"];  //aqui data donde esta definido
                // print_r($prescripcion);
                $persona = $data["persona"];
                //print_r($persona1);

                $insert_prescripcion = $this->Prescripcion_model->create($prescripcion);


                $persona['id_prescripcion'] = $insert_prescripcion->id;
                $persona = $this->Persona_model->create($persona);
                // print_r($persona);
                
                if( array_key_exists("pres_representante_legal",  $data)){
                  $insert_juridicos = $data["pres_representante_legal"];
                   
                   echo "siiiii";
                   print_r($insert_juridicos);
                   $var=$insert_juridicos['id_tipo_documento'];

                   print_r($insert_juridicos['id_tipo_documento']);
                  
                  if( !empty($var) ){
                          
                    $insert_juridicos["id_prescripcion"] = $insert_prescripcion->id;
                    $insert_juridicos = $this->PresRepresentanteLegal_model->create($insert_juridicos);
                
                  }else{

                  }
                }else{

                }
                unset($insert_prescripcion->id);
                
                $this->response([
                    'status' => true,
                    'message' => $this->lang->line('item_was_has_add'),
                    'prescripcion' =>  $insert_prescripcion
                ], REST_Controller::HTTP_OK);
            }else{
                 $this->response([
                    'status' => false,
                    'message' => validation_errors()
                ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            }
            
            $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP 
    }


    public function update_post()
    {
        header("Access-Control-Allow-Origin: *");
        $this->form_validation->set_rules('pres_representante_legal[id_tipo_documento]', 'Tipo Documento', 'required|trim|strtoupper');
        $this->form_validation->set_rules('persona[expedido_en]', 'Lugar Donde fue expedido el CI', 'required|trim|strtoupper');  //esto controlar, solo para tipo documento CI
        $this->form_validation->set_rules('persona[numero_documento]', 'Número Documento', 'required|trim|strtoupper');
        $this->form_validation->set_rules('persona[nombre]', 'Nombre', 'required|trim|strtoupper');
        $this->form_validation->set_rules('persona[apellido_paterno]', 'Apellido Paterno', 'required|trim|strtoupper');
        $this->form_validation->set_rules('pres_representante_legal[razon_social]', 'nombre Representante Legal', 'required|trim|strtoupper');
        //$this->form_validation->set_rules('pres_prescripcion[id]', 'id', 'required');
      if( $this->form_validation->run() )
      {     
         $data = $this->security->xss_clean($this->input->post());  # XSS filtering 
         //print_r($data);
         //exit;
         $prescripcion_in = $data['prescripcion'];
        // prn
         $pres_representante_legal_in = $data['pres_representante_legal'];
         print_r($pres_representante_legal_in);
         $persona_in = $data['persona'];
         print_r($persona_in);
         $prescripcion = $this->Prescripcion_model->getByToken($prescripcion_in['token']);
         //$pres_representante_legal_in = $this->PresRepresentanteLegal_model->create($pres_representante_legal_in);
         
         if(!is_null($prescripcion)){
         //print_r($prescripcion);
            $pres_representante_legal = $this->PresRepresentanteLegal_model->getByIdPrescripcion($prescripcion->id); //$prescripcion->id
             if(!is_null($pres_representante_legal))
             {
              echo "entro";
                  $pres_representante_legal->razon_social = $pres_representante_legal_in['razon_social'];
                  $pres_representante_legal->telefono =  $pres_representante_legal_in['telefono'];
                  $pres_representante_legal->numero_documento  =  $pres_representante_legal_in['num_docum_repre'];
                  $pres_representante_legal->correo_electronico =  $pres_representante_legal_in['correo_electronico'];
                   
                  $pres_representante_legal = $this->PresRepresentanteLegal_model->update($pres_representante_legal );

             } else {
                 $pres_representante_legal_in["id_prescripcion"] = $prescripcion->id;
                 $pres_representante_legal_in = $this->PresRepresentanteLegal_model->create($pres_representante_legal_in); 
               
               }
              // print_r($pres_representante_legal);

            $persona = $this->Persona_model->getByIdPrescripcion($prescripcion->id);
            $persona->nombre = $persona_in['nombre'];
            $persona->apellido_paterno = $persona_in['apellido_paterno'];
            $persona->apellido_materno = $persona_in['apellido_materno'];
            $persona->numero_documento = $persona_in['numero_documento'];
            $persona->id_tipo_documento = $persona_in['id_tipo_documento'];
            $persona->expedido_en = $persona_in['expedido_en'];
            $persona = $this->Persona_model->update($persona );
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

 
   public function search_post(){

        $this->security->xss_clean($this->input->post());

       // $token = $this->input->post('auth', TRUE);   //token de authentication
       // $is_valid_token = $this->authorization_token->validateToken($token);

       //if(null !== $is_valid_token && $is_valid_token['status'] === TRUE){

            //$usuario = $this->Usuario_model->getByTokenJWT($is_valid_token['data']);
            //print_r($usuario);
            $text = $this->input->post('search', TRUE);
           // $type = $this->input->post('type', TRUE);
          //  $list = $this->Prescripcion_model->search($text, $type,  $usuario->token );
            $list = $this->Prescripcion_model->search($text);

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

public function getObjetTributario_get($id)
{
              header("Access-Control-Allow-Origin: *");

           if(!is_null($id) && !empty($id) ){

                 $this->db->select('pres_objeto_tributario.*');
               // $this->db->from('datos_juridicos');
                 $this->db->from('pres_objeto_tributario');
                 $this->db->join('pres_prescripcion', 'pres_prescripcion.id = pres_objeto_tributario.id_prescripcion');
                //$this->db_recaudaciones->from('pres_objeto_tributario');
                 $this->db->where('id_prescripcion', $id);
                //$this->db_recaudaciones->where('estado', 'AC'); 
                 $query = $this->db->get();
                 $data = $query->result();
        
                $this->response([
                            'status' => true,
                            'message' => $this->lang->line('successfully getting'),
                           'data' => $data,
                            //'tri'=>$tri,
                            //'objetoTribu'=> $objetoTribu
                        ], REST_Controller::HTTP_OK);
        }
    }
    
     public function getDataContribuyente_get()
     {
        header("Access-Control-Allow-Origin: *");
       
        $this->db->select('fur, ptop.name, prl.razon_social');
        $this->db->from('pres_prescripcion as pp');
        $this->db->join('pres_objeto_tributario as pot', 'pp.id = pot.id_prescripcion');
        $this->db->join('pres_tipo_objeto_tributario as ptop', 'pot.id_tipo_objeto_tributario = ptop.id');
        $this->db->join('pres_representante_legal as prl','pp.id = prl.id_prescripcion');
        //$this->db->join('persona as p','pp.id = p.id_prescripcion');
        $this->db->group_by('fur, ptop.name, prl.razon_social' );
         $query=$this->db->get();
         $data=$query->result();
           
        $this->db->select('fur, ptop.name, p.nombre');
        $this->db->from('pres_prescripcion as pp');
        $this->db->join('pres_objeto_tributario as pot', 'pp.id = pot.id_prescripcion');
        $this->db->join('pres_tipo_objeto_tributario as ptop', 'pot.id_tipo_objeto_tributario = ptop.id');
        $this->db->join('persona as p','pp.id = p.id_prescripcion');
        $this->db->group_by('fur, ptop.name, p.nombre');
        $aux=$this->db->get();
         $res=$aux->result();
     
        $this->response([
                        'status' => true,
                        'message' => $this->lang->line('successfully getting'),
                       'juridico' => $data,
                        'natural' => $res
                        //'tri'=>$tri,
                        //'objetoTribu'=> $objetoTribu
                    ], REST_Controller::HTTP_OK);

       }

    public function getByPage_get($page, $per_page)
    {
        $page = $this->security->xss_clean($page);  # XSS filtering
        $per_page = $this->security->xss_clean($per_page);  # XSS filtering

        if(is_numeric($page) && is_numeric($per_page)){
            $total = $this->Prescripcion_model->getCount();
          //  $totalN = $this->Prescripcion_model->getCountN();
            $offset = ($page - 1) * $per_page;
            $list = $this->Prescripcion_model->getByPage($per_page, $offset);

                if(null !== $list ){
                    $this->response([
                        'status' => true,
                        'message' => $this->lang->line('item_has_found'),
                        "page" => (int)$page,
                        "per_page"=> (int)$per_page,
                        "total"=> (int)$total,
                        
                        "total_pages"=> ceil($total/$per_page),
                        'data' => $list,
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
                'message' => $this->lang->line('parameter_is_not_valid')
            ], REST_Controller::HTTP_NO_CONTENT); // NOT_FOUND (400) being the HTTP response code
        }
    }
      //actuiza al contribuyeente juridico  ..........


  private function updateRepresentante_legal($data, $solicitante){
        if(array_key_exists('datos_juridicos', $data)){
            $in_pres_legal = $data['pres_representante_legal'];

            $legal = $this->DatosJuridicos_model->getById($solicitante->id_datos_juridicos);
            $legal->razon_social = $in_pres_legal['razon_social'];
            $legal->nit = $in_pres_legal['nit'];
            $legal->telefono = $in_pres_legal['telefono'];

            return $this->DatosJuridicos_model->update($datos_juridicos);
        }
        return null;
    }
   /*
    * mi function para sacar el detalle de juridico y natural
    * necesito un id para hcer el control de las gestiones  
    */

  public function detalleJuri_get($id)
  {
      
        $this->db->select('fur, ptop.*, prl.*, p.*');
        $this->db->from('pres_prescripcion as pp');
        $this->db->join('pres_objeto_tributario as pot', 'pp.id = pot.id_prescripcion');
        $this->db->join('pres_tipo_objeto_tributario as ptop', 'pot.id_tipo_objeto_tributario = ptop.id');
        $this->db->join('pres_representante_legal as prl','pp.id = prl.id_prescripcion','left');
        $this->db->join('persona as p','pp.id = p.id_prescripcion','left');
         $this->db->where('pp.id', $id);
        $this->db->group_by('fur, ptop.id, prl.id, p.id'  );

         $query=$this->db->get();
         $data=$query->result();
  
        $this->db->select( 'pot.gestion');
        $this->db->from( 'pres_prescripcion as pp');
       // $this->db->join ('pres_representante_legal as prl',' prl.id_prescripcion = pp.id');
        $this->db->join( 'pres_objeto_tributario as pot ',' pot.id_prescripcion = pp.id');
        $this->db->where ('pp.id', $id);
      //group by pot.gestion
         $query = $this->db->get();
         $gestion = $query->result();

        $this->response([
                        'status' => true,
                        'message' => $this->lang->line('successfully getting'),
                       'juridico' => $data,
                       'gestiones'=>$gestion
                    ], REST_Controller::HTTP_OK);
  }
//esto es para saca el prescripcion con token 
  // igual define si es natural!!!!

 //public function getPrescripcionById_get($id, $ci){ "edit de contribuyente"
  public function getPrescripcionById_get($token){
   
    if($token!= null)
    {
       $this->db->select(' pp.fur,pp.token, ptot.name, p.*, pot.*, prl.razon_social, prl.num_docum_repre, prl.id_tipo_documento, prl.correo_electronico, prl.telefono, d.zona, d.direccion');
       $this->db->from('pres_prescripcion as pp') ;
       $this->db->join('pres_objeto_tributario as pot', 'pp.id = pot.id_prescripcion');
       $this->db->join('pres_tipo_objeto_tributario as ptot', 'ptot.id = pot.id_tipo_objeto_tributario');
       $this->db->join ('pres_representante_legal as prl',' prl.id_prescripcion = pp.id', 'left');
       $this->db->join(' persona as p', 'p.id_prescripcion = pot.id_prescripcion', 'left');
       $this->db->join(' domicilio as d', 'p.id = d.id_persona', 'left');
       $this->db->Where('pp.token', $token);  
       $this->db->group_by(array("pp.fur", " pp.token", "ptot.name","prl.razon_social", "prl.num_docum_repre", "prl.id_tipo_documento", "prl.telefono","prl.correo_electronico" ,"p.id", "pot.id", "d.zona", "d.direccion")); 

        $query=$this->db->get();
        $persona=$query->row();

        //print_r($this->db->last_query());

        $this->db->select( 'pot.gestion');
        $this->db->from( 'pres_prescripcion as pp');
        //$this->db->join ('pres_representante_legal as prl',' prl.id_prescripcion = pp.id');
        $this->db->join( 'pres_objeto_tributario as pot ',' pot.id_prescripcion = pp.id');
        $this->db->where ('pp.token', $token);      
        $this->db->group_by(array("pot.gestion"  ));
     
         $query = $this->db->get();
         $gestion = $query->result();
           
           $this->response([
                   'status' => true,
                   'message' => $this->lang->line('successfully getting'),
                   $this->lang->line('persona') => $persona,
                   $this->lang->line('gestion') => $gestion            
                ], REST_Controller::HTTP_OK);
    
    }
  }

   // estdo si lleno pero tenog aun estado .....solo un paso ....

   public function estadoPrescripcion_get($token)
   {
        $token_pres = $this->security->xss_clean($this->input->get()); 
        $token_edi = $this->security->xss_clean($token); 
       //print_r($token_edi);
        $prescripcion = $this->Prescripcion_model->getByToken($token_edi);
      //  print_r($prescripcion);
        
        // print_r($persona);
       if($prescripcion !== null){
        
            $persona = $this->Persona_model->getById($prescripcion->id);
            $domicilio = $this->Domicilio_model->getById($persona->id);
          
           // print_r($domicilio);
          if($persona !== null ){
             //print_r($persona);
           // var_dump($persona);
             $is_complete_data_contribuyente =  $this->Prescripcion_model->isDataContribuyenteComplete($persona->id);
             $is_complete_data_domicilio = $this->Prescripcion->model->isDataDomicilio($persona->id);
              $is_data_objetoTributario =$this->Prescripcion_model->isDataObjetoTributario($persona->id);
                var_dump($is_complete_data_domicilio);
            //print_r($is_data_domicilio);
            //print_r('....');
             $this->response([
                        'status' => true,
                        'message' => $this->lang->line('item_has_found'),
                        $this->lang->line('is_complete_data_contribuyente') => $is_complete_data_contribuyente,//is_data_domicilio
                        $this->lang->line('is_complete_data_domicilio') => $is_complete_data_domicilio,
                         $this->lang->line('is_data_objetoTributario') => $is_data_objetoTributario,
                       // $this->lang->line('is_complete_data_domicilio') => $is_complete_data_domicilio,
                        //$this->lang->line('is_complete_data_domicilio_actividad_economica') => $is_complete_data_domicilio_actividad,
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
             $this->set_response($this->lang->line('server_successfully_create_new_resource'), REST_Controller::HTTP_RESET_CONTENT);
       
       }

      

 public function getPrescripcionByNit_get($id, $nit){
    
        if($nit != null){
         $this->db->select(' pp.fur, ptot.name, prl.*, pot.numero');
         $this->db->from('pres_prescripcion as pp') ;
         $this->db->join('pres_objeto_tributario as pot', 'pp.id = pot.id_prescripcion');
         $this->db->join('pres_tipo_objeto_tributario as ptot', 'ptot.id = pot.id_tipo_objeto_tributario');
         $this->db->join(' pres_representante_legal as prl', 'prl.id_prescripcion = pot.id_prescripcion');
         $this->db->Where('prl.id_prescripcion', $id); 

         $this->db->group_by(array(" pp.fur", "ptot.name", "prl.id", "pot.numero")); 
         //$this->db->where('prl.nit',$ci);
         $query=$this->db->get();
         $juri=$query->result();
        
        $this->db->select( 'pot.gestion');
        $this->db->from( 'pres_prescripcion as pp');
        $this->db->join ('pres_representante_legal as prl',' prl.id_prescripcion = pp.id');
        $this->db->join( 'pres_objeto_tributario as pot ',' pot.id_prescripcion = prl.id_prescripcion');
        $this->db->where ('pp.id', $id);
      //group by pot.gestion
         $query = $this->db->get();
         $gestion = $query->result();

         $this->db->select('p.*, d.direccion, d.zona, d.celular, d.telefono');
         $this->db->from('pres_prescripcion as pp') ;
         $this->db->join('persona as p ', 'pp.id = p.id_prescripcion');
         $this->db->join('domicilio as d', 'd.id_persona = p.id');
         $this->db->where('pp.id', $id);
         $this->db->group_by(array("p.id","d.direccion","d.zona","d.celular", "d.telefono")); 
         $query = $this->db->get();
         $repre_legal = $query->result();

         // print_r($juri);
          $this->response([
                   'status' => true,
                   'message' => $this->lang->line('successfully getting'),
                     'juri' => $juri,
                     'gestion' => $gestion,
                     'repre_legal'=> $repre_legal
                ], REST_Controller::HTTP_OK);
       
    }

  } 


}




