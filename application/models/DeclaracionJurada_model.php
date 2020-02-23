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
class DeclaracionJurada_model extends MY_model {

    private $TABLE = "declaracion_jurada";

    public function __construct()
    {
        parent::__construct();
        $this->table_name = $this->TABLE;
    }

     /**
    * Search declaracion_jurada by pagination, user and type search
    * --------------------------
    * @param: token_user
    * @param: type_search   0:all,  1:en_proceso, 2:completados,  3:aprobados, 4:reaperturados, 5:cancelado/eliminado
    * --------------------------
    * @method: query
    * @author: RAR
    */
    public function getCount($token_user, $type_search){

        /*$query = self::querySearch($token_user, $type_search);
        $total_ = $query->count_all_results();
        echo $type_search."*************<br>";
        echo $this->db->last_query();
        echo "*************<br>";*/
        $total = parent::executeQuerySingle( "SELECT * FROM total_licencia_funcionamiento('".$token_user."', ".$type_search.")" );
        //echo "*************".$result;
        //echo $this->db->last_query();
        //echo "*************<br>";
        //print_r($total); 
        //echo " --- ".$type_search." ---";
        //echo $token_user." --- --";
        if(null === $total )
            return 0;
        return $total->numrows;
        //return 3;
    }

    /**
    * Search declaracion_jurada by pagination, user and type search
    * --------------------------
    * @param: limit
    * @param: offset
    * @param: token_user
    * @param: type_search   0:all,  1:en_proceso, 2:completados,  3:aprobados, 4:reaperturados, 5:cancelado/eliminado
    * --------------------------
    * @method: query
    * @author: RAR
    */
    public function getByPage($limit, $offset, $token_user, $type_search)
    {
        /*$query = self::querySearch($token_user, $type_search);
        $query->limit($limit , $offset);
        $query->order_by($this->TABLE.".id", "desc");
        $result = $query->get();
        echo $this->db->last_query();
        return $result->result();*/

        $result = parent::executeQuery( "SELECT * FROM licencia_funcionamiento_by_page(".$limit.", ".$offset.", '".$token_user."', ".$type_search.")" );

        //echo $this->db->last_query();
        return $result;
    }

