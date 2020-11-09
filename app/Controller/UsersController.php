<?php
// app/Controller/UsersController.php
App::uses('AppController', 'Controller');
//App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');
class UsersController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
		$this->Auth->allow(['register', 'login']);
    }


	public function login() {
		$response = null;
		if ($this->request->is('post')) {
			$email = strtolower($this->request->data['user_email']);
			/*$passwordHasher = new BlowfishPasswordHasher();
			$password = $passwordHasher->hash(
						$this->request->data['user_password']
					);*/
			$password = md5($this->request->data['user_password']);
			$user = $this->User->find('first', array(
						'conditions' => array('User.user_email' => $email, 'User.user_password' => $password)
					));
			if($user) {
				$user_id = $user['User']['user_id'];
				$accessToken = $this->generateAccessToken($user_id);
				$accessTokenData = [
					'access_token' => $accessToken,
					'user_id' => $user_id
				];
				$this->loadModel('AccessToken');
				if ($this->AccessToken->save($accessTokenData)) {
					unset($user['User']['user_password']);
					$user['User']['accessToken'] = $accessToken;
					$response = $user['User'];
				} else {
					$response = [
						'error' => true,
						'message' => 'User could not be saved.'
					];
				}
			} else {
				$response = [
					'error' => true,
					'message' => 'Invalid credentials.'
				];
			}
		} else {
			$response = [
				'error' => true,
				'message' => 'Method not allowed.'
			];
		}
		$this->set([
			'result'=>$response
		]);
		/*if ($this->request->is('post')) {
			if ($this->Auth->login()) {
			
				$status = $this->Auth->user('status');
				if($status != 0){
					$this->Session->setFlash(__('Welcome, '. $this->Auth->user('username')));
					return $this->redirect($this->Auth->redirectUrl());
				}else{

				}
			} else {
				$this->Session->setFlash(__('Invalid username or password'));
			}
		}*/
	}
	
	public function register() {
		$response = null;
		if ($this->request->is('post')) {
			$user = $this->User->find('first', array(
					'conditions' => array('User.user_email' => $this->request->data['user_email'])
				));
			if(!$user) {
				$this->User->create();
				if ($this->User->save($this->request->data)) {
					$registeredUser = $this->User->find('first', array(
						'conditions' => array('User.user_id' => $this->User->getLastInsertId())
					));
					$response = $registeredUser['User'];
					unset($response['user_password']);
				} else {
					$response = [
						'error' => true,
						'message' => 'User could not be saved.'
					];
				}
			} else {
				$response = [
					'error' => true,
					'message' => 'User already added.'
				];
			}
        } else {
			$response = [
				'error' => true,
				'message' => 'Method not allowed.'
			];
		}
		$this->set([
			'result'=>$response
		]);
	}
	
	public function getUser() {
		$response = null;
		if ($this->request->is('get')) {
			$response = $this->Auth->user();
        } else {
			$response = [
				'error' => true,
				'message' => 'Method not allowed.'
			];
		}
		$this->set([
			'result'=>$response
		]);
	}

	public function logout() {

	}
	
	protected function generateAccessToken($id) {
        try {
            // Generate a random token
            $token = bin2hex(openssl_random_pseudo_bytes(16)) . SHA1(($id * time()));
            return $token;
        } catch (Exception $e) {
            return $this->_returnJson(false, $e->getMessage());
        }
    }
	
}

?>