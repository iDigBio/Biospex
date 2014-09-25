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
	private $version = '1.0';

	/**
	 * Load xml string and return
	 *
	 * @param $input_xml
	 * @return \DOMDocument
	 * @throws XmlProcessException
	 */
	public function load($input_xml)
	{
		$this->xml = new \DOMDocument($this->version, $this->encoding);
		$this->xml->preserveWhiteSpace = false;

		$parsed = $this->xml->load($input_xml);
		if(!$parsed) {
			throw new XmlProcessException('[XMLProcess] Error parsing the XML string.');
		}

		$this->xpath = new \DOMXpath($this->xml);
		$this->xpath->registerNamespace('ns', $this->xml->documentElement->namespaceURI);
		$this->xpath->registerNamespace('php', 'http://php.net/xpath');
		$this->xpath->registerPhpFunctions(); // Allow all PHP functions

		return $this->xml->saveXML();
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
	 * @param bool $single
	 * @return mixed
	 */
	public function xpathQuery($query)
	{
		return $this->xpath->query($query);
	}

	/**
	 * Return first item of xpath query
	 *
	 * @param $query
	 * @return mixed
	 */
	public function xpathQueryOne($query)
	{
		return $this->xpathQuery($query)->item(0);
	}

	/**
	 * Convert an XML to Array
	 *
	 * @param $xml
	 * @return mixed
	 */
	public function &createArray($xml) {
		$array[$this->xml->documentElement->tagName] = self::convert($this->xml->documentElement);
		//$this->xml = null;    // clear the xml node in the class for 2nd time use.
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
}
