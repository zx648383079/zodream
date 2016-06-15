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
        foreach ($data['items'] as $key => $item) {
            $active = count($item) > 2;
            $title .= $this->getTitle($item['title'] ?? $item[0], $key, $active);
            $content .= $this->getContent($item['content'] ?? $item[0], $key, $active);
        }
        unset($data['items']);
        return Html::div(Html::ul($title, [
            'class' => 'nav nav-tabs',
            'role' => 'tablist'
        ].Html::div($content, [
                'class' => 'tab-content'
        ])), $data);
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