<?php
	App::uses('AppModel', 'Model');
	//App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');
	
	class User extends AppModel {
		public $primaryKey = 'user_id';
		public $validate = array(
			'user_full_name' => array(
				'required' => array(
					'rule' => 'notBlank',
					'message' => 'Please provide full name'
				)
			),
			'user_email' => array(
				'rule' => 'email',
				'required' => true,
				'allowEmpty' => false,
				'message' => 'Please provide a valid email'
			),
			'user_password' => array(
				'required' => array(
					'rule' => 'notBlank',
					'message' => 'Please provide a password'
				)
			),
			'user_avatar' => array(
				'rule' => 'string',
				'required' => false,
				'message' => 'Please provide a valid image url'
			),
			/*'role' => array(
				'valid' => array(
					'rule' => array('inList', array('admin', 'author')),
					'message' => 'Please enter a valid role',
					'allowEmpty' => false
				)
			)*/
		);
		public function beforeSave($options = array()) {
			if (isset($this->data[$this->alias]['user_password'])) {
				/*$passwordHasher = new BlowfishPasswordHasher();
				$this->data[$this->alias]['user_password'] = $passwordHasher->hash(
					$this->data[$this->alias]['user_password']
				);*/
				$this->data[$this->alias]['user_password'] = md5($this->data[$this->alias]['user_password']);
			}
			return true;
		}
	}
?>