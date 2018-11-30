<?php

class DBAdapter {
	
	private $DB;
	private const $DB_NAME = 'finalproject';
	
	
	public function __construct() {
		$db = 'mysql:dbname='.$DB_NAME.';charset=utf8;host=127.0.0.1';
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
		$statement->bindParam("password", password_hash($password, PASSWORD_DEFAULT));
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
		if (!password_verify($password, $account['password']))
			return [false, -1];
		return [true, $account['id']];
	}
	
	public function changePassword($username, $oldPass, $newPass) {
		$valid = loginAccount($username, $oldPass);
		if (!$valid)
			return false;
		$statement = $this->DB->prepare('UPDATE users SET password = :newPass WHERE username = :username;');
		$statement->bindParam('username', htmlspecialchars($username));
		$statement->bindParam('newPass', password_hash(htmlspecialchars($newPass), PASSWORD_DEFAULT));
		$statement->execute();
		return true;
	}
	
	public function saveGameData($userId, $data) {
		
	}
	
	public function getGameData($userId) {
		
	}
	
}

?>