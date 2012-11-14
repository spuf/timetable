<?php

include_once __DIR__.'/PHPExcel/IOFactory.php';

class Parser
{
	/**
	 * @var PHPExcel_Reader_Excel5
	 */
	var $objReader;
	/**
	 * @var PHPExcel
	 */
	var $objPHPExcel;

	var $tempFile;

	function LoadFromFile($inputFileName) {
		//$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
		$inputFileType = 'Excel5';

		$this->objReader = PHPExcel_IOFactory::createReader($inputFileType);

		$this->objReader->setLoadAllSheets();

		$this->objPHPExcel = $this->objReader->load($inputFileName);

		Debug::Log("Filename: ".pathinfo($inputFileName, PATHINFO_BASENAME).
			", Type: $inputFileType, Worksheets: {$this->objPHPExcel->getSheetCount()}",
			$this->objPHPExcel->getSheetNames()
		);
		return $this->objPHPExcel->getSheetNames();
	}

	function LoadFromURL($url) {
		$this->tempFile = './uploads/'.pathinfo($url, PATHINFO_BASENAME);

		$data = file_get_contents($url);
		file_put_contents($this->tempFile, $data);

		$this->LoadFromFile($this->tempFile);
	}

	function UnloadExcel() {
		unset($this->objPHPExcel);
		unset($this->objReader);
		unlink($this->tempFile);
	}

	function PrintHTML() {
		foreach ($this->objPHPExcel->getAllSheets() as $objSheet) {
			print "<h3>{$objSheet->getTitle()}</h3>";

			$colCount = PHPExcel_Cell::columnIndexFromString($objSheet->getHighestColumn());
			$rowCount = $objSheet->getHighestRow();

			print "<table border='1'>";
			for ($row = 0; $row < $rowCount; $row++) {
				print "<tr>";
				for ($col = 0; $col < $colCount; $col++) {
					$cell = $objSheet->getCellByColumnAndRow($col, $row);

					$style = $objSheet->getStyleByColumnAndRow($col, $row);
					$style = $this->styleToCSS($style);

					$merged = $this->isMerged($objSheet, $cell);
					$text = $cell->getFormattedValue();
					$text = empty($text) ? '' : nl2br($text);

					if ($merged && $merged['col'] > 1) {
						print "<td style='$style' colspan='{$merged['col']}'>$text</td>";
						$col += $merged['col'] - 1;
					} else {
						print "<td style='$style'>$text</td>";
					}
				}
				print "</tr>";
			}
			print "</table>";
			//var_dump($sheetData);
		}
	}

	function PrintTimetableArray(&$timetable) {
		foreach ($timetable as $group => $days) {
			print "<h3>$group</h3>";
			print "<table border='1'>";
			foreach ($days as $date => $day) {
				if (is_array($day['pairs'])) {
					$first = "<td rowspan=".count($day['pairs']).">$date<br>{$day['dow']}</td>";
					foreach ($day['pairs'] as $number => $pair) {
						print "<tr>";
						if (!empty($first)) {
							print $first;
							$first = null;
						}
						print "<td>$number<br>{$pair['time']}</td>";
						if (empty($pair['style']))
							$pair['style'] = '';
						if (empty($pair['with']))
							$with = '';
						else {
							$with = "<div>".implode($pair['with'])."</div>";
						}
						print "<td><div style='{$pair['style']}'>".nl2br($pair['title'])."</div>$with</td>";
						print "</tr>";
					}
				} else {
					print "<tr><td>$date<br>{$day['dow']}</td><td>&nbsp;</td></tr>";
				}
			}
			print "</table>";
		}
	}

	function ToTimetableArray() {
		$timetable = array();
		foreach ($this->objPHPExcel->getAllSheets() as $objSheet) {
			$timetable = array_merge($timetable, $this->SheetToTimetableArray($objSheet));
		}
		return $timetable;
	}

