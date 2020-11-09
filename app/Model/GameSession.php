<?php
	App::uses('AppModel', 'Model');
	
	class GameSession extends AppModel {
		public $primaryKey = 'game_session_id';
		public $validate = array(
			'user_id' => array(
				'required' => array(
					'rule' => 'notBlank',
					'message' => 'Please provide a user id'
				)
			),
			'game_winner_type' => array(
				'valid' => array(
					'rule' => array('inList', array('player', 'monster')),
					'message' => 'Please provide a valid winner',
					'allowEmpty' => true
				)
			),
			'game_status' => array(
				'valid' => array(
					'rule' => array('inList', array('game_started', 'completed', 'given_up')),
					'message' => 'Please provide a valid status',
					'allowEmpty' => false
				)
			),
			'game_duration' => array(
				'number' => array(
					'rule' => array('range', 60, null),
					'message' => 'Game duration should be a number and atleast 60 seconds',
					'allowEmpty' => false
				)
			),
			'game_start_timestamp' => array(
				'rule' => 'numeric',
				'required' => false,
				'message' => 'Please provide a valid timestamp'
			),
			'last_player_operation_timestamp' => array(
				'rule' => 'numeric',
				'required' => false,
				'message' => 'Please provide a valid timestamp'
			),
			'last_monster_operation_timestamp' => array(
				'rule' => 'numeric',
				'required' => false,
				'message' => 'Please provide a valid timestamp'
			),
			'next_attack_value_for_player' => array(
				'number' => array(
					'rule' => array('range', 0, 101),
					'message' => 'Attack value should be a number and between 1 and 100',
					'allowEmpty' => false
				)
			),
			'next_attack_value_for_monster' => array(
				'number' => array(
					'rule' => array('range', 0, 101),
					'message' => 'Attack value should be a number and between 1 and 100',
					'allowEmpty' => false
				)
			),
			'next_heal_value_for_player' => array(
				'number' => array(
					'rule' => array('range', 0, 101),
					'message' => 'Heal value should be a number and between 1 and 100',
					'allowEmpty' => false
				)
			),
			'next_heal_value_for_monster' => array(
				'number' => array(
					'rule' => array('range', 0, 101),
					'message' => 'Heal value should be a number and between 1 and 100',
					'allowEmpty' => false
				)
			),
			'player_current_health' => array(
				'number' => array(
					'rule' => array('range', -1, 101),
					'message' => 'Health value should be a number and between 0 and 100',
					'allowEmpty' => false
				)
			),
			'monster_current_health' => array(
				'number' => array(
					'rule' => array('range', -1, 101),
					'message' => 'Health value should be a number and between 0 and 100',
					'allowEmpty' => false
				)
			),
			'player_current_heal_count' => array(
				'number' => array(
					'rule' => array('range', -1, null),
					'message' => 'Heal count should be a number and greater than equal to 0',
					'allowEmpty' => false
				)
			),
			'player_current_blast_count' => array(
				'number' => array(
					'rule' => array('range', -1, null),
					'message' => 'Blast count should be a number and greater than equal to 0',
					'allowEmpty' => false
				)
			),
			'monster_current_heal_count' => array(
				'number' => array(
					'rule' => array('range', -1, null),
					'message' => 'Heal count should be a number and greater than equal to 0',
					'allowEmpty' => false
				)
			),
			'monster_current_blast_count' => array(
				'number' => array(
					'rule' => array('range', -1, null),
					'message' => 'Blast count should be a number and greater than equal to 0',
					'allowEmpty' => false
				)
			),
			'game_config' => array(
				'required' => array(
					'rule' => 'notBlank',
					'message' => 'Please provide a valid json string for game configuration'
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