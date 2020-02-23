<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class MY_Controller extends REST_Controller
{
    private $param;
    protected $baseurl;
    //protected $estados = array(0 => array('key' => 'AC', 'value' => 'Activo'), 1 => array('key' => 'IN', 'value' => 'Inactivo'));

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        !$this->load->library('form_validation') ? $this->load->library('form_validation') : false;

        //$baseurl = base_url();
    }

    private function loadDataSession(){
        if(!$this->session->has_userdata('username')  );
        {
            !$this->load->model('Rol_model') ? $this->load->model('Rol_model') : false;
            !$this->load->model('Usuario_model') ? $this->load->model('Usuario_model') : false;
        }
    }
}