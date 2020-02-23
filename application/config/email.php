<?php
defined('BASEPATH') OR exit('No direct script access allowed');


$config['protocol'] = 'smtp';
$config['smtp_host'] = '192.168.104.24';    //192.168.104.24 192.168.104.41
$config['smtp_user'] = 'notificaciones@cochabamba.bo';
$config['smtp_pass'] = 'temporal.1';
$config['smtp_port'] = '25';  //25  465  587
$config['mailtype'] = 'html';
$config['charset'] = 'utf-8';
$config['wordwrap'] = TRUE;
$config['priority'] = 3;