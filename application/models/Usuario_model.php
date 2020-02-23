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
class Usuario_model extends MY_model {

    private $TABLE = "usuario";

    public function __construct()
    {
        parent::__construct();
        $this->table_name = $this->TABLE;

        $this->form_validation->set_error_delimiters('', '');

        $this->form_validation->set_message('required', 'El campo %s es obligatorio');
        $this->form_validation->set_message('integer', 'El campo %s debe poseer solo números enteros');
        $this->form_validation->set_message('is_unique', 'El campo %s ya esta registrado');
        $this->form_validation->set_message('max_length', 'El Campo %s debe tener un Máximo de %d Caracteres');
    }

    /**
     * Create a new user
     * @param: username or email address
     * @param: password
     */
    public function create($values = array()){

        $values['token'] = bin2hex(TOKEN);
        $values['password'] = md5($values['password']); //encriptamos el password

        return parent::create( $values );
    }

    /**
     * Update a user
     * @param: username or email address
     * @param: password
     */
    public function update($usuario, $update_pass = true){
        if($update_pass === true)
            $usuario->password = md5($usuario->password); //encriptamos el password
        return parent::update( $usuario );
    }

    /**
     * User Login
     * ----------------------------------
     * @param: username or email address
     * @param: password
     */
    public function login($username, $password)
    {
        $item = $this->db->where('username', $username)
                        ->where('password', md5($password) )
                        ->from($this->table_name)
                        ->join('rol_usuario ru', $this->TABLE.'.id = ru.id_usuario' )
                        ->join('rol r', 'ru.id_rol = r.id' )
                        ->get()
                        ->row();
        //->join('solicitante sol', $this->TABLE.'.id_solicitante = sol.id')
        return $item;
    }

    /**
     * get user by token_user and token_confirm
     * ----------------------------------
     * @param: token_user  token of user
     * @param: token_confirm  token of confirm user
     */
    public function getByTokenAndTokenConfirm($token_usr, $token_confirm)
    {
        $item = $this->db->select($this->TABLE.'.*')
                        ->where($this->TABLE.'.token', $token_usr)
                        ->from($this->table_name)
                        ->join('rol_usuario ru', $this->TABLE.'.id = ru.id_usuario' )
                        ->join('rol r', 'ru.id_rol = r.id' )
                        ->join('confirmar_usuario cu', $this->TABLE.'.id = cu.id_usuario' )
                        ->join('estado e', $this->TABLE.'.id_estado = e.id' )
                        ->where('e.code', 'PENDIENTE_APROBACION')
                        ->where('cu.token', $token_confirm)
                        ->where('cu.activo', true)
                        ->get()
                        ->row();
        //print_r($this->db->last_query());
        return $item;
    }

    /**
     * User Login
     * ----------------------------------
     * @param: username
     */
    public function getByUsername($username){
        $item = $this->db->select($this->TABLE.'.*')
                         ->from($this->TABLE)
                         ->join('estado e', $this->TABLE.'.id_estado = e.id' )
                         ->where('username', $username)
                         ->where('e.code', ESTADO_ACTIVO)
                         ->get()
                         ->row();
        return $item;
    }

    /**
     * get User by JWT session
     * ----------------------------------
     * @param: jwt
     */
    public function getByTokenJWT($jwt){

        $item = $this->db->where('token', $jwt->token)
                         ->where('username', $jwt->username)
                         ->from($this->table_name)
                         ->get()
                         ->row();
        return $item;
    }

    

    public function rules($login = false)
    {
        $rules_username = 'trim|required|valid_email';
        if($login === FALSE){
            $rules_username = $rules_username.'|callback_username_unique';
        }

        return [
            ['field' => 'usuario[username]',
            'label' => 'Nombre de Usuario y/o Correo Electronico',
            'rules' => $rules_username],

            ['field' => 'usuario[password]',
            'label' => 'Contraseña',
            'rules' => 'trim|required'],
        ];
    }

     /**
    * find usuario by token
    * @param: array values
    */
    public function getByToken($token){
        $item = $this->db->select($this->TABLE.'.*')
                         //->where('token', $token)
                         ->from($this->table_name)
                         ->join('estado e', $this->TABLE.'.id_estado = e.id' )
                         ->where('token', $token)
                         ->where('e.code', ESTADO_ACTIVO)
                         ->get()
                         ->row();
        return $item;
    }

    /**
     * get user by token_user and token_change_password
     * ----------------------------------
     * @param: token_user  token of user
     * @param: take_change_password token of change password
     */
    public function getByTokenAndTokenChangePassword($token_usr, $token_change_password)
    {
        $item = $this->db->select($this->TABLE.'.*')
                        ->where($this->TABLE.'.token', $token_usr)
                        ->from($this->table_name)
                        ->join('rol_usuario ru', $this->TABLE.'.id = ru.id_usuario' )
                        ->join('rol r', 'ru.id_rol = r.id' )
                        ->join('change_password cp', $this->TABLE.'.id = cp.id_usuario' )
                        ->join('estado e', $this->TABLE.'.id_estado = e.id' )
                        ->where('e.code', 'ACTIVO')
                        ->where('cp.token', $token_change_password)
                        ->where('cp.activo', true)
                        ->get()
                        ->row();
        //print_r($this->db->last_query());
        return $item;
    }
}