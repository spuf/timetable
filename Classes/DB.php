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
			$msg = 'PDOException on connect: ' . $e->getMessage();
			Debug::Log($msg, $dsn);
			die($msg);
		}
	}

	static function Query($sql, $params = array(), $fetch = true, &$id = null, $log = true) {
		try {
			$sth = DB::$dbh->prepare($sql);
			$sth->execute($params);
			//if ($log)
			//	Debug::Log('SQL query is', $sth->queryString);
			if ($fetch) {
				return $sth->fetchAll(PDO::FETCH_ASSOC);
			} else {
				$id = DB::$dbh->lastInsertId();
				return $sth->rowCount();
			}
		} catch (PDOException $e) {
			$msg = 'PDOException on query: ' . $e->getMessage();
			Debug::Log($msg, func_get_args());
			die($msg);
		}
	}

}
