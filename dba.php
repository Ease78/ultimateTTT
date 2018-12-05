<?php

class DBAdapter {
	
	private $DB;
	
	
	public function __construct() {
		$db = "mysql:dbname=finalproject;charset=utf8;host=127.0.0.1";
		$user = 'root';
		$pass = '';
		try {
			$this->DB = new PDO($db, $user, $pass);
			$this->DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			echo 'Error establishing connection';
			exit();
		}
	}
	
	public function registerAccount($username, $password) {
		$username = htmlspecialchars($username);
		$password = htmlspecialchars($password);
		$statement = $this->DB->prepare("SELECT username FROM users WHERE username = :username;");
		$statement->bindParam("username", $username);
		$statement->execute();
		$matches = $statement->fetchAll(PDO::FETCH_ASSOC);
		if (count($matches) != 0)
			return false;
		$statement = $this->DB->prepare("INSERT INTO users (username, password) values (:username, :password);");
		$statement->bindParam("username", $username);
		$hashedPass = password_hash($password, PASSWORD_DEFAULT);
		$statement->bindParam("password", $hashedPass);
		$statement->execute();
		return true;
	}
	
	public function loginAccount($username, $password) {
		$username = htmlspecialchars($username);
		$password = htmlspecialchars($password);
		$statement = $this->DB->prepare("SELECT password, id FROM users WHERE username = :username;");
		$statement->bindParam("username", $username);
		$statement->execute();
		$account = $statement->fetchAll(PDO::FETCH_ASSOC);
		if (count($account) == 0)
			return [false, -1];
		if (!password_verify($password, $account[0]['password']))
			return [false, -1];
		return [true, $account[0]['id']];
	}
	
	public function changePassword($username, $oldPass, $newPass) {
		$valid = loginAccount($username, $oldPass);
		$username = htmlspecialchars($username);
		$password = htmlspecialchars($oldPass);
		$password = htmlspecialchars($newPass);
		if (!$valid)
			return false;
		$statement = $this->DB->prepare('UPDATE users SET password = :newPass WHERE username = :username;');
		$statement->bindParam('username', $username);
		$hashedPass = password_hash($newPass, PASSWORD_DEFAULT);
		$statement->bindParam('newPass', $hashedPass);
		$statement->execute();
		return true;
	}
	
	public function saveGameData($username, $result, $type, $opponent) {
		$username = htmlspecialchars($username);
		$result = htmlspecialchars($result);
		$opponent = htmlspecialchars($opponent);
		$type = htmlspecialchars($type);
		$statement = $this->DB->prepare('SELECT id FROM users WHERE username = :user');
		$statement->bindParam('user', $username);
		$statement->execute();
		$account = $statement->fetchAll(PDO::FETCH_ASSOC);
		if (count($account) == 0)
			return false;
		$userId = $account[0]['id'];
		$statement = $this->DB->prepare('INSERT INTO games (user_id, game_result, game_type, opponent) values (:userId, :result, :type, :opp);');
		$statement->bindParam('userId', $userId);
		$statement->bindParam('result', $result);
		$statement->bindParam('type', $type);
		$statement->bindParam('opp', $opponent);
		$statement->execute();
		return true;
	}
	
	public function getGameData($username) {
		$username = htmlspecialchars($username);
		$statement = $this->DB->prepare('SELECT games.game_result, games.game_type FROM games JOIN users ON games.user_id = users.id WHERE users.username = :user;');
		$statement->bindParam('user', $username);
		$statement->execute();
		$records = $statement->fetchAll(PDO::FETCH_ASSOC);
		$stats = array('count' => 0, 'fav' => "", 'win' => 0, 'loss' => 0, 'tie' => 0);
		$stats['count'] = count($records);
		$ultGames = 0;
		$normGames = 0;
		for ($i = 0; $i < $stats['count']; $i++) {
			if ($records[$i]['game_type'] == 'u')
				$ultGames++;
			else //'n'
				$normGames++;
			//
			if ($records[$i]['game_result'] == 'w') $stats['win']++;
			if ($records[$i]['game_result'] == 'l') $stats['loss']++;
			if ($records[$i]['game_result'] == 't') $stats['tie']++;
		}
		if ($ultGames > $normGames)
			$stats['fav'] = "Ultimate";
		else if ($ultGames < $normGames)
			$stats['fav'] = "Normal";
		else
			$stats['fav'] = "Both!";
		
		return $stats;
	}
	
}

?>