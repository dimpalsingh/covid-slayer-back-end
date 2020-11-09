<?php
	App::uses('AppModel', 'Model');
	
	class GameConfig extends AppModel {
		public $primaryKey = 'access_token_id';
		public $validate = array(
			'game_config_key',
			'game_config_key_value'
		);
	}
?>