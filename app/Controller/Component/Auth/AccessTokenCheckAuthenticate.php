<?php
App::uses('BaseAuthenticate', 'Controller/Component/Auth');
//App::uses('AccessToken', 'Model/AccessToken');
//use Cake\Model\AccessToken;

class AccessTokenCheckAuthenticate extends BaseAuthenticate {
    public function authenticate(CakeRequest $request, CakeResponse $response) {
        // Do things for OpenID here.
        // Return an array of user if they could authenticate the user,
        // return false if not
		/*return [
				'sss' => $request->header('Authorization')
			];*/
		$token = $request->header('Authorization');
		//echo $token;
		//die;
		if($token) {
			//$this->loadModel('AccessToken');
			$this->AccessToken = ClassRegistry::init('AccessToken');
			$tokenObj = $this->AccessToken->find('first', array(
				'conditions' => array('AccessToken.access_token' => $token)
			));
			//echo $this->AccessToken->lastQuery();
			//die;
			if($tokenObj && $tokenObj['User']) {
				return $tokenObj['User'];
			}
		}
		return false;
    }
}
?>