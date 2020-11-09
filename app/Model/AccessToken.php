<?php
	App::uses('AppModel', 'Model');
	
	class AccessToken extends AppModel {
		public $primaryKey = 'access_token_id';
		public $validate = array(
			'user_id' => array(
				'required' => array(
					'rule' => 'notBlank',
					'message' => 'Please provide a user id'
				)
			),
			'access_token' => array(
				'required' => array(
					'rule' => 'notBlank',
					'message' => 'Please provide an access token'
				)
			),
		);
		public $belongsTo = array(
			'User' => array(
				'className' => 'User',
				'foreignKey' => 'user_id',
				'fields' => 'User.user_id, User.user_full_name, User.user_email'
			)
		);
	}
?>