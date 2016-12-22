<?php
namespace Zodream\Domain\Html\Bootstrap;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/15
 * Time: 18:55
 */
use Zodream\Domain\Html\Widget;

class TabWidget extends Widget {

    protected $default = [
        'items' => [
            [
                'title' => '',
                'content' => '',
                'active' => true
            ]
        ]
    ];

    protected function run() {
        $data = $this->get();
        $title = $content = null;
        $hasActive = false;
        foreach ($data['items'] as $key => $item) {
            list($t, $c, $a) = $this->convert($item, $key);
            if ($hasActive) {
                $a = false;
            }
            if ($a) {
                $hasActive = true;
            }
            $title .= $this->getTitle($t, $key, $a);
            $content .= $this->getContent($c, $key, $a);
        }
        unset($data['items']);
        return Html::div(Html::ul($title, [
                'class' => 'nav nav-tabs',
                'role' => 'tablist'
            ]).Html::div($content, [
                'class' => 'tab-content'
            ]), $data);
    }

    /**
     * @param $item
     * @param $key
     * @return array
     */
    protected function convert($item, $key) {
        if (!is_array($item)) {
            return [$key, $item, false];
        }
        if (array_key_exists('title', $item)) {
            return [$item['title'], $item['content'], isset($item['active']) && $item['active']];
        }
        if (array_key_exists('content', $item)) {
            return [$key, $item['content'], isset($item['active']) && $item['active']];
        }
        if (is_integer($key)) {
            return [$item[0], $item[1], isset($item[2]) && $item[2]];
        }
        return [$key, $item[0], $item[1]];
    }

    protected function getTitle($title, $id, $isActive = false) {
        $active = null;
        if ($isActive) {
            $active = ' class="active"';
        }
        $html = <<<HTML
<li role="presentation"{$active}><a href="#tab{$id}" aria-controls="tab{$id}" role="tab" data-toggle="tab">{$title}</a></li>
HTML;
        return $html;
    }

    protected function getContent($content, $id, $isActive = false) {
        $active = null;
        if ($isActive) {
            $active = ' active';
        }
        $html = <<<HTML
<div role="tabpanel" class="tab-pane{$active}" id="tab{$id}">{$content}</div>
HTML;
        return $html;
    }
}