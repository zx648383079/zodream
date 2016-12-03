<?php
namespace Zodream\Domain\Html\Bootstrap;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/4
 * Time: 10:44
 */
use Zodream\Domain\Html\Widget;
use Zodream\Service\Routing\Url;

class NavBarWidget extends Widget {

    protected $default = array(
        'brand' => null,
        'full' => false,
        'class' => ['default'],
        'tag' => 1, //防止一个页面有多个,
        'items' => []
    );

    protected function run() {
        $brand = $this->get('brand');
        if (!is_array($brand)) {
            $brand = ['label' => $brand];
        }
        return $this->nav(
            Html::container(
                $this->head(
                    $brand['label'],
                    array_key_exists('url', $brand) ? $brand['url'] : '#'
                ),
                $this->get('full')).
                $this->collapse($this->menu($this->get('items'))),
            $this->get('class'));
    }

    public function nav($content, $args = array()) {
        $class = ['navbar'];
        foreach ((array)$args as $item) {
            $class[] = 'navbar-'.$item;
        }
        return Html::div($content, array(
            'class' => implode(' ', $class)
        ));
    }

    public function head($brand, $url = '#') {
        $html = null;
        $tag = $this->get('tag', 1);
        if (!empty($brand)) {
            $html = Html::a($brand, $url, [
                'class' => 'navbar-brand'
            ]);
        }
        return <<<HTML
<div class="navbar-header">
  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#zodream-navbar-collapse-{$tag}" aria-expanded="false">
    <span class="sr-only">Toggle navigation</span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
  </button>
  {$html}
</div>
HTML;
    }

    public function collapse($content) {
        return Html::div($content, [
            'class' => 'collapse navbar-collapse',
            'id' => 'zodream-navbar-collapse-'.$this->get('tag', 1)
        ]);
    }

    public function menu(array $args, $left = true) {
        $content = null;
        foreach ($args as $item) {
            $content .= $this->menuItem($item);
        }
        return Html::ul($content, [
            'class' => 'nav navbar-nav'.($left ? null : ' navbar-right')
        ]);
    }

    public function menuItem($args) {
        if (!is_array()) {
            $args = ['label' => $args];
        }
        if (array_key_exists('option', $args)) {
            $args['option'] = array();
        }
        if (!array_key_exists('items', $args)) {
            return Html::li(
                Html::a(array_key_exists('label', $args) ? $args['label'] : null,
                    array_key_exists('url', $args) ? $args['url'] : '#'
                ),
                $args['option']);
        }
        $args['option']['class'] = (array_key_exists('class', $args['option']) ?
                $args['option']['class'] :
                null
            ).' dropdown';
        $content = null;
        foreach ($args['items'] as $item) {
            $content .= $this->menuItem($item);
        }
        return Html::li(
            Html::a($args['label'], '#', [
                'class' => 'dropdown-toggle',
                'data-toggle' => 'dropdown',
                'role' => 'button',
                'aria-haspopup' => true,
                'aria-expanded' => false
            ]). Html::ul($content, [
                'class' => 'dropdown-menu'
            ]),
            $args['option']);
    }

    public function form($content, $left = true) {
        return Html::form($content, [
            'class' => 'navbar-form navbar-'.($left ? 'left' : 'right')
        ]);
    }

    public function search($url, $tag = 'search') {
        $url = Url::to($url);
        return <<<HTML
<form class="navbar-form navbar-left" action="{$url}" method="get" role="search">
    <div class="form-group">
      <input type="text" class="form-control" name="{$tag}" placeholder="Search">
    </div>
    <button type="submit" class="btn btn-default">Submit</button>
</form>
HTML;

    }

}