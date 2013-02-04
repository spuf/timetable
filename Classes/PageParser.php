<?php

class PageParser {

	var $html;
	var $array;

	function Load($url) {
		$this->html = file_get_contents($url);
		$this->html = mb_convert_encoding($this->html, 'utf-8');
		return (strpos($this->html, 'Расписание занятий') !== false);
	}

	function Size() {
		return round(strlen($this->html) / 1024, 1) . " kb";
	}

	function Parse() {
		$this->array = array();
		if (strpos($this->html, 'Расписание занятий') !== false) {
			$parser = new DOMParser($this->html);

			$nodes = $parser->Nodes('//div[@class="timetable"]/*');
			$category = '';
			foreach ($nodes as $node) {
				if ($parser->Name($node) == 'p' || $parser->Name($node) == 'strong') {
					$category = $parser->Value('', $node);
					$this->array[$category] = array();
				} elseif ($parser->Name($node) == 'ul') {
					foreach ($parser->Nodes('li', $node) as $item) {
						$this->array[$category][] = array(
							'name' => $parser->Value('.//a', $item),
							'date' => $parser->Value('.//a/@href', $item, '@(\d{4}/\d{2}/\d{2})/\d+/@') . ' ' . date('H:i:s', $parser->Value('.//a/@href', $item, '@\d{4}/\d{2}/\d{2}/(\d+)/@')),
							'link' => $parser->Value('.//a/@href', $item),
						);
					}
				}
			}
		}
		//Debug::Log('Parsed page', $this->array);
		return $this->array;
	}

	function GetParsable($array = null) {
		if (is_null($array))
			$array = $this->array;
		$array = isset($array['Дневное отделение']) ? $array['Дневное отделение'] : array();
		$array = array_filter($array, function ($item) {
			return (pathinfo($item['link'], PATHINFO_EXTENSION) == 'xls' && strpos($item['name'], 'неделя') !== false);
		});
		return $array;
	}

	function GetDiff(&$cache) {
	 	$new = $this->GetParsable();
		$old = $this->GetParsable($cache);
		$result = array_udiff($new, $old, function ($a, $b) {
			foreach (array('name', 'date', 'link')  as $key)
				if ($a[$key] != $b[$key])
					return 1;
			return 0;
		});
		return $result;
	}
}