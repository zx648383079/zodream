<?php
namespace Zodream\Infrastructure\ObjectExpand;

use Zodream\Infrastructure\ObjectExpand\Xml\ArrayToXml;
use Zodream\Infrastructure\ObjectExpand\Xml\XmlToArray;

class XmlExpand {
    /**
     * @param string $xml
     * @param bool $isArray
     * @return array|object
     * @throws Xml\Exception
     */
    public static function decode($xml, $isArray = true) {
        if (!is_string($xml)) {
            return $xml;
        }
        if ($isArray === false) {
            return simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        return XmlToArray::createArray($xml);
    }

    /**
     * @param array $args
     * @param string $root
     * @return string
     */
    public static function encode(array $args, $root = 'root') {
        return ArrayToXml::createXML($root, $args)->saveXML();
    }
}