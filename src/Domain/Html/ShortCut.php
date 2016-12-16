<?php
namespace Zodream\Domain\Html;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/16
 * Time: 18:24
 */
use Zodream\Infrastructure\Interfaces\ExpertObject;

class ShortCut implements ExpertObject {
    
    protected $title;
    protected $url;

    /**
     * Excel constructor.
     * @param string $title
     * @param string $url
     */
    public function __construct($title, $url) {
        $this->title = $title;
        $this->url = $url;
    }


    /**
     * 导出假的excel文件
     * 
     */
    public function send() {
        echo '[InternetShortcut] 
URL=',$this->url,'
IDList=
[{000214A0-0000-0000-C000-000000000046}]
Prop3=19,2';
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->title .'.url';
    }

    /**
     * @return string
     */
    public function getType() {
        return 'exe';
    }
}