<?php
namespace Zodream\Domain\Html\Bootstrap;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/3
 * Time: 21:08
 */
use Zodream\Domain\Html\Widget;
use Zodream\Infrastructure\Html;

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
            $content .= $this->col($item, $key, $size);
        }
        return $this->row($content);
    }

    /**
     * 一行
     * @param string $content
     * @return string
     */
    public function row($content = null) {
        return Html::div($content, array(
            'class' => 'row'
        ));
    }

    /**
     * 一格
     * @param string $content
     * @param int $size
     * @param array $types
     * @return string
     */
    public function col($content, $size = 1, $types = ['md']) {
        $class = [];
        foreach ((array)$types as $item) {
            $class[] = 'col-'.$item.'-'.$size;
        }
        return Html::div($content, array(
            'class' => implode(' ', $class)
        ));
    }
}