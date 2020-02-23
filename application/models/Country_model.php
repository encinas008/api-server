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
class Country_model extends MY_model {

    private $TABLE = "country";

    public function __construct()
    {
        parent::__construct();
        $this->table_name = $this->TABLE;
    }

    /**
     * Search Tipo Actividad Economica
     * @param: name activity
     */
    public function getSearchByName($name){
        $query = "SELECT c.name as label, c.id as value, c.code_phone FROM ".$this->TABLE." c WHERE c.name ilike '%".$name."%'";
        return $this->executeQuery($query);
    }
}