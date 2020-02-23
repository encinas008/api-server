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
class DatosJuridicos_model extends MY_model {

    private $TABLE = "datos_juridicos";

    public function __construct()
    {
        parent::__construct();
        $this->table_name = $this->TABLE;
    }
}