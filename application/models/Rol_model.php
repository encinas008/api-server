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
class Rol_model extends MY_model {

    private $TABLE = "rol";

    public function __construct()
    {
        parent::__construct();
        $this->table_name = $this->TABLE;
    }

    /**
     * Get Rol by name
     * @param: 
     */
    public function getRolFuncionario(){
        $item = $this->db->where('name', 'Funcionario')
                         ->from($this->table_name)
                         ->get()
                         ->row();
        return $item;
    }

    /**
     * Get Rol by name
     * @param: 
     */
    public function getRolContribuyente(){
        $item = $this->db->where('name', 'Contribuyente')
                         ->from($this->table_name)
                         ->get()
                         ->row();
        return $item;
    }

    /**
     * Get Rol by name
     * @param: 
     */
    public function getRolAdmin(){
        $item = $this->db->where('name', 'Admin')
                         ->from($this->table_name)
                         ->get()
                         ->row();
        return $item;
    }

    /*
    public function getRolByIdUser($id_user){
        $item = $this->db$->select(' ')
                         ->where('u.id', $id_user)
                         ->from($this->table_name)
                         ->join('rol_usuario ru', $this->TABLE.'.id = ru.id_rol')
                         ->join('usuario u', 'ru.id_usuario = u.id')
                         ->get()
                         ->row();
        return $item;
    }*/
}