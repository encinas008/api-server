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
class Estado_model extends MY_model {

    private $TABLE = "estado";

    public function __construct()
    {
        parent::__construct();
        $this->table_name = $this->TABLE;
    }

    /**
     * Get Estado by code
     * @param: code
     */
    public function getByCode($code){
        $item = $this->db->where('code', $code)
                         ->from($this->table_name)
                         ->get()
                         ->row();
        return $item;
    }
}