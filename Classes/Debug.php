<?php

class Debug {

	static function MemInfo($html = true) {
		$mb = pow(1024, 2);
		$usage = round(memory_get_peak_usage() / $mb, 1);
		$peak = round(memory_get_peak_usage(true) / $mb, 1);

		if ($html)
			return "<div style='position: fixed; top: 0; right: 0; border: 1px black solid; background: white; padding: 3px;'>$usage mb / $peak mb</div>";
		else
			return "($usage mb / $peak mb)";
	}

	static function Log($msg) {
		if (is_string($msg))	{
			print "<p>$msg</p>";
		} else {
			print "<pre>".print_r($msg, true)."</pre>";
		}
	}

}
