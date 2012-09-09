<?php

class DB {
	/** @var PDO */
	static $dbh;

	static function Connect($dsn, $username, $password) {
		$options = array(
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
		);
		try {
			DB::$dbh = new PDO($dsn, $username, $password, $options);
			DB::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			die('<b>PDOException: </b>' . $e->getMessage());
		}
	}

	static function Query($sql, $params = array(), $fetch = true) {
		try {
			$sth = DB::$dbh->prepare($sql);
			$sth->execute($params);
			if ($fetch)
				return $sth->fetchAll(PDO::FETCH_ASSOC);
			else
				return $sth->rowCount();
		} catch (PDOException $e) {
			die('<b>PDOException: </b>' . $e->getMessage());
		}
	}
}

$host = isset($_SERVER["DB1_HOST"]) ? 'host='.$_SERVER["DB1_HOST"] : 'unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock';
$host .= isset($_SERVER["DB1_PORT"]) ? ';port='.$_SERVER["DB1_PORT"] : '';
$user = isset($_SERVER["DB1_USER"]) ? $_SERVER["DB1_USER"] : 'root';
$pass = isset($_SERVER["DB1_PASS"]) ? $_SERVER["DB1_PASS"] : 'root';
DB::Connect('mysql:dbname=timetable;'.$host, $user, $pass);
