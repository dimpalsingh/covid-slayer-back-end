<?php
	App::uses('AppModel', 'Model');
	
	class GameLog extends AppModel {
		public $primaryKey = 'game_log_id';
		public $validate = array(
			'game_session_id' => array(
				'required' => array(
					'rule' => 'notBlank',
					'message' => 'Please provide a game session id'
				)
			),
			'operation_by' => array(
				'valid' => array(
					'rule' => array('inList', array('player', 'monster')),
					'message' => 'Please provide a valid operator',
					'allowEmpty' => true
				)
			),
			'operation_type' => array(
				'valid' => array(
					'rule' => array('inList', array('game_started','game_ended','attack','blast','heal','given_up','completed')),
					'message' => 'Please provide a valid operation type',
					'allowEmpty' => false
				)
			),
			'operation_value' => array(
				'rule' => 'alphanumeric',
				'required' => false,
				'allowEmpty' => true,
			),
			'monster_health' => array(
				'number' => array(
					'rule' => array('range', -1, 101),
					'message' => 'Health should be a number and between 0 and 100',
					'allowEmpty' => false
				)
			),
			'player_health' => array(
				'number' => array(
					'rule' => array('range', -1, 101),
					'message' => 'Health should be a number and between 0 and 100',
					'allowEmpty' => false
				)
			)
		);
		public $belongsTo = array(
			'GameSession' => array(
				'className' => 'GameSession',
				'foreignKey' => 'game_session_id',
			)
		);
	}
?>