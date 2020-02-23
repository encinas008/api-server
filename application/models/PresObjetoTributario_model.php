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
class PresObjetoTributario_model extends MY_model {

    private $TABLE = "pres_objeto_tributario";

    public function __construct()
    {
        parent::__construct();
        $this->table_name = $this->TABLE;
    }
     public function getByIdObjetoTributario($id_pres){
        $item = $this->db->select($this->table_name.'.*')
                    ->from($this->table_name)
                    ->join('pres_tipo_objeto_tributario ptot', $this->TABLE.'.id_tipo_objeto_tributario = ptot.id')
                    ->where('id_prescripcion', $id_pres)
                    ->get()
                    ->result();
        return $item;
       
    }

/*
    public function getByIdObjetoTributarioGestion($id_pres){
        $item = $this->db->select($this->table_name.'.gestion')
                    ->from($this->table_name)
                    ->join('pres_tipo_objeto_tributario ptot', $this->TABLE.'.id_tipo_objeto_tributario = ptot.id')
                    ->where('id_prescripcion', $id_pres)
                    ->get()
                    ->result();
        return $item;
       
    }
    */
}