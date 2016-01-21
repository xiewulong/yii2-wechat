<?php

namespace yii\wechat\components;

use DOMCdataSection;
use DOMDocument;
use DOMElement;
use DOMText;
use yii\base\Arrayable;
use yii\helpers\StringHelper;

class XmlResponseFormatter extends \yii\web\XmlResponseFormatter {

    public $encoding = 'utf-8';

	public $rootTag = 'xml';

	protected function buildXml($element, $data) {
		if(is_object($data)) {
			$child = new DOMElement(StringHelper::basename(get_class($data)));
			$element->appendChild($child);
			if($data instanceof Arrayable) {
				$this->buildXml($child, $data->toArray());
			} else {
				$array = [];
				foreach($data as $name => $value) {
					$array[$name] = $value;
				}
				$this->buildXml($child, $array);
			}
		} else if(is_array($data)) {
			foreach ($data as $name => $value) {
				if(is_int($name) && is_object($value)) {
					$this->buildXml($element, $value);
				} else if(is_array($value) || is_object($value)) {
					$child = new DOMElement(is_int($name) ? $this->itemTag : $name);
					$element->appendChild($child);
					$this->buildXml($child, $value);
				} else {
					$child = new DOMElement(is_int($name) ? $this->itemTag : $name);
					$element->appendChild($child);
					//$child->appendChild(new DOMText((string) $value));
					$child->appendChild(is_string($value) ? new DOMCdataSection((string) $value) : new DOMText((string) $value));
				}
			}
		} else {
			//$element->appendChild(new DOMText((string) $data));
			$element->appendChild(is_string($data) ? new DOMCdataSection((string) $data) : new DOMText((string) $data));
		}
	}

	public static function formatData($data) {
		$XmlResponseFormatter = new static;
		$dom = new DOMDocument($XmlResponseFormatter->version, $XmlResponseFormatter->encoding);
		$root = new DOMElement($XmlResponseFormatter->rootTag);
		$dom->appendChild($root);
		$XmlResponseFormatter->buildXml($root, $data);

		return $dom->saveXML();
	}

}
