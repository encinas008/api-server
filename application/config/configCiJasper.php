<?php
defined('BASEPATH') OR exit('No direct script access allowed');


$config['dbConnection'] = array('driver' => 'postgres', 
				                'username' => 'postgres',
				                'password' => 'victor123',
				                'host' => 'localhost',
				                'database' => 'atm1',
				                'port' => '5432');
$config['dirJrxml'] = FCPATH."jasper_jrxml/";
$config['dirReport'] = FCPATH."temp_reports/";