	function SheetToTimetableArray(PHPExcel_Worksheet &$objSheet) {
		$timetable = array();

		$colCount = PHPExcel_Cell::columnIndexFromString($objSheet->getHighestColumn());
		$rowCount = $objSheet->getHighestRow();

		$groupsRow = $this->GetGroupsRow($objSheet, $colCount, $rowCount);
		$groups = $this->GetGroupsColumns($objSheet, $colCount, $groupsRow);

		$day = null;
		$pair = null;
		for ($row = $groupsRow + 1; $row < $rowCount; $row++) {
			for ($col = 0; $col < $colCount; $col++) {
				
				$cell = $objSheet->getCellByColumnAndRow($col, $row);
				$text = $cell->getFormattedValue();

				if ($temp = $this->IsDayCell($text)) {
					
					$day = $temp;
					$pair = array(
						'number' => 0,
						'time' => '',
					);
					
				} elseif ($temp = $this->IsPairCell($text)) {
					
					$pair = $temp;
					
				} elseif ($day && isset($groups[$col])) {
					
					$info = array(
						'time' => $pair['time'],
						'title' => preg_replace("/\s+\n+/", "\n", str_replace("\r", "", preg_replace('/[ ]+/', ' ', trim($text)))),
					);
					
					if (!empty($info['title'])) {
						
						$style = $objSheet->getStyleByColumnAndRow($col, $row);
						$style = $this->styleToCSS($style);
						if (!empty($style))
							$info['style'] = $style;

						if ($colspan = $this->isMerged($objSheet, $cell)) {
							$info['with'] = array();
							for ($g = 0; $g < $colspan; $g++) {
								if (isset($groups[$col + $g])) {
									$info['with'][] = $groups[$col + $g];
								}
							}
							for ($g = 0; $g < $colspan; $g++) {
								if (isset($groups[$col + $g])) {
									$this->SetTimetible($timetable, $groups, $col + $g, $day, $pair, $info);
								}
							}
						}
					}
					$this->SetTimetible($timetable, $groups, $col, $day, $pair, $info);
				}
			}
		}

		$this->CleanTimetable($timetable);

		return $timetable;
	}

	function GetGroupsRow(PHPExcel_Worksheet &$objSheet, $colCount, $rowCount) {
		$groupsRow = 0;

		$max = 0;
		for ($row = 0; $row < $rowCount; $row++) {
			$sum = 0;
			for ($col = 0; $col < $colCount; $col++) {
				$cell = $objSheet->getCellByColumnAndRow($col, $row);
				$text = $cell->getFormattedValue();
				$sum += preg_match('/.+-\d+-\S+/u', $text);
			}
			if ($sum > $max) {
				$groupsRow = $row;
				$max = $sum;
			}
		}
		//Debug::Log("Found groups row", $groupsRow);

		return $groupsRow;
	}

	function GetGroupsColumns(PHPExcel_Worksheet &$objSheet, $colCount, $groupsRow) {
		$groups = array();
		for ($col = 0; $col < $colCount; $col++) {
			$cell = $objSheet->getCellByColumnAndRow($col, $groupsRow);
			$text = $cell->getFormattedValue();
			if (preg_match('/.+-\d+-\S+/u', $text)) {
				$groups[$col] = $text;
			}
		}
		//Debug::Log('Groups list', $groups);

		return $groups;
	}

	function IsDayCell(&$text) {
		if (preg_match('/^\s*(?<dow>\S+).+(?<date>\d{2}\.\d{2}\.\d{4})\s*$/us', $text, $matches)) {
			if (preg_match('/(?<d>\d{2})\.(?<m>\d{2})\.(?<y>\d{4})/', $matches['date'], $date)) {
				$date = $date['y'] .'-'. $date['m'] .'-'. $date['d'];
				//Debug::Log("Found date", $date);
				$day = array(
					'date' => $date,
					'dow' => $matches['dow'],
				);
				return $day;
			}
		}
		return false;
	}

	function IsPairCell(&$text) {
		if (preg_match('/^\s*(?<number>\d)\s+(?<time>\d{1,2}[:\.]{1}\d{2}[- ]+\d{1,2}[:\.]{1}\d{2})?\s*$/us', $text, $matches)) {
			$pair = array(
				'number' => intval($matches['number']),
				'time' => !empty($matches['time']) ? $matches['time'] : '?',
			);
			return $pair;
		}
		return false;
	}

