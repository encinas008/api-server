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
class Domicilio_model extends MY_model {

    private $TABLE = "domicilio";

    public function __construct()
    {
        parent::__construct();
        $this->table_name = $this->TABLE;
    }

    public function getByIdPersona($id_persona){
        $item = $this->db->where('id_persona', $id_persona)
                         ->from($this->table_name)
                         ->get()
                         ->row();
        return $item;
    }
}