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
class Persona_model extends MY_model {

    private $TABLE = "persona";

    public function __construct()
    {
        parent::__construct();
        $this->table_name = $this->TABLE;
    }


    public function getByIdPrescripcion($id_p)
    {
         $item = $this->db->select($this->table_name.'.*')
                    ->from($this->table_name)
                   // ->join('persona p', $this->TABLE.'.id = p.id_prescripcion')
                    ->where('id_prescripcion', $id_p)
                    ->get()
                    ->row();
        return $item;
       
    }
}