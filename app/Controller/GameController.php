<?php
// app/Controller/GameController.php
App::uses('AppController', 'Controller');
//App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');
class GameController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
		//$this->Auth->allow(['register', 'login']);
		$this->loadModel('GameSession');
		$this->loadModel('GameLog');
		$this->loadModel('GameConfig');
    }

	public function getActiveSession() {
		$response = null;
		if ($this->request->is('get')) {
			//$globalSettings = $this->getGlobalSettings();
			$gameSessionData = $this->getActiveSessionData();
			if($gameSessionData != null) {
				try {
					$gameSessionLog = $this->GameLog->find('all', array(
							'conditions' => array('GameLog.game_session_id' => $gameSessionData['game_session_id']),
							'order' => array('GameLog.date_created'=>'desc'),
							'limit' => 2
						));
						
					$response = [
						'gameSessionData' => $gameSessionData,
						'gameLogsData' => $gameSessionLog
					];
				} catch(Exception $e) {
					die($e->getMessage());
				}
			} else {
				$response = [
					'error' => true,
					'message' => 'No active session going on.'
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

	public function start() {
		$response = null;
		if ($this->request->is('post')) {
			$user = $this->Auth->user();
			//$this->request->getQuery()
			$gameSessionData = $this->getActiveSessionData();
			if($gameSessionData == null) {
				$globalSettings = $this->getGlobalSettings();
				$sessionData = [
					'user_id'=>$user['user_id'],
					'game_winner_type'=>'',
					'game_status'=>'game_started',
					'game_duration'=>(int)$globalSettings['game_duration'],
					'game_start_timestamp'=> time(),
					'last_player_operation_timestamp'=>0,
					'last_monster_operation_timestamp'=>0,
					'next_attack_value_for_player'=>mt_rand((int)$globalSettings['min_attack_health_percent'],(int)$globalSettings['max_attack_health_percent']),
					'next_attack_value_for_monster'=>mt_rand((int)$globalSettings['min_attack_health_percent'],(int)$globalSettings['max_attack_health_percent']),
					'next_heal_value_for_player'=>mt_rand((int)$globalSettings['min_heal_health_percent'],(int)$globalSettings['max_heal_health_percent']),
					'next_heal_value_for_monster'=>mt_rand((int)$globalSettings['min_heal_health_percent'],(int)$globalSettings['max_heal_health_percent']),
					'player_current_health'=>100,
					'monster_current_health'=>100,
					'game_config'=>json_encode($globalSettings)
				];
				if($this->GameSession->save($sessionData)) {
					$gameSession = $this->GameSession->find('first', array(
								'conditions' => array('GameSession.game_session_id' => $this->GameSession->getLastInsertId())
							));
					$gameSessionData = $gameSession['GameSession'];
					$gameSessionLogData = $this->createSessionLog('game_started', '', 'player', $gameSessionData);
					$response = [
						'gameSessionData'=>$gameSessionData,
						'gameSessionLogData'=>$gameSessionLogData
					];
				} else {
					$response = [
						'error' => true,
						'message' => 'Game session could not be started.'
					];
				}
			} else {
				$response = [
					'error' => true,
					'message' => 'You cannot start a new session without completing current session.'
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
	
	public function giveUp() {
		$response = null;
		if ($this->request->is('post')) {
			$gameSessionData = $this->getActiveSessionData();
			if($gameSessionData != null) {
				$gameSessionData['game_status'] = 'given_up';
				$gameSessionData['game_winner_type'] = 'monster';
				if ($this->GameSession->save($gameSessionData)) {
					$gameSessionLogData = $this->createSessionLog('given_up', '', 'player', $gameSessionData);
					$response = [
						'gameSessionData'=>$gameSessionData,
						'gameSessionLogData'=>$gameSessionLogData
					];
				} else {
					$response = [
						'error' => true,
						'message' => 'Game session could not be given up.'
					];
				}
			} else {
				$response = [
					'error' => true,
					'message' => 'No active session going on.'
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
	
	protected function canOpponentAttackOrHeal($gameSessionData, $operation_type, $operation_by) {
		$gameConfig = json_decode($gameSessionData['game_config']);
		$lastPlayerOperationTimestamp = $gameSessionData['last_player_operation_timestamp'];
		$lastMonsterOperationTimestamp = $gameSessionData['last_monster_operation_timestamp'];
		
		$canAttackHeal = true;
		if($operation_by == 'player' && (int)$lastPlayerOperationTimestamp) {
			if(time() - $lastPlayerOperationTimestamp < (int)$gameConfig->min_interval_for_next_turn) {
				$canAttackHeal = false;
			}
		}
		if($operation_by == 'monster' && (int)$lastMonsterOperationTimestamp) {
			if(time() - $lastMonsterOperationTimestamp < (int)$gameConfig->min_interval_for_next_turn) {
				$canAttackHeal = false;
			}
		}

		$maxHealCount = (int)$gameConfig->max_heal_count;
		if($operation_type == 'heal') {
			if($operation_by == 'player') {
				$playerCurrentHealCount = (int)$gameSessionData['player_current_heal_count'];
				if($playerCurrentHealCount >= $maxHealCount) {
					$canAttackHeal = false;
				}
			}
			if($operation_by == 'monster') {
				$monsterCurrentHealCount = (int)$gameSessionData['monster_current_heal_count'];
				if($monsterCurrentHealCount >= $maxHealCount) {
					$canAttackHeal = false;
				}
			}
		}
		$maxBlastCount = (int)$gameConfig->max_blast_count;
		if($operation_type == 'blast') {
			if($operation_by == 'player') {
				$playerCurrentBlastCount = (int)$gameSessionData['player_current_blast_count'];
				if($playerCurrentBlastCount >= $maxBlastCount) {
					$canAttackHeal = false;
				}
			}
			if($operation_by == 'monster') {
				$monsterCurrentBlastCount = (int)$gameSessionData['monster_current_blast_count'];
				if($monsterCurrentBlastCount >= $maxBlastCount) {
					$canAttackHeal = false;
				}
			}
		}
		return $canAttackHeal;
	}
	
	public function blast() {
		$response = null;
		if ($this->request->is('post')) {
			$gameSessionData = $this->getActiveSessionData();
			if($gameSessionData != null) {
				$operation_by = isset($this->request->query['by']) ? $this->request->query['by'] : 'player';
				$operation_by = $operation_by == 'monster' ? 'monster' : 'player';
				
				if($this->canOpponentAttackOrHeal($gameSessionData, 'blast', $operation_by)) {
					$gameConfig = json_decode($gameSessionData['game_config']);

					/*$nextAttackValueForPlayer = (int)$gameSessionData['next_attack_value_for_player'];
					$nextAttackValueForMonster = (int)$gameSessionData['next_attack_value_for_monster'];
					$nextHealValueForPlayer = (int)$gameSessionData['next_heal_value_for_player'];
					$nextHealValueForMonster = (int)$gameSessionData['next_heal_value_for_monster'];*/
					
					$playerCurrentHealth = (int)$gameSessionData['player_current_health'];
					$monsterCurrentHealth = (int)$gameSessionData['monster_current_health'];
					
					$blastHealthPercent = (int)$gameConfig->blast_health_percent;
					
					if($operation_by == "monster") {
						$playerCurrentHealth -= $blastHealthPercent;
						$playerCurrentHealth = $playerCurrentHealth <= 0 ? 0 : $playerCurrentHealth;
						$gameSessionData['monster_current_blast_count'] += 1;
						$gameSessionData['last_monster_operation_timestamp'] = time();
					}
					
					if($operation_by == "player") {
						$monsterCurrentHealth -= $blastHealthPercent;
						$monsterCurrentHealth = $monsterCurrentHealth <= 0 ? 0 : $monsterCurrentHealth;
						$gameSessionData['player_current_blast_count'] += 1;
						$gameSessionData['last_player_operation_timestamp'] = time();
					}
					
					if($playerCurrentHealth <= 0) {
						$gameSessionData['game_status'] = 'completed';
						$gameSessionData['game_winner_type'] = 'monster';
						
					} elseif($monsterCurrentHealth <= 0) {
						$gameSessionData['game_status'] = 'completed';
						$gameSessionData['game_winner_type'] = 'player';
					}
					
					$gameSessionData['monster_current_health'] = $monsterCurrentHealth;
					$gameSessionData['player_current_health'] = $playerCurrentHealth;
					
					if ($this->GameSession->save($gameSessionData)) {
						$gameSessionLogData = $this->createSessionLog('blast', $blastHealthPercent, $operation_by, $gameSessionData);
						if($gameSessionData['game_status'] == "completed") {
							$gameSessionLogData = $this->createSessionLog('completed', '', $operation_by, $gameSessionData);
						}
						$response = [
							'gameSessionData'=>$gameSessionData,
							'gameSessionLogData'=>$gameSessionLogData
						];
					} else {
						$response = [
							'error' => true,
							'message' => 'Could not perform operation.'
						];
					}
				} else {
					$response = [
						'error' => true,
						'message' => 'Cannot perform this operation right now. Please try again.'
					];
				}
			} else {
				$response = [
					'error' => true,
					'message' => 'No active session going on.'
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
	
	public function heal() {
		$response = null;
		if ($this->request->is('post')) {
			$gameSessionData = $this->getActiveSessionData();
			if($gameSessionData != null) {
				$operation_by = isset($this->request->query['by']) ? $this->request->query['by'] : 'player';
				$operation_by = $operation_by == 'monster' ? 'monster' : 'player';
				
				if($this->canOpponentAttackOrHeal($gameSessionData, 'heal', $operation_by)) {
					$gameConfig = json_decode($gameSessionData['game_config']);

					/*$nextAttackValueForPlayer = (int)$gameSessionData['next_attack_value_for_player'];
					$nextAttackValueForMonster = (int)$gameSessionData['next_attack_value_for_monster'];*/
					$nextHealValueForPlayer = (int)$gameSessionData['next_heal_value_for_player'];
					$nextHealValueForMonster = (int)$gameSessionData['next_heal_value_for_monster'];
					
					$playerCurrentHealth = (int)$gameSessionData['player_current_health'];
					$monsterCurrentHealth = (int)$gameSessionData['monster_current_health'];
					
					if($operation_by == "player") {
						$playerCurrentHealth += $nextHealValueForPlayer;
						$playerCurrentHealth = $playerCurrentHealth >= 100 ? 100 : $playerCurrentHealth;
						$gameSessionData['player_current_heal_count'] += 1;
						$gameSessionData['last_player_operation_timestamp'] = time();
						$gameSessionData['next_heal_value_for_player'] = mt_rand((int)$gameConfig->min_heal_health_percent,(int)$gameConfig->max_heal_health_percent);
					}
					
					if($operation_by == "monster") {
						$monsterCurrentHealth += $nextHealValueForMonster;
						$monsterCurrentHealth = $monsterCurrentHealth >= 100 ? 100 : $monsterCurrentHealth;
						$gameSessionData['monster_current_heal_count'] += 1;
						$gameSessionData['last_monster_operation_timestamp'] = time();
						$gameSessionData['next_heal_value_for_monster'] = mt_rand((int)$gameConfig->min_heal_health_percent,(int)$gameConfig->max_heal_health_percent);
					}
					
					$gameSessionData['monster_current_health'] = $monsterCurrentHealth;
					$gameSessionData['player_current_health'] = $playerCurrentHealth;
					
					if ($this->GameSession->save($gameSessionData)) {
						$gameSessionLogData = $this->createSessionLog('heal', $operation_by == 'player' ? $nextHealValueForPlayer : $nextHealValueForMonster, $operation_by, $gameSessionData);
						if($gameSessionData['game_status'] == "completed") {
							$gameSessionLogData = $this->createSessionLog('completed', '', $operation_by, $gameSessionData);
						}
						$response = [
							'gameSessionData'=>$gameSessionData,
							'gameSessionLogData'=>$gameSessionLogData
						];
					} else {
						$response = [
							'error' => true,
							'message' => 'Could not perform operation.'
						];
					}
				} else {
					$response = [
						'error' => true,
						'message' => 'Cannot perform this operation right now. Please try again.'
					];
				}
			} else {
				$response = [
					'error' => true,
					'message' => 'No active session going on.'
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
	
	public function endSession() {
		
	}

	public function attack() {
		$response = null;
		if ($this->request->is('post')) {
			$gameSessionData = $this->getActiveSessionData();
			if($gameSessionData != null) {
				$operation_by = isset($this->request->query['by']) ? $this->request->query['by'] : 'player';
				$operation_by = $operation_by == 'monster' ? 'monster' : 'player';
				
				if($this->canOpponentAttackOrHeal($gameSessionData, 'attack', $operation_by)) {
					$gameConfig = json_decode($gameSessionData['game_config']);

					$nextAttackValueForPlayer = (int)$gameSessionData['next_attack_value_for_player'];
					$nextAttackValueForMonster = (int)$gameSessionData['next_attack_value_for_monster'];
					
					$playerCurrentHealth = (int)$gameSessionData['player_current_health'];
					$monsterCurrentHealth = (int)$gameSessionData['monster_current_health'];
					
					if($operation_by == "monster") {
						$playerCurrentHealth -= $nextAttackValueForMonster;
						$playerCurrentHealth = $playerCurrentHealth <= 0 ? 0 : $playerCurrentHealth;
						$gameSessionData['last_monster_operation_timestamp'] = time();
						$gameSessionData['next_attack_value_for_monster'] = mt_rand((int)$gameConfig->min_attack_health_percent,(int)$gameConfig->max_attack_health_percent);
					}
					
					if($operation_by == "player") {
						$monsterCurrentHealth -= $nextAttackValueForPlayer;
						$monsterCurrentHealth = $monsterCurrentHealth <= 0 ? 0 : $monsterCurrentHealth;
						$gameSessionData['last_player_operation_timestamp'] = time();
						$gameSessionData['next_attack_value_for_player'] = mt_rand((int)$gameConfig->min_attack_health_percent,(int)$gameConfig->max_attack_health_percent);
					}
					
					if($playerCurrentHealth <= 0) {
						$gameSessionData['game_status'] = 'completed';
						$gameSessionData['game_winner_type'] = 'monster';
						
					} elseif($monsterCurrentHealth <= 0) {
						$gameSessionData['game_status'] = 'completed';
						$gameSessionData['game_winner_type'] = 'player';
					}
					
					$gameSessionData['monster_current_health'] = $monsterCurrentHealth;
					$gameSessionData['player_current_health'] = $playerCurrentHealth;
					
					if ($this->GameSession->save($gameSessionData)) {
						$gameSessionLogData = $this->createSessionLog('attack', $operation_by == 'player' ? $nextAttackValueForPlayer : $nextAttackValueForMonster, $operation_by, $gameSessionData);
						if($gameSessionData['game_status'] == "completed") {
							$gameSessionLogData = $this->createSessionLog('completed', '', $operation_by, $gameSessionData);
						}
						$response = [
							'gameSessionData'=>$gameSessionData,
							'gameSessionLogData'=>$gameSessionLogData
						];
					} else {
						$response = [
							'error' => true,
							'message' => 'Could not perform operation.'
						];
					}
				} else {
					$response = [
						'error' => true,
						'message' => 'Cannot perform this operation right now. Please try again.'
					];
				}
			} else {
				$response = [
					'error' => true,
					'message' => 'No active session going on.'
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
	
	protected function createSessionLog($operation_type, $operation_value, $operation_by, $gameSessionData){
		$gameSessionLog = null;
		$gameLogData = [
			'game_session_id'=>$gameSessionData['game_session_id'],
			'operation_by'=>$operation_by,
			'operation_type'=>$operation_type,
			'operation_value'=>$operation_value,
			'monster_health'=>$gameSessionData['monster_current_health'],
			'player_health'=>$gameSessionData['player_current_health']
		];
		if($this->GameLog->save($gameLogData)) {
			$gameSessionLog = $this->GameLog->find('first', array(
						'conditions' => array('GameLog.game_log_id' => $this->GameLog->getLastInsertId())
					));
			return $gameSessionLog['GameLog'];
		} else {
			return null;
		}
	}
	
	protected function getGlobalSettings() {
		//$user = $this->Auth->user();
		//$this->loadModel('GameConfig');
		$allConfigsDict = array();
		$allConfigsArray = $this->GameConfig->find('all');
		for($i = 0; $i < count($allConfigsArray); $i++) {
			$allConfigsDict[$allConfigsArray[$i]['GameConfig']['game_config_key']] = $allConfigsArray[$i]['GameConfig']['game_config_key_value'];
		}
		return $allConfigsDict;
	}
	
	protected function getActiveSessionData() {
		//$globalSettings = $this->getGlobalSettings();
		$user = $this->Auth->user();
		$gameSession = $this->GameSession->find('first', array(
			'conditions' => array(
				'(UNIX_TIMESTAMP() - UNIX_TIMESTAMP(GameSession.date_created)) < CONVERT(GameSession.game_duration, SIGNED)',
				'GameSession.game_status'=>'game_started',
				'GameSession.user_id'=>$user['user_id']
			)
		));
		if($gameSession) {
			return $gameSession['GameSession'];
		} else {
			return null;
		}
	}
}

?>