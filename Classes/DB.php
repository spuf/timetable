<?php

class DB {
	/** @var PDO */
	static $dbh;

	const DATETIME = 'Y-m-d H:i:s';

	static function Connect($dsn, $username, $password) {
		$options = array(
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
		);
		try {
			DB::$dbh = new PDO($dsn, $username, $password, $options);
			DB::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			die('<b>PDOException on connect: </b>' . $e->getMessage());
		}
	}

	static function Query($sql, $params = array(), $fetch = true, &$id = null) {
		try {
			$sth = DB::$dbh->prepare($sql);
			$sth->execute($params);
			//Debug::Log($sth->queryString);
			if ($fetch) {
				return $sth->fetchAll(PDO::FETCH_ASSOC);
			} else {
				$id = DB::$dbh->lastInsertId();
				return $sth->rowCount();
			}
		} catch (PDOException $e) {
			die('<b>PDOException on query: </b>' . $e->getMessage());
		}
	}
}
