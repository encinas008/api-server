<?php
require_once APPPATH . 'core/MY_model.php';
/**
 * This is a model for Usuario, this model extend My_model
 *
 * @package         restServerAtm
 * @subpackage      restServerAtm
 * @category        Controller
 * @author          Ronald Acha Ramos
 * @license         MIT 
 */
class Prescripcion_model extends MY_model {

    private $TABLE = "pres_prescripcion";

    public function __construct()
    {
        parent::__construct();
        $this->table_name = $this->TABLE;
    }

    /**
     * Create a new declaracion_jurada
     * @param: array values
     */
    public function create($values = array()){

        $values['token'] = bin2hex(TOKEN);
        return parent::create( $values );
    }


     public function getByIdPres($id){
        $item = $this->db->where('id', $id)
                         ->from($this->table_name)
                         ->get()
                         ->row();
        return $item;
    }

    public function isDataContribuyenteComplete($id_c) 
    {
       $search = $this->db->select('pres_prescripcion.fur')
                           ->from($this->TABLE)
                           ->join('persona per', $this->TABLE.'.id = per.id_prescripcion');

        $search->join('pres_representante_legal repre_legal', 'pres_prescripcion.id = repre_legal.id_prescripcion');
        $search->join('pres_objeto_tributario pot', 'pres_prescripcion.id = pot.id_prescripcion');
        //$search->join('domicilio dom_per', 'per.id = dom_per.id_persona');

        $search->where($this->TABLE.'.id', $id_c);
        $result = $search->get();
        $result = $result->result();
        print_r($result);
        
        return $result;

    }

    public function isDataObjetoTributario($id_c) 
    {
       $search = $this->db->select('pres_prescripcion.fur')
                           ->from($this->TABLE)
                           ->join('persona per', $this->TABLE.'.id = per.id_prescripcion');

        $search->join('pres_representante_legal repre_legal', 'pres_prescripcion.id = repre_legal.id_prescripcion');
        $search->join('pres_objeto_tributario pot', 'pres_prescripcion.id = pot.id_prescripcion');
        //$search->join('domicilio dom_per', 'per.id = dom_per.id_persona');

        $search->where($this->TABLE.'.id', $id_c);
        $result = $search->get();
        $result = $result->result();
        print_r($result);
        
        return $result;

    }


    public function isDataDomicilio($id_c) 
    {
       $search = $this->db->select('pres_prescripcion.fur')
                           ->from($this->TABLE)
                           ->join('persona per', $this->TABLE.'.id = per.id_prescripcion')
                           ->join('domicilio d', 'd.id_persona = per.id');
       // $search->join('pres_objeto_tributario pot', 'pres_prescripcion.id = pot.id_prescripcion');
        //$search->join('domicilio dom_per', 'per.id = dom_per.id_persona');
        $search->where($this->TABLE.'.id', $id_c);
        $result = $search->get();
        $result = $result->result();
        print_r($result);
       
        return $result;

}


/*
    public function getByPage($limit, $offset)
    {
        $this->db->select('prescripcion.*, ta.name as tipo_actividad, es.name as estado');
        $this->db->from($this->TABLE);
        $this->db->join('pres_prescripcion ta', $this->TABLE.'.id_persona = ta.id');
        $this->db->join('persona as es', $this->TABLE.'.id_estado = es.id');
        $this->db->limit($limit , $offset);  //limit(limit, offset)
        $this->db->order_by("id", "desc");
        $query = $this->db->get();  //$this->db->get('mytable', 10, 20);

        return $query->result();
    }
    */
    /**
     * El search prescripcion con numero objeto  
     * nombre de objeto tributario  $token_user, $type, 
     */

   public function search($text){
        
   
        if($type === 'FUR'){
            $result = parent::executeQuery( "SELECT * FROM search_prescripcion('".$text."', 'FUR')" );
        }
        //echo $this->db->last_query();
        return $result;
    }

  // $token_user sin eso por ahora

