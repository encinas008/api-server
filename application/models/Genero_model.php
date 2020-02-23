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
//class Genero_model extends MY_model {
class Genero_model  {

    private $TABLE = "genero";

    public function __construct()
    {
        //parent::__construct();
        $this->table_name = $this->TABLE;
    }

    public function getAll(){

        $obj = new stdClass();
        $obj->code = "F";
        $obj->name = "Femenino";
        $obj->id = 1;

        $result[0] = $obj;

        $obj1 = new stdClass();
        $obj1->code = "M";
        $obj1->name = "Masculino";
        $obj1->id = 2;

        $result[1] = $obj1;

        /*$obj2 = new stdClass();
        $obj2->code = "O";
        $obj2->name = "OTROS";
        $obj2->id = 3;

        $result[2] = $obj2;*/

        return $result;
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