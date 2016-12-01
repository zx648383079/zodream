<?php
namespace Zodream\Domain\Html\Bootstrap;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/30
 * Time: 14:43
 */
use Zodream\Domain\Html\Widget;

class PanelWidget extends Widget {
    protected $default = array(
        'class' => 'panel panel-default'
    );

    protected function run() {
        return Html::tag(
            'div',
            $this->getHead(). $this->getBody(). $this->getFoot(),
            $this->get('id,class'));
    }

    protected function getHead() {
        if (!$this->has('head')) {
            return null;
        }
        return '<div class="panel-heading">
			<h3 class="panel-title">'.$this->get('head').'</h3>
	  </div>';
    }

    protected function getBody() {
        if (!$this->has('body')) {
            return null;
        }
        return Html::tag('div', $this->get('body'), array(
            'class' => 'panel-body'
        ));
    }

    protected function getFoot() {
        if (!$this->has('foot')) {
            return null;
        }
        return Html::tag('div', $this->get('foot'), array(
            'class' => 'panel-footer'
        ));
    }
}