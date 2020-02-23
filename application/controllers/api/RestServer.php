<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use RestServer\Libraries\REST_Controller;

class RestServer extends MY_Controller {
	
	public function helloworld_get() {
		// Users from a data store e.g. database
        $users = [
            ['id' => 0, 'name' => 'John', 'email' => 'john@example.com'],
            ['id' => 1, 'name' => 'Jim', 'email' => 'jim@example.com'],
        ];

		// Set the response and exit
		$this->response( $users, 200 );
	}
}
