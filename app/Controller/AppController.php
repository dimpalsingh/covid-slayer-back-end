<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');


/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		https://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
	/*public $components = [
		'RequestHandler'
	];*/
	
	public $components = array(
		'RequestHandler',
        'Auth' => array(
            'authenticate' => array(
                'AccessTokenCheck'
            )
        )
    );

	public function authenticate($data) {
		
	}
	
	public function beforeRender() {
		//$this->response->header('Access-Control-Allow-Origin', '*');
	}
	
	public function beforeFilter() {
		//$this->response->header('Access-Control-Allow-Origin', '*');
		//echo "header";
		/*$this->Auth->authenticate = array(
			'Openid', // app authentication object.
			'AuthBag.Combo', // plugin authentication object.
		);*/
		//var_dump($this->Auth->user());
		//var_dump($this->request->header('Authorization'));
		//die;
		//return false;
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: *');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}
        /*$this->response->header('Access-Control-Allow-Origin','*');
        $this->response->header('Access-Control-Allow-Methods','*');
        $this->response->header('Access-Control-Allow-Headers','*');
        //$this->response->header('Access-Control-Allow-Headers','Content-Type, x-xsrf-token');
        $this->response->header('Access-Control-Max-Age','172800');*/
		if($this->request->header('Authorization')) {
			if(!$this->Auth->login()) {
				echo json_encode([
					'result' => false,
					'message' => 'Invalid login token provided.'
				]);
				die;
			}
		}
	}
}
