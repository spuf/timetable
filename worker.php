<?php

require_once 'config.php';
require_once 'DB.php';

function nodeValue(DOMNodeList $nodes, $regexp = null) {
	$value = ($nodes->length > 0) ? $nodes->item(0)->nodeValue : '';
	if (!empty($regexp)) {
		if (preg_match($regexp, $value, $matches)) {
			if (isset($matches[1])) {
				$value = $matches[1];
			}
		}
	}
	$value = trim($value);
	return $value;
}

function Check() {
	$html = file_get_contents('http://www.hse.perm.ru/student/timetable/');
	$info = round(strlen($html) / 1024, 1) . " kb";

	$result = array();
	if (strpos($html, 'Расписание занятий') !== false) {
		libxml_use_internal_errors(true);
		libxml_disable_entity_loader(true);
		$dom = new DOMDocument();
		$dom->loadHTML($html);
		libxml_clear_errors();
		libxml_disable_entity_loader(false);
		libxml_use_internal_errors(false);

		$xpath = new DOMXPath($dom);
		$nodes = $xpath->query('//div[@id="content"]/div/*');
		$category = '';
		foreach ($nodes as $node) {
			if ($node->nodeName == 'p') {
				$category = $node->nodeValue;
				$result[$category] = array();
			} elseif ($node->nodeName == 'ul') {
				foreach ($xpath->query('li', $node) as $item) {
					$result[$category][] = array(
						'name' => nodeValue($xpath->query('a', $item)),
						'date' => nodeValue($xpath->query('small', $item), '/Дата изменения:\s+(.+)\./ims'),
						'link' => nodeValue($xpath->query('a/@href', $item)),
					);
				}
			}
		}
	}
	$data = json_encode(isset($result['Дневное отделение']) ? $result['Дневное отделение'] : $result, JSON_UNESCAPED_UNICODE);

	$text = 'Now is '.date('H:i:s').'. Memory used '.meminfo(false).'. Page size '.$info.'. '.$data;
	print "<p>$text</p>";
	DB::Query('INSERT INTO log(text) VALUES(:text)', array(':text' => $text), false);
}

while (true) {
	Check();
	sleep(60 * 15);
}
