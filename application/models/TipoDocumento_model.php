<?php
require_once APPPATH . 'core/MY_model.php';
/**
 * This is a model for tipo_documento, this model extend My_model
 *
 * @package         restServerAtm
 * @subpackage      restServerAtm
 * @category        Models
 * @author          Ronald Acha Ramos
 * @license         MIT 
 */
class TipoDocumento_model extends MY_model {

    private $TABLE = "tipo_documento";

    public function __construct()
    {
        parent::__construct();
        $this->table_name = $this->TABLE;
    }

    public function getAllPrescripcion()
    {
    	$this->db->select('id, code, name');
    	$this->db->from( $this->table_name);
    	$this->db->where('prescripcion', true);
    	$query = $this->db->get();
    	//rint_r($query);
        $data = $query->result();

        return $data;
    }
}