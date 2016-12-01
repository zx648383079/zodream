<?php
namespace Zodream\Domain\Html\Bootstrap;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/3
 * Time: 21:08
 */
use Zodream\Domain\Html\Widget;

class RowWidget extends Widget {

    protected $default = array(
        'size' => ['md'],
        'columns' => [
            //'2' => ''
        ]
    );

    protected function run() {
        $content = null;
        $size = $this->get('size');
        foreach ($this->get('columns', array()) as $key => $item) {
            $content .= Html::col($item, $key, $size);
        }
        return Html::row($content);
    }
}