<?php
require_once APPPATH . 'core/MY_model.php';

/**
 * This is a model for ChangePassword, this model extend My_model
 *
 * @package         restServerAtm
 * @subpackage      restServerAtm
 * @category        Model
 * @author          Ronald Acha Ramos
 * @license         MIT 
 */
class ChangePassword_model extends MY_model {

    private $TABLE = "change_password";

    public function __construct()
    {
        parent::__construct();
        $this->table_name = $this->TABLE;
    }


    /**
     * Create a new change password
     * @param: username or email address
     * @param: password
     */
    public function create($values = array()){

        $values['token'] = bin2hex(TOKEN);
        $values['activo'] = true; 

        $now = date(DATETIME_FORMAT);
        $new_time = date(DATETIME_FORMAT,strtotime('+24 hour',strtotime($now)));

        $values['expires_at'] = $new_time; 
        return parent::create( $values );
    }

    /**
    * find change_password by token
    * @param: array values
    */
    public function getByToken($token){
        $item = $this->db->select($this->TABLE.'.*')
                         ->from($this->table_name)
                         ->where('token', $token)
                         ->where('activo', true)
                         ->get()
                         ->row();
        return $item;
    }
}