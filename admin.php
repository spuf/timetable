<?php

include_once 'config.php';
include_once 'Classes/PHPExcel/IOFactory.php';

$inputFileName = './files/test.xls';

$inputFileType = PHPExcel_IOFactory::identify($inputFileName);

/** @var PHPExcel_Reader_Excel5 $objReader */
$objReader = PHPExcel_IOFactory::createReader($inputFileType);

$objReader->setLoadAllSheets();

/** @var PHPExcel $objPHPExcel */
$objPHPExcel = $objReader->load($inputFileName);

echo "File <b>".pathinfo($inputFileName, PATHINFO_BASENAME)."</b> is <b>$inputFileType</b> type,
	contains <b>{$objPHPExcel->getSheetCount()}</b> worksheet".(($objPHPExcel->getSheetCount() == 1) ? '' : 's').":<br />";
print "<pre>";
print_r($objPHPExcel->getSheetNames());
print "</pre>";

echo '<hr />';

foreach ($objPHPExcel->getAllSheets() as $objSheet) {
	print "<h3>{$objSheet->getTitle()}</h3>";

	$colCount = PHPExcel_Cell::columnIndexFromString($objSheet->getHighestColumn());
	$rowCount = $objSheet->getHighestRow();

	print "<table border='1'>";
	for ($row = 0; $row < $rowCount; $row++) {
		print "<tr>";
		for ($col = 0; $col < $colCount; $col++) {
			$cell = $objSheet->getCellByColumnAndRow($col, $row);

			$style = $objSheet->getStyleByColumnAndRow($col, $row);
			$style = styleToArray($style);
			$style = arrayToCSS($style);

			$merged = isMerged($objSheet, $cell);
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

/**
 * @param PHPExcel_Worksheet $sheet
 * @param PHPExcel_Cell $cell
 * @return bool
 */
function isMerged(&$sheet, &$cell) {
	$mergedCellsRange = $sheet->getMergeCells();
	foreach ($mergedCellsRange as $currMergedRange) {
		if ($cell->isInRange($currMergedRange)) {
			$currMergedCellsArray = PHPExcel_Cell::splitRange($currMergedRange);
			$cell = $sheet->getCell($currMergedCellsArray[0][0]);

			list($rangeStart, $rangeEnd) = PHPExcel_Cell::rangeBoundaries($currMergedRange);
			$minCol = $rangeStart[0] -1;
			$minRow = (int)$rangeStart[1];
			$maxCol = $rangeEnd[0] -1;
			$maxRow = (int)$rangeEnd[1];
			return array('col' => $maxCol - $minCol + 1, 'row' => $maxRow - $minRow + 1);
    	}
	}
	return false;
}

/**
 * @param PHPExcel_Style $style
 * @return array
 */
function styleToArray(&$style) {
	$css = array();
	$font = $style->getFont();
	$css['font-weight'] = $font->getBold() ? 'bold' : 'normal';
	$css['font-style'] = $font->getItalic() ? 'italic' : 'normal';
	$css['text-decoration'] = $font->getUnderline() != 'none' ? 'underline' : 'none';
	$css['color'] = '#'.$font->getColor()->getRGB();
	$fill = $style->getFill();
	if ($fill->getFillType() != 'none')
		$css['background-color'] = '#'.$fill->getStartColor()->getRGB();
	return $css;
}
function arrayToCSS(&$array) {
	$css = '';
	foreach ($array as $key => $value)
		$css .= "$key: $value;";
	return $css;
}

print meminfo();
