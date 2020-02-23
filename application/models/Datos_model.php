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
class Datos_model extends MY_model {

    private $TABLE = "datos";

    public function __construct()
    {
        parent::__construct();
        $this->table_name = $this->TABLE;
    }

    /**
     * Datos get by IdUser
     * ----------------------------------
     * @param: id_user
     */
    public function getByIdUser($id_user){
        $item = $this->db->where('id_usuario', $id_user)
                         ->from($this->table_name)
                         ->get()
                         ->row();
        return $item;
    }
}