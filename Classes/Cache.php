<?php

class Cache {

	static function Query($sql, $invalidate = false) {
		$key = 'cache:'.md5($sql);
		$value = !$invalidate ? Storage::Get($key, null) : null;
		if (is_null($value)) {
			$value = DB::Query($sql);
			Storage::Set($key, $value);
		}
		return $value;
	}

}
