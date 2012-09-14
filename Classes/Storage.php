<?php

class Storage {

	static function Set($key, $value) {
		DB::Query('INSERT INTO Variables (`Key`, `Value`) VALUES (:key, :value) ON DUPLICATE KEY UPDATE `Value` = :value', array(
			':key' => $key,
			':value' => serialize($value),
		), false);
	}

	static function Get($key, $default = null) {
		$data = DB::Query('SELECT `Value` FROM Variables WHERE `Key` = :key', array(
			':key' => $key,
		));
		if (count($data) > 0) {
			return unserialize($data[0]['Value']);
		}
		return $default;
	}
}
