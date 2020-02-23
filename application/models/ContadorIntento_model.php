<?php
require_once APPPATH . 'core/MY_model.php';

/**
 * This is a model for Usuario, this model extend My_model
 * 
 * licencias de actividades economicas, para este caso tiene las siguientes funcionalidades
 * 1.- Si esta activo el atributo declaracion_jurada y la fecha esta actual esta dentro del rango (24 horas)
 * 2.- si el estado el false, ha sido deshabilitado por el usuario
 * 3.- si la fecha ha expirado se ha completado un dia de espera
 *
 * @package         restServerAtm
 * @subpackage      restServerAtm
 * @category        Model
 * @author          Ronald Acha Ramos
 * @license         MIT 
 */
class ContadorIntento_model extends MY_model {

    private $TABLE = "contador_intento";

    public function __construct()
    {
        parent::__construct();
        $this->table_name = $this->TABLE;
    }

    /**
     * Create a new contadorIntento
     * @param: username or email address
     * @param: password
     */
    public function create($values = array()){

        $now = date(DATETIME_FORMAT);
        $new_time = date(DATETIME_FORMAT,strtotime('+'.TIME_EXPIRATION_INTENTO.' hour',strtotime($now)));
        $values['hour_started'] = $new_time;
        return parent::create( $values );
    }

    /**
     * ContadorIntento by token
     * ----------------------------------
     * @param: token
     */
    public function getByIdUsuario($id_usuario){
        $query = "SELECT ci.*
                FROM ".$this->TABLE." ci
                JOIN usuario u ON ci.id_usuario = u.id
                WHERE ci.declaracion_jurada = TRUE
                AND ci.hour_started IS NOT NULL
                AND u.id = ".$id_usuario."
                AND TIMESTAMP WITHOUT TIME ZONE 'now' <= ci.hour_started 
                AND ci.hour_started >= (select now() + interval '1' HOUR )";
        $result = parent::executeQuerySingle($query);
        return $result;
    }

    /**
     * permit validate intent in range hour
     * ----------------------------------
     * @param: token
     */
    public function validar($id_contador){
        $query = "SELECT ci.count as total
        FROM contador_intento ci
        JOIN usuario u ON ci.id_usuario = u.id
        JOIN rol_usuario ru ON u.id = ru.id_usuario
        JOIN rol r ON ru.id_rol = r.id
        JOIN estado est ON u.id_estado = est.id
        WHERE est.code = 'ACTIVO'
        AND ci.declaracion_jurada = TRUE
        AND ci.hour_started IS NOT NULL
        AND ci.id = ".$id_contador."
        AND TIMESTAMP WITHOUT TIME ZONE 'now' <= ci.hour_started 
        AND ci.hour_started >= (select now() + interval '1' HOUR )";
        $total = parent::executeQuerySingle($query);

        //echo $this->db->last_query();

        if(null === $total )
            return false;
        else{
            if($total->total >= MAXIMO_INTENTOS_LICENCIA_ACTIVIDAD_ECONOMICA){
                return false;
            }
        }

        return true;
    }

    public function getTotalLicenciaActividadEconomica($id){
        //$query = self::querySearch($token_user, $type_search);
        $query = "SELECT contador_intento.count as total
        FROM contador_intento
        JOIN usuario u ON contador_intento.id_usuario = u.id
        JOIN rol_usuario ru ON u.id = ru.id_usuario
        JOIN rol r ON ru.id_rol = r.id
        JOIN estado est ON u.id_estado = est.id
        WHERE est.code = 'ACTIVO'
        AND contador_intento.declaracion_jurada = TRUE
        AND contador_intento.hour_started IS NOT NULL
        AND TIMESTAMP WITHOUT TIME ZONE 'now' >= contador_intento.hour_started 
        AND contador_intento.hour_started <= (select now() + interval '1' HOUR )";
        $total = parent::executeQuerySingle($query);

        //$total = parent::executeQuerySingle( "SELECT * FROM total_licencia_funcionamiento('".$token_user."', ".$type_search.")" );

        
        //$total_ = $search->row();

        //echo $this->db->last_query();

        if(null === $total )
            return 0;

        return $total->total;
        //return $total;
    }
}