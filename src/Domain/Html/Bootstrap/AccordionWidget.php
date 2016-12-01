<?php
namespace Zodream\Domain\Html\Bootstrap;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/5
 * Time: 18:39
 */
use Zodream\Domain\Html\Widget;
use Zodream\Infrastructure\Error\Error;

class AccordionWidget extends Widget {

    protected $default = [
        'data' => [],
        'id' => 'accordion',
        'item' => [
            'title' => '',
            'body' => ''
        ] // \Closure
    ];

    protected function run() {
        $tags = $this->get('item');
        $data = $this->get('data');
        $content = null;
        foreach ($data as $key => $item) {
            if ($tags instanceof \Closure) {
                list($title, $body) = $tags($item, $key);
            } elseif (!is_array($tags) || count($tags) != 2) {
                Error::out('参数不正确！', __FILE__, __LINE__);
            } elseif (array_key_exists('title', $tags)) {
                $title = $item[$tags['title']];
                $body = $item[$tags['body']];
            } else {
                list($title, $body) = $tags;
            }
            $content .= $this->item($title, $body, $key);
        }
        return Html::div($content, [
            'class' => 'panel-group',
            'id' => $this->get('id'),
            'role' => 'tablist',
            'aria-multiselectable' => true
        ]);
    }

    public function item($title, $content, $index = 0) {
        return <<<HTML
<div class="panel panel-default">
<div class="panel-heading" role="tab" id="heading{$index}">
  <h4 class="panel-title">
    <a role="button" data-toggle="collapse" data-parent="{$this->get('id')}" href="#collapse{$index}" aria-expanded="true" aria-controls="collapse{$index}">
      {$title}
    </a>
  </h4>
</div>
<div id="collapse{$index}" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading{$index}">
  <div class="panel-body">
     {$content}
  </div>
</div>
</div>
HTML;

    }
}