  private function querySearch( $type_search){

        $estado = null;
        $estados = array("0", "1", "2", "3", "4", "5");
        if (in_array($type_search, $estados)) {
            switch($type_search){
                case "1":
                    $estado = $this->Estado_model->getByCode('EN_PROCESO');
                    break;
                case "2":
                    $estado = $this->Estado_model->getByCode('COMPLETADO');
                    break;
                case "3":
                    $estado = $this->Estado_model->getByCode('APROBADO');
                    break;
                case "4":
                    $estado = $this->Estado_model->getByCode('"REAPERTURADO"');
                    break;
                case "4":
                    $estado = $this->Estado_model->getByCode('"ELIMINADO"');
                    break;
                default:
                    break;
            }
        }

        $search = $this->db->select('da.*, ac.rotulo_comercial as actividad_economica, 
                    est.name as estado, est.code as code_estado, tae.name as tipo_actividad_economica, sol.contribuyente ')
                    ->from($this->TABLE)
                    ->join('solicitante sol', $this->TABLE.'.id_solicitante = sol.id')
                    ->join('actividad_economica ac', $this->TABLE.'.id_actividad_economica = ac.id')
                    ->join('tipo_actividad_economica tae', 'ac.id_tipo_actividad = tae.id');

        if(null !== $estado){
            $search->join('estado est', $this->TABLE.'.id_estado = est.id');
            $search->where('est.id', $estado->id);
        }else{
            $search->join('estado est', $this->TABLE.'.id_estado = est.id');

            //and "est"."code" IN "('EN_PROCESO', 'APROBADO', 'REAPERTURADO', 'COMPLETADO', 'ACTIVO', 'CANCELADO')"
        }
        //$names = array('EN_PROCESO', 'APROBADO', 'REAPERTURADO', 'COMPLETADO', 'ACTIVO', 'CANCELADO');
        //$search->where_in('est.code', $names);
        //echo $this->db->last_query();
        return $search;
    }

     public function getByToken($token){
        $item = $this->db->select($this->table_name.'.*')
                    ->from($this->table_name)
                    ->join('persona p', $this->TABLE.'.id = p.id_prescripcion')
                    ->where('token', $token)
                    ->get()
                    ->row();
        return $item;
       
    }
    /**
    * Search prescripcion by pagination, user and type search
    * --------------------------
    * @method: query
    * @author: RAR
    */
    public function getCount(){
         
        $query = $this->db->select('fur, ptop.name, prl.razon_social')
        ->from('pres_prescripcion as pp')
        ->join('pres_objeto_tributario as pot', 'pp.id = pot.id_prescripcion')
        ->join('pres_tipo_objeto_tributario as ptop', 'pot.id_tipo_objeto_tributario = ptop.id')
        ->join('pres_representante_legal as prl','pp.id = prl.id_prescripcion')
        ->group_by('fur, ptop.name, prl.razon_social' );
         $uno= $query->count_all_results();
          //echo $query->count_all_results();
          // print_r($uno);
         $res = $this->db->select('fur, ptop.name, p.nombre')
          ->from('pres_prescripcion as pp')
          ->join('pres_objeto_tributario as pot', 'pp.id = pot.id_prescripcion')
          ->join('pres_tipo_objeto_tributario as ptop', 'pot.id_tipo_objeto_tributario = ptop.id')
          ->join('persona as p','pp.id = p.id_prescripcion')
          ->group_by('fur, ptop.name, p.nombre');
         $dos=$res->count_all_results();
         
          $total = $uno + $dos;
  //       print_r($total);
        return $total;

    }

    /**
    * Search prescripcion by pagination, user and type search
    * --------------------------
    * @param: limit
    * @param: offset
    * --------------------------
    * @method: query
    * @author: RAR
    */
    public function getByPage($limit, $offset)
    {
        $this->db->select('fur, token, ptop.name, prl.razon_social as nombre, pp.id, prl.num_docum_repre, pot.numero');
        $this->db->from('pres_prescripcion as pp');
        $this->db->join('pres_objeto_tributario as pot', 'pp.id = pot.id_prescripcion');
        $this->db->join('pres_tipo_objeto_tributario as ptop', 'pot.id_tipo_objeto_tributario = ptop.id');
        $this->db->join('pres_representante_legal as prl','pp.id = prl.id_prescripcion');
       // $this->db->join('persona as p','pp.id = p.id_prescripcion');
        $this->db->limit($limit , $offset);
        $this->db->group_by('fur, token, ptop.name, nombre, pp.id, prl.num_docum_repre, pot.numero' );
        $query = $this->db->get();
        $data1 = $query->result();
        //echo $this->db->last_query();
        $this->db->select('fur, token, ptop.name, p.nombre, pp.id, p.numero_documento, pot.numero');
        $this->db->from('pres_prescripcion as pp');
        $this->db->join('pres_objeto_tributario as pot', 'pp.id = pot.id_prescripcion');
        $this->db->join('pres_tipo_objeto_tributario as ptop', 'pot.id_tipo_objeto_tributario = ptop.id');
        $this->db->join('persona as p','pp.id = p.id_prescripcion');
        $this->db->limit($limit , $offset);
        $this->db->group_by('fur, token, ptop.name, p.nombre, pp.id, p.numero_documento, pot.numero' );
        $aux = $this->db->get();
        $data2 = $aux->result();
        $data = array_merge($data1, $data2);
        //print_r($data);
        return $data;
    }
}