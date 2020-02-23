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
//class EstadoCivil_model extends MY_model {
class EstadoCivil_model  {

    private $TABLE = "estado_civil";

    public function __construct()
    {
        //parent::__construct();
        $this->table_name = $this->TABLE;
    }

    public function getAll(){

        $obj = new stdClass();
        $obj->code = "S";
        $obj->name = "Soltero(a)";
        $obj->id = 1;

        $result[0] = $obj;

        $obj1 = new stdClass();
        $obj1->code = "C";
        $obj1->name = "Casado(a)";
        $obj1->id = 2;

        $result[1] = $obj1;

        $obj2 = new stdClass();
        $obj2->code = "D";
        $obj2->name = "Divorciado(a)";
        $obj2->id = 3;

        $result[2] = $obj2;

        $obj3 = new stdClass();
        $obj3->code = "V";
        $obj3->name = "Viudo(a)";
        $obj3->id = 4;

        $result[3] = $obj3;

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