<?php

class DOMParser {

	var $document;
	var $xpath;

	function __construct($html) {
		libxml_use_internal_errors(true);
		libxml_disable_entity_loader(true);

		$this->document = new DOMDocument('1.0', 'utf-8');
		$this->document->loadHTML($html);

		libxml_clear_errors();
		libxml_disable_entity_loader(false);
		libxml_use_internal_errors(false);

		$this->xpath = new DOMXPath($this->document);
	}

	function Nodes($query, DOMNode $root = null) {
		return $this->xpath->query($query, $root);
	}

	function Name(DOMNode $node) {
		return strtolower($node->nodeName);
	}

	function Value($query, DOMNode $root = null, $regexp = null) {
		if (!empty($query)) {
			$nodes = $this->xpath->query($query, $root);
			$value = ($nodes->length > 0) ? $nodes->item(0)->nodeValue : null;
		} else {
			$value = !is_null($root) ? $root->nodeValue : null;
		}
		if (!is_null($value)) {
			if (!empty($regexp)) {
				if (preg_match($regexp, $value, $matches)) {
					if (isset($matches[1])) {
						$value = $matches[1];
					}
				}
			}
			$value = $this->CleanXMLValue($value);
		}
		return $value;
	}

	function CleanXMLValue( $s ){
		$s = mb_convert_encoding($s, 'UTF-8', 'UTF-8'); // remove bugged symbols
		$s = preg_replace("/\p{Mc}/u", ' ', $s); // normalize spaces
		$s = trim(preg_replace("/\s+/u", " ", preg_replace("/\r|\n|\t/u", ' ', $s)));
		$s = html_entity_decode($s, ENT_COMPAT, 'UTF-8');
		return $s;
	}

}