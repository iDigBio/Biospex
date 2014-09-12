<?php namespace Biospex\Services\Xml;
/**
 * XmlProcess.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <bruhnrp@gmail.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */

class XmlProcess {

	private $xml = null;
	private $xpath = null;
	private $encoding = 'UTF-8';

	/**
	 * Initialize the root XML node [optional]
	 * @param $version
	 * @param $encoding
	 * @param $format_output
	 */
	public static function init($version = '1.0', $encoding = 'UTF-8', $format_output = true) {
		self::$xml = new \DOMDocument($version, $encoding);
		self::$xml->preserveWhiteSpace = false;
		self::$xml->formatOutput = $format_output;
		self::$encoding = $encoding;
	}

	/**
	 * Load xml string and return
	 *
	 * @param $input_xml
	 * @return null
	 * @throws Exception
	 */
	public function load($input_xml)
	{
		$this->xml = $this->getXMLRoot();
		$parsed = $this->xml->load($input_xml);
		if(!$parsed) {
			throw new Exception('[XML2Array] Error parsing the XML string.');
		}

		$this->setXPath();

		return $this->xml;
	}

	/**
	 * Convert an XML to Array
	 *
	 * @param $xml
	 * @return mixed
	 */
	public function &createArray($xml) {
		$array[$xml->documentElement->tagName] = self::convert($xml->documentElement);
		$this->xml = null;    // clear the xml node in the class for 2nd time use.
		return $array;
	}

	/**
	 * Convert an Array to XML
	 * @param mixed $node - XML as a string or as an object of DOMDocument
	 * @return mixed
	 */
	private function &convert($node) {
		$output = array();

		switch ($node->nodeType) {
			case XML_CDATA_SECTION_NODE:
				$output['@cdata'] = trim($node->textContent);
				break;

			case XML_TEXT_NODE:
				$output = trim($node->textContent);
				break;

			case XML_ELEMENT_NODE:

				// for each child node, call the covert function recursively
				for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
					$child = $node->childNodes->item($i);
					$v = self::convert($child);
					if(isset($child->tagName)) {
						$t = $child->tagName;

						// assume more nodes of same kind are coming
						if(!isset($output[$t])) {
							$output[$t] = array();
						}
						$output[$t][] = $v;
					} else {
						//check if it is not an empty text node
						if($v !== '') {
							$output = $v;
						}
					}
				}

				if(is_array($output)) {
					// if only one node of its kind, assign it directly instead if array($value);
					foreach ($output as $t => $v) {
						if(is_array($v) && count($v)==1) {
							$output[$t] = $v[0];
						}
					}
					if(empty($output)) {
						//for empty nodes
						$output = '';
					}
				}

				// loop through the attributes and collect them
				if($node->attributes->length) {
					$a = array();
					foreach($node->attributes as $attrName => $attrNode) {
						$a[$attrName] = (string) $attrNode->value;
					}
					// if its an leaf node, store the value in @value instead of directly storing it.
					if(!is_array($output)) {
						$output = array('@value' => $output);
					}
					$output['@attributes'] = $a;
				}
				break;
		}
		return $output;
	}

	/**
	 * Get dom document attribute by tag
	 *
	 * @param $tag
	 * @param $attribute
	 * @return mixed
	 */
	public function getDomTagAttribute($tag, $attribute)
	{
		return $this->xml->getElementsByTagName($tag)->item(0)->getAttribute($attribute);
	}

	/**
	 * Get dom document element by tag
	 *
	 * @param $tag
	 * @return mixed
	 */
	public function getElementByTag($tag)
	{
		return $this->xml->getElementsByTagName($tag)->item(0)->nodeValue;
	}

	/**
	 * Perform query on dom document
	 *
	 * @param $query
	 * @return mixed
	 */
	public function getXpathQuery($query)
	{
		return $this->xpath->query($query)->item(0);
	}

	/*
	 * Get the root XML node, if there isn't one, create it.
	 */
	private function getXMLRoot(){
		if(empty($this->xml)) {
			$this->init();
		}
	}

	/**
	 * Set xpath for document
	 *
	 * @param $xml
	 */
	private function setXPath()
	{
		$this->xpath = new \DOMXpath($this->xml);
		$this->xpath->registerNamespace('ns', $this->xml->documentElement->namespaceURI);
	}


	public function headerArray($array)
	{


		return $array;
	}
}
