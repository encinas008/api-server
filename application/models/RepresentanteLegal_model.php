<?php
require_once APPPATH . 'core/MY_model.php';

/**
 * This is a model for Usuario, this model extend My_model
 *
 * @package         restServerAtm
 * @subpackage      restServerAtm
 * @category        Model
 * @author          Ronald Acha Ramos
 * @license         MIT 
 */
class RepresentanteLegal_model extends MY_model {

    private $TABLE = "representante_legal";

    public function __construct()
    {
        parent::__construct();
        $this->table_name = $this->TABLE;
    }

    /**
    * find representante legal by  id_solicitante
    * @param: array values
    */
    public function getByIdSolicitud($id_solicitante){
        $item = $this->db->where('id_solicitante', $id_solicitante)
                         ->from($this->table_name)
                         ->get()
                         ->row();
        return $item;
    }
}