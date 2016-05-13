<?php
namespace Zodream\Infrastructure\ObjectExpand;

use Zodream\Infrastructure\ObjectExpand\Xml\ArrayToXml;
use Zodream\Infrastructure\ObjectExpand\Xml\XmlToArray;

class XmlExpand {
    /**
     * @param string $xml
     * @param bool $is_array
     * @return array|object
     * @throws Xml\Exception
     */
    public static function decode($xml, $is_array = true) {
        if (!is_string($xml)) {
            return $xml;
        }
        if ($is_array === false) {
            return simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        return XmlToArray::createArray($xml);
    }

    /**
     * @param array $atgs
     * @return \DOMDocument
     */
    public static function encode(array $atgs) {
        return ArrayToXml::createXML('root', $atgs);
    }
}