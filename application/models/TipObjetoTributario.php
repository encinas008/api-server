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
class TipObjetoTributario_model extends MY_model {

    private $TABLE = "actividad_economica";

    public function __construct()
    {
        parent::__construct();
        $this->table_name = $this->TABLE;
    }

    /**
     * Search Actividad Economica
     * @param: name activity
     */
    public function getActividadByName($name){

        $query = "SELECT ae.name as name FROM pres_tipo_objeto_tributario ae;";
        return $this->executeQuery($query);
    }
}