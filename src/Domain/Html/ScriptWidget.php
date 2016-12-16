<?php
namespace Zodream\Domain\Html;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/29
 * Time: 16:24
 */
use Zodream\Service\Routing\Url;
use Zodream\Infrastructure\Support\Html;

class ScriptWidget extends Widget {
    protected function run() {
        if (strtolower($this->get('kind')) === 'css') {
            return $this->css();
        }
        return $this->js();
    }

    protected function js() {
        if ($this->has('file')) {
            return Html::tag('script' , '', array(
                'type' => 'text/javascript',
                'src' => Url::to($this->get('file'))
            ));
        }
        return Html::tag('script', $this->get('source'));
    }

    protected function css() {
        if ($this->has('file')) {
            return Html::tag('link', '', array(
                'href' => Url::to($this->get('file')),
                'rel' => 'stylesheet'
            ));
        }
        return Html::tag('style', $this->get('source'));
    }
}