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
class ConfirmarUsuario_model extends MY_model {

    private $TABLE = "confirmar_usuario";

    public function __construct()
    {
        parent::__construct();
        $this->table_name = $this->TABLE;
    }

    /**
     * Create a new confirm_user
     * @param: username or email address
     * @param: password
     */
    public function create($values = array()){

        $values['token'] = bin2hex(TOKEN);
        $values['activo'] = true; 

        $now = date(DATETIME_FORMAT);
        $new_time = date(DATETIME_FORMAT,strtotime('+72 hour',strtotime($now)));

        $values['expires_at'] = $new_time; 
        return parent::create( $values );
    }

    /**
     * Confirmar usuario by token
     * ----------------------------------
     * @param: token
     */
    public function getByToken($token){
        $item = $this->db->where('token', $token)
                         ->from($this->table_name)
                         ->get()
                         ->row();
        return $item;
    }
}