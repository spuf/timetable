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

	static function Log($msg, $var = null) {
		print "<p>$msg<br><pre>".print_r($var, true)."</pre><p>";
		DB::Query('INSERT INTO Log (`Time`, Message, Variable) VALUES (:time, :message, :variable)', array(
			':time' => date(DB::DATETIME),
			':message' => $msg,
			':variable' => serialize($var),
		), false);
	}

}
