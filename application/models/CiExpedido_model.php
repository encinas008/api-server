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
class CiExpedido_model  {

    private $TABLE = "ci_expedido";

    public function __construct()
    {
        $this->table_name = $this->TABLE;
    }

    public function getAll(){

        $obj = new stdClass();
        $obj->code = "CB";
        $obj->name = "Cochabamba";
        $obj->id = 1;

        $result[0] = $obj;

        $obj1 = new stdClass();
        $obj1->code = "LP";
        $obj1->name = "La Paz";
        $obj1->id = 2;

        $result[1] = $obj1;

        $obj2 = new stdClass();
        $obj2->code = "SC";
        $obj2->name = "Santa Cruz";
        $obj2->id = 3;

        $result[2] = $obj2;

        $obj3 = new stdClass();
        $obj3->code = "OR";
        $obj3->name = "Oruro";
        $obj3->id = 4;

        $result[3] = $obj3;

        $obj3 = new stdClass();
        $obj3->code = "CH";
        $obj3->name = "Chuquisaca";
        $obj3->id = 5;

        $result[4] = $obj3;

        $obj3 = new stdClass();
        $obj3->code = "PO";
        $obj3->name = "Potosi";
        $obj3->id = 6;

        $result[5] = $obj3;

        $obj3 = new stdClass();
        $obj3->code = "BN";
        $obj3->name = "Beni";
        $obj3->id = 7;

        $result[6] = $obj3;


        $obj3 = new stdClass();
        $obj3->code = "PD";
        $obj3->name = "Pando";
        $obj3->id = 8;

        $result[7] = $obj3;

        $obj3 = new stdClass();
        $obj3->code = "TJ";
        $obj3->name = "Tarija";
        $obj3->id = 9;

        $result[8] = $obj3;

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