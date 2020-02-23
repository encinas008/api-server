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
class PresRepresentanteLegal_model extends MY_model {

    private $TABLE = "pres_representante_legal";

    public function __construct()
    {
        parent::__construct();
        $this->table_name = $this->TABLE;
    }
    public function getByIdPrescripcion($id_pres){
        $item = $this->db->where('id_prescripcion', $id_pres)
                         ->from($this->table_name)
                         ->get()
                         ->row();
        return $item;
    }
}