    private function querySearch($token_user, $type_search){

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

        $search = $this->db->select('declaracion_jurada.*, ac.rotulo_comercial as actividad_economica, 
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

    /**
    * Search declaracion_jurada by text and type filter
    * --------------------------
    * @param: text
    * @param: type   //numero NUMERO, FUR
    * --------------------------
    * @method: query
    * @author: RAR
    */
    public function search($text, $type, $token_user){
        //echo "texto ".$text."  -- ".$type;
        /*
        $search = $this->db->select('declaracion_jurada.*, ac.rotulo_comercial as actividad_economica, 
                    est.name as estado, est.code as code_estado, tae.name as tipo_actividad_economica, sol.contribuyente, \'{CREATE,DELETE,UPDATE}\' as permissions ')
                    ->from($this->TABLE)
                    ->join('solicitante sol', $this->TABLE.'.id_solicitante = sol.id')
                    ->join('actividad_economica ac', $this->TABLE.'.id_actividad_economica = ac.id')
                    ->join('tipo_actividad_economica tae', 'ac.id_tipo_actividad = tae.id')
                    ->join('estado est', $this->TABLE.'.id_estado = est.id');
        if($type === 'NUMERO'){
            $search->where('declaracion_jurada.numero', $text);
        }

        if($type === 'FUR'){
            $search->where('declaracion_jurada.fur', $text);
        }

        //$search->limit($limit , $offset);
        $search->order_by($this->TABLE.".id", "desc");
        $result = $search->get();

        //echo $this->db->last_query();

        echo $this->db->last_query();
        return $result->result();

        //SELECT * FROM search_licencia('a6f2000058', 'NUMERO', '7449dfad92a99108ea34d43d9431d4c6');
        $result = parent::executeQuery( "SELECT * FROM search_licencia(".$text.", , '".$token_user."', ".$type_search.")" );
        */

        if($type === 'NUMERO'){
            $result = parent::executeQuery( "SELECT * FROM search_licencia('".$text."', 'NUMERO', '".$token_user."')" );
        }

        if($type === 'FUR'){
            $result = parent::executeQuery( "SELECT * FROM search_licencia('".$text."', 'FUR', '".$token_user."')" );
        }

        //echo $this->db->last_query();
        return $result;
    }

    /**
    * find declaracion by token
    * @param: array values
    */
    public function getByToken($token){
        $item = $this->db->select($this->table_name.'.*')
                    ->where('token', $token)
                    ->from($this->table_name)
                    ->join('estado e', $this->TABLE.'.id_estado = e.id')
                    ->where('e.code <>', ESTADO_CANCELADO)
                    ->get()
                    ->row();
        return $item;
    }

    /**
    * find declaracion by fur
    * @param: array values
    */
    public function getByFur($fur){
        $item = $this->db->select($this->table_name.'.*')
                    ->where('fur', $fur)
                    ->from($this->table_name)
                    ->join('estado e', $this->TABLE.'.id_estado = e.id')
                    ->where('e.code <>', ESTADO_CANCELADO)
                    ->get()
                    ->row();
        return $item;
    }
    
    /**
     * Create a new declaracion_jurada
     * @param: array values
     */
    public function create($values = array()){

        $values['token'] = bin2hex(TOKEN);
        return parent::create( $values );
    }

    
     /**
     * Verifica si todos los datos para una licencia de actividad economica estan insertados
     * @param: id_dj  id de la declaracion jurada
     * @param: is_natural  persona natural(1) o juridica(2)
     */
    public function isComleteData($id_dj, $contribuyente){
        $search = $this->db->select('declaracion_jurada.numero ')
                    ->from($this->TABLE)
                    ->join('actividad_economica ac', $this->TABLE.'.id_actividad_economica = ac.id')
                    ->join('domicilio_actividad_economica dac', 'ac.id = dac.id_actividad_economica')
                    ->join('tipo_actividad_economica tae', 'ac.id_tipo_actividad = tae.id')

                    ->join('solicitante sol', $this->TABLE.'.id_solicitante = sol.id');

        if($contribuyente === '2'){
            $search->join('datos_juridicos datojuri', 'sol.id_datos_juridicos = datojuri.id');
        }

        $search->join('representante_legal repre_legal', 'sol.id = repre_legal.id_solicitante');

        $search->join('persona per', 'repre_legal.id_persona = per.id');
        $search->join('domicilio dom_per', 'per.id = dom_per.id_persona');

        if($contribuyente === '1'){
            $search->where('sol.contribuyente', $contribuyente);
        }else{
            $search->where('sol.contribuyente', $contribuyente);
        }

        //and "dom_per"."image" <> ''  validar ambas imagenes de la actividad economica y del contribuyente

        $search->where($this->TABLE.'.id', $id_dj);
        $result = $search->get();
        $result = $result->result();

        //echo $this->db->last_query();
        if($result !== null && sizeof($result) > 0)
            if( !is_null($result[0]->numero) && !empty($result[0]->numero))
                return true;
        else
            return false;

        return false;
    }

    /**
     * Verifica si los datos de contribuyente referente a una declaracion jurada estan completas
     * @param: id_dj  id de la declaracion jurada
     * @param: contribuyente  persona natural(1) o juridica(2)
     */
    public function isDataContribuyenteComplete($id_dj, $contribuyente){

        $search = $this->db->select('declaracion_jurada.numero ')
                    ->from($this->TABLE)
                    ->join('solicitante sol', $this->TABLE.'.id_solicitante = sol.id');

        if($contribuyente === '2'){
            $search->join('datos_juridicos datojuri', 'sol.id_datos_juridicos = datojuri.id');
        }

        $search->join('representante_legal repre_legal', 'sol.id = repre_legal.id_solicitante');
        $search->join('persona per', 'repre_legal.id_persona = per.id');

        if($contribuyente === '1'){
            $search->where('sol.contribuyente', $contribuyente);
        }else{
            $search->where('sol.contribuyente', $contribuyente);
        }

        $search->where($this->TABLE.'.id', $id_dj);
        $result = $search->get();
        $result = $result->result();

        //echo $this->db->last_query();
        if($result !== null && sizeof($result) > 0)
            if( !is_null($result[0]->numero) && !empty($result[0]->numero))
                return true;
        else
            return false;

        return false;
    }

    /**
     * Verifica si los datos de domicilio del contribuyente referente a una declaracion jurada estan completas
     * @param: id_dj  id de la declaracion jurada
     */
    public function isDataDomicilioComplete($id_dj){

        $search = $this->db->select('declaracion_jurada.numero ')
                    ->from($this->TABLE)
                    ->join('solicitante sol', $this->TABLE.'.id_solicitante = sol.id');


        $search->join('representante_legal repre_legal', 'sol.id = repre_legal.id_solicitante');
        $search->join('persona per', 'repre_legal.id_persona = per.id');
        $search->join('domicilio dom_per', 'per.id = dom_per.id_persona');

        $search->where($this->TABLE.'.id', $id_dj);
        $result = $search->get();
        $result = $result->result();

        //echo $this->db->last_query();
        if($result !== null && sizeof($result) > 0)
            if( !is_null($result[0]->numero) && !empty($result[0]->numero))
                return true;
        else
            return false;

        return false;
    }

    /**
     * Verifica los datos de domicilio de la actividad económica del contribuyente referente a una declaracion jurada estan completas
     * @param: id_dj  id de la declaracion jurada
     */
    public function isDataDomicilioActividadEconomicaComplete($id_dj){

        $search = $this->db->select('declaracion_jurada.numero ')
        ->from($this->TABLE)
        ->join('actividad_economica ac', $this->TABLE.'.id_actividad_economica = ac.id')
        ->join('domicilio_actividad_economica dac', 'ac.id = dac.id_actividad_economica')
        ->join('tipo_actividad_economica tae', 'ac.id_tipo_actividad = tae.id');

        $search->where($this->TABLE.'.id', $id_dj);
        $result = $search->get();
        $result = $result->result();

        //echo $this->db->last_query();
        if($result !== null && sizeof($result) > 0)
            if( !is_null($result[0]->numero) && !empty($result[0]->numero))
                return true;
        else
            return false;

        return false;
    }
}