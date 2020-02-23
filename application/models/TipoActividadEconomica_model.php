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
class TipoActividadEconomica_model extends MY_model {

    private $TABLE = "tipo_actividad_economica";

    public function __construct()
    {
        parent::__construct();
        $this->table_name = $this->TABLE;
    }

    /**
     * Search Tipo Actividad Economica
     * @param: name activity
     */
    public function getSearchByName($name, $derecho_admision){

        if((int)$derecho_admision === (int)TEMPORAL) 
            $query = "SELECT ae.name as label, ae.id as value, ae.ciiu FROM ".$this->TABLE." ae WHERE LOWER(ae.name) ilike('%".$name."%') and ae.temporal = true;";
        else 
            $query = "SELECT ae.name as label, ae.id as value, ae.ciiu FROM ".$this->TABLE." ae WHERE LOWER(ae.name) ilike('%".$name."%') and ae.temporal = false;";
        return $this->executeQuery($query);
    }
}