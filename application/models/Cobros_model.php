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
class Cobros_model {

    //private $TABLE = "domicilio";

    public function __construct()
    {
        //parent::__construct();
        //$this->table_name = $this->TABLE;
    }

    /**
     * verifica y devuelve los datos relacionados con el fur
     */
    public function getFurValidate($fur){

        $curl = curl_init();
        $url = "http://192.168.104.117/cb-dev/web/index.php?r=cobros-varios/validar-fur&fur=".$fur;
        
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        
        $result = curl_exec($curl);
        curl_close($curl);

        $jsonFur = json_decode($result);
        return $jsonFur;
    }


/* el servicio post Vehiculo
 * numero = licencia, placa, codigo_licencia, etc
 * key = publicidad. placa, etc..
 */


  public function patentePublicidad($codigo_licencia){

       $curl = curl_init();
        $url = "http://192.168.104.117/servicio/consulta-servicios/webservice.php"; //develope
        
        curl_setopt($curl, CURLOPT_POST, 1); //en caso sea post
        //curl_setopt($curl, CURLOPT_POSTFIELDS, $data); //asumiendo que $data: array("parametro" => "valor") submit:publicidad, codigo_licencia:01G50110000008
        curl_setopt($curl, CURLOPT_POSTFIELDS, "submit=publicidad&codigo_licencia=".$codigo_licencia);
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        
        $result = curl_exec($curl);
        curl_close($curl);

        $jsonFur = json_decode($result);
        //echo "******************************";
        print_r($jsonFur);
        exit;
        /*if(!is_null($jsonFur) && $jsonFur.status){

        }*/
        if($jsonFur->status)
            return $jsonFur->data;
    }
     // Actividad Economica 

    public function patenteActividadEconomica($numero_actividad){


       $curl = curl_init();
        $url = "http://192.168.104.117/servicio/consulta-servicios/webservice.php"; //develope
        
        curl_setopt($curl, CURLOPT_POST, 1); //en caso sea post
        //curl_setopt($curl, CURLOPT_POSTFIELDS, $data); //asumiendo que $data: array("parametro" => "valor") submit:licencia numero_actividad:15598
        curl_setopt($curl, CURLOPT_POSTFIELDS, "submit=licencia&numero_actividad=".$numero_actividad);
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        
        $result = curl_exec($curl);
        curl_close($curl);

        $jsonActividad = json_decode($result);
      //  print_r($jsonActividad);
        return $jsonActividad->resumen;
    }

     //sitios municipales

     public function patenteSitiosMunicipales($codigo_licencia){

       $curl = curl_init();
        $url = "http://192.168.104.117/servicio/consulta-servicios/webservice.php"; //develope
        
        curl_setopt($curl, CURLOPT_POST, 1); //en caso sea post
        //curl_setopt($curl, CURLOPT_POSTFIELDS, $data); //asumiendo que $data: array("parametro" => "valor")submit:sitios-municipales&codigo_licencia:123456
        curl_setopt($curl, CURLOPT_POSTFIELDS, "submit=sitios-municipales&codigo_licencia=".$codigo_licencia);
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        
        $result = curl_exec($curl);
        curl_close($curl);

        $jsonMunicipal = json_decode($result);
        //print_r($jsonMunicipal);

        return $jsonMunicipal->data;
    }


    public function vehiculo($numero){

       $curl = curl_init();
        $url = "http://192.168.104.117/servicio/consulta-servicios/webservice.php"; //develope
        
        curl_setopt($curl, CURLOPT_POST, 1); //en caso sea post
        //curl_setopt($curl, CURLOPT_POSTFIELDS, $data); //asumiendo que $data: array("parametro" => "valor") numero:4806KZD&submit:placa
        curl_setopt($curl, CURLOPT_POSTFIELDS, "submit=placa&numero=".$numero);
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        
        $result = curl_exec($curl);
        curl_close($curl);

        $jsonVehiculo = json_decode($result);
        //print_r($jsonVehiculo);

        return $jsonVehiculo->resumen;
    }


    /**
     * permite confirmar y cerrar el fur
     */
    public function confirmarFur($fur, $usuario){
        $curl = curl_init();
        $url = "http://192.168.104.117/cb-dev/web/index.php?r=cobros-varios/confirmar-fur&fur=".$fur."&usuario=".$usuario;
             
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        
        $result = curl_exec($curl);
        curl_close($curl);

        $jsonFur = json_decode($result);
        return $jsonFur;
    }
}