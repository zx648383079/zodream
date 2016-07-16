<?php
namespace Zodream\Domain\Html;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/16
 * Time: 18:24
 */
use Zodream\Infrastructure\DomainObject\ExpertObject;

class Excel implements ExpertObject {

    public $readerObj;
    public $charset = 'utf-8';
    
    protected $title;
    
    protected $firstRow;
    
    protected $data;

    /**
     * Excel constructor.
     * @param $title     string
     * @param $firstRow  array
     *          如：array('name'=>'名字', 'title' => '标题') 键名与后面的数组$data的子元素键名关联
     * @param $data      array
     */
    public function __construct($title = '', $firstRow = array(), $data = array()) {
        $this->title = $title;
        $this->firstRow = $firstRow;
        $this->data = $data;
    }

    /**
     * 输出切换编码
     * @param string $output
     * @return string
     */
    public function excelExportIconv($output){
        return iconv(self::$charset, 'GBK', $output);
    }

    /**
     * 导出假的excel文件
     * 
     */
    public function send() {
        if (!empty($this->title)) {
            echo self::excelExportIconv($this->title) . "\t\n";
        }
        /**
         *  第一行与后面的数据以键名关联
         */
        if (empty($this->firstRow) || !is_array($this->firstRow)) {
            if (!empty($this->data) && is_array($this->data)) {
                foreach ($this->data as $item) {
                    foreach ($item as $val) {
                        echo self::excelExportIconv($val) . "\t";
                    }
                    echo "\n";
                }
                echo "\n";
            }
            return;
        }
        //输出第一行内容
        foreach ($this->firstRow as $first) {
            echo self::excelExportIconv($first) . "\t";
        }
        echo "\n";

        if (empty($this->data) || !is_array($this->data)) {
            return;
        }
        foreach ($this->data as $item) {
            foreach ($this->firstRow as $_key => $_val) {
                if (isset($item[$_key])) {
                    echo self::excelExportIconv($item[$_key]) . "\t";
                } else {
                    echo self::excelExportIconv('') . "\t";
                }
            }
            echo "\n";
        }
    }
}