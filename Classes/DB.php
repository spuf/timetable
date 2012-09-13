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
