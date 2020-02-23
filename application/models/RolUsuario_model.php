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
class RolUsuario_model extends MY_model {

    private $TABLE = "rol_usuario";

    public function __construct()
    {
        parent::__construct();
        $this->table_name = $this->TABLE;
    }
}