	function SetTimetible(&$timetable, &$groups, $col, &$day, &$pair, &$info) {
		if (empty($timetable[$groups[$col]][$day['date']]['pairs'][$pair['number']])) {
			$timetable[$groups[$col]][$day['date']]['dow'] = $day['dow'];
			$timetable[$groups[$col]][$day['date']]['pairs'][$pair['number']] = $info;
		} else {
			if (empty($timetable[$groups[$col]][$day['date']]['pairs'][$pair['number']]['title']))
				$timetable[$groups[$col]][$day['date']]['pairs'][$pair['number']]['title'] = $info['title'];
			elseif (empty($info['title']))
				/* Nothing to do here */;
			elseif ($timetable[$groups[$col]][$day['date']]['pairs'][$pair['number']]['title'] == $info['title'])
				/* Nothing to do here */;
			elseif ($timetable[$groups[$col]][$day['date']]['pairs'][$pair['number']]['title'] != $info['title'])
				$timetable[$groups[$col]][$day['date']]['pairs'][$pair['number']]['title'] .= "\n---\n".$info['title'];
			else
				$timetable[$groups[$col]][$day['date']]['pairs'][$pair['number']]['title'] .= "\n-----\n".$info['title'];
		}
	}

	function CleanTimetable(&$timetable) {
		foreach ($timetable as &$days) {
			foreach ($days as &$day) {
				ksort($day['pairs']);
				$found = true;
				do {
					reset($day['pairs']);
					$pair = current($day['pairs']);
					if (is_array($pair) && empty($pair['title'])) {
						list($k) = array_keys($day['pairs']);
						unset($day['pairs'][$k]);
					}
					else
						$found = false;
				} while ($found && !empty($day['pairs']));
				$found = true;
				do {
					end($day['pairs']);
					$pair = current($day['pairs']);
					if (is_array($pair) && empty($pair['title']))
						array_pop($day['pairs']);
					else
						$found = false;
				} while ($found && !empty($day['pairs']));
			}
		}
	}


	/**
	 * @param PHPExcel_Worksheet $sheet
	 * @param PHPExcel_Cell $cell
	 * @return bool or int
	 */
	function isMerged(&$sheet, &$cell) {
		$mergedCellsRange = $sheet->getMergeCells();
		foreach ($mergedCellsRange as $currMergedRange) {
			if ($cell->isInRange($currMergedRange)) {
				//$currMergedCellsArray = PHPExcel_Cell::splitRange($currMergedRange);
				//$cell = $sheet->getCell($currMergedCellsArray[0][0]);

				list($rangeStart, $rangeEnd) = PHPExcel_Cell::rangeBoundaries($currMergedRange);
				$minCol = $rangeStart[0] -1;
				//$minRow = (int)$rangeStart[1];
				$maxCol = $rangeEnd[0] -1;
				//$maxRow = (int)$rangeEnd[1];
				$colspan = $maxCol - $minCol + 1;
				//$rowspan = $maxRow - $minRow + 1;
				if ($colspan > 1)
					return $colspan;
				return false;
			}
		}
		return false;
	}

	/**
	 * @param PHPExcel_Style $style
	 * @return string
	 */
	function styleToCSS(&$style) {
		$css = array();
		$font = $style->getFont();
		//$css['font-weight'] = $font->getBold() ? 'bold' : 'normal';
		$css['font-style'] = $font->getItalic() ? 'italic' : 'normal';
		$css['text-decoration'] = $font->getUnderline() != 'none' ? 'underline' : 'none';
		$css['color'] = '#'.$font->getColor()->getRGB();
		$fill = $style->getFill();
		if ($fill->getFillType() != 'none')
			$css['background-color'] = '#'.$fill->getStartColor()->getRGB();

		$normalCss = array(
			'font-weight' => 'normal',
			'font-style' => 'normal',
			'text-decoration' => 'none',
			'color' => '#000000',
		);

		$diffCss = array_diff_assoc($css, $normalCss);

		$cssString = '';
		foreach ($diffCss as $key => $value)
			$cssString .= "$key:$value;";
		return $cssString;
	}

}
