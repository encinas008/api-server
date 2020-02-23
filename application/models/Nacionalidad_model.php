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
//class TipoDocumento_model extends MY_model {
class Nacionalidad_model  {

    private $TABLE = "nacionalidad";

    public function __construct()
    {
        //parent::__construct();
        $this->table_name = $this->TABLE;
    }

    public function getAll(){

        $obj = new stdClass();
        $obj->code = "B";
        $obj->name = "Boliviana";
        $obj->id = 1;

        $result[0] = $obj;

        $obj1 = new stdClass();
        $obj1->code = "E";
        $obj1->name = "Extranjero";
        $obj1->id = 2;

        $result[1] = $obj1;

        return $result;
    }

    /**
     * 
     */
    public function getByCode($code){

        $list = self::getAll();
        $item = null;

        foreach ($list as $key => $value) {
            if($value->code === $code){
                $item = $value;
                break;
            }
        }
        return $item;
    }

    /**
     * 
     */
    public function getById($id){

        $list = self::getAll();
        $item = null;

        foreach ($list as $key => $value) {
            if((int)$value->id === (int)$id){
                $item = $value;
                break;
            }
        }
        return $item;
    }
}