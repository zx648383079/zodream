<?php
namespace Zodream\Domain\ThirdParty\WeChat;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/19
 * Time: 22:27
 */
use Zodream\Infrastructure\ThirdParty;

abstract class BaseWeChat extends ThirdParty {
    protected $name = 'wechat';



    /**
     * 数据XML编码
     * @param mixed $data 数据
     * @return string
     */
    protected function dataToXml($data) {
        $xml = '';
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = "item id=\"$key\"";
            $xml    .=  "<$key>";
            $xml    .=  (is_array($val) || is_object($val)) ? $this->dataToXml($val)  :$this->xmlSafeStr($val);
            list($key, ) = explode(' ', $key);
            $xml   .=  "</$key>";
        }
        return $xml;
    }

    protected function xmlSafeStr($str) {
        return '<![CDATA['. preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/", '', $str). ']]>';
    }

    /**
     * XML编码
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $attr 根节点属性
     * @param string $id   数字索引子节点key转换的属性名
     * @return string
     */
    protected function xmlEncode($data, $root = 'xml', $item = 'item', $attr = '', $id = 'id') {
        if (is_array($attr)) {
            $_attr = array();
            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $_attr);
        }
        $attr   = trim($attr);
        $attr   = empty($attr) ? '' : " {$attr}";
        $xml   = "<{$root}{$attr}>";
        $xml   .= $this->dataToXml($data, $item, $id);
        $xml   .= "</{$root}>";
        return $xml;
    }

    protected function jsonEncode(array $data) {
        $parts = array();
        $is_list = false;
        //Find out if the given array is a numerical array
        $keys = array_keys($data);
        $max_length = count($data) - 1;
        if (($keys [0] === 0) && ($keys[$max_length] === $max_length)) { //See if the first key is 0 and last key is length - 1
            $is_list = true;
            for($i = 0; $i < count($keys); $i ++) { //See if each key correspondes to its position
                if ($i != $keys[$i]) { //A key fails at position check.
                    $is_list = false; //It is an associative array.
                    break;
                }
            }
        }
        foreach ($data as $key => $value ) {
            if (is_array($value)) { //Custom handling for arrays
                $parts[] = (!$is_list ? '"' . $key . '":' :null) .$this->jsonEncode($value);
            } else {
                $str = '';
                if (! $is_list) {
                    $str = '"' . $key . '":';
                }
                //Custom handling for multiple data types
                if (!is_string($value) && is_numeric($value) && $value < 2000000000) {
                    $parts[] = $str . $value;
                    continue;
                }
                if ($value === false) {
                    $parts[] = $str . 'false';
                    continue;
                }
                if ($value === true) {
                    $parts[] = $str . 'true';
                    continue;
                }
                $parts[] = $str . '"' .addcslashes($value, "\\\"\n\r\t/"). '"';
            }
        }
        $json = implode ( ',', $parts );
        if ($is_list) {
            return '[' . $json . ']';
        }
        return '{' . $json . '}'; //Return associative JSON
    }

    /**
     * POST URL(BY NAME) DATA (JSON ENCODE ARRAY), THEN JSON DECODE
     * @param string $name
     * @param array $data
     * @return mixed
     */
    protected function jsonPost($name, $data = array()) {
        return $this->json($this->httpPost($this->getUrl($name),
            $this->jsonEncode($data)));
    }
}