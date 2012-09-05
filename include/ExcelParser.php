<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '../PHPExcel/Classes/');
include_once 'PHPExcel/IOFactory.php';

class ExcelParser
{
	/**
	 * @var PHPExcel_Reader_Excel5
	 */
	var $objReader;
	/**
	 * @var PHPExcel
	 */
	var $objPHPExcel;

	function __construct($filename) {
		$inputFileType = PHPExcel_IOFactory::identify($filename);
		echo 'File ',pathinfo($filename, PATHINFO_BASENAME),' has been identified as an ',$inputFileType,' file<br />';

		echo 'Loading file ',pathinfo($filename, PATHINFO_BASENAME),' using IOFactory with the identified reader type<br />';
		/** @var $objReader PHPExcel_Reader_Excel5 **/
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);

		echo 'Loading all WorkSheets<br />';
		$objReader->setLoadAllSheets();

		$objPHPExcel = $objReader->load($filename);

	}

	function ToHTML() {

	}
}
