<?php
namespace Zodream\Domain\Html\Bootstrap;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/30
 * Time: 9:57
 */
use Zodream\Domain\Html\VerifyCsrfToken;
use Zodream\Domain\Html\Widget;
use Zodream\Domain\Routing\Url;

class FormWidget extends Widget {
    protected $default = array(
        'data' => array(),
        'fields' => array(),
        'class' => 'form-horizontal'
    );
    
    protected function run() {
        $this->set('action', Url::to($this->get('action')));
        $content = '';
        $data = (array)$this->get('data');
        foreach ($this->get('fields', array()) as $key => $value) {
            if (!is_integer($key)) {
                $value['name'] = $key;
            }
            if (array_key_exists($key, $data)) {
                $value['value'] = $data[$key];
            }
            $content .= FieldWidget::show($value);
        }
        return Html::tag('form', 
            $content, 
            $this->get('id,class form-horizontal,role form,action,method POST')
        );
    }

    public static function begin($data = array(), $option = array()) {
        $instance = new static;
        $option['data'] = $data;
        $instance->set($option);
        $instance->csrf();
        return $instance;
    }
    
    public function csrf() {
        return $this->hidden('csrf', array(
            'value' => VerifyCsrfToken::get()
        ));
    }
    
    public function hidden($name, $option = array()) {
        return $this->input($name, __FUNCTION__, $option);
    }

    public function text($name, $option = array()) {
        return $this->input($name, __FUNCTION__, $option);
    }

    public function input($name, $type = 'text', $option = array()) {
        if (!in_array($type, array('radio', 'checkbox'))) {
            $this->_data['fields'][$name] = array(
                'type' => $type,
                'option' => $option
            );
            return $this;
        }
        if (!isset($this->_data['fields'][$name])) {
            $this->_data['fields'][$name] = array(
                'type' => $type,
                'label' => isset($option['label']) ? $option['label'] : null,
                'groups' => array()
            );
        }
        $this->_data['fields'][$name]['groups'][] = $option;
        return $this;
    }

    public function textArea($name, $option = array()) {
        $this->_data['fields'][$name] = array(
            'type' => 'textarea',
            'option' => $option
        );
        return $this;
    }

    public function email($name, $option = array()) {
        return $this->input($name, __FUNCTION__, $option);
    }

    public function number($name, $option = array()) {
        return $this->input($name, __FUNCTION__, $option);
    }

    public function password($name, $option = array()) {
        return $this->input($name, __FUNCTION__, $option);
    }

    public function checkbox($name, $option = array()) {
        return $this->input($name, __FUNCTION__, $option);
    }


    public function radio($name, $option = array()) {
        return $this->input($name, __FUNCTION__, $option);
    }

    public function select($name, array $items, $option = array()) {
        $this->_data['fields'][$name] = array(
            'type' => 'select',
            'items' => $items,
            'option' => $option
        );
        return $this;
    }

    /**
     * 列表框
     * @param $name 名称
     * @param $items 列表项
     * @param string $size 显示个数
     * @param bool $allowMultiple 是否允许多选
     * @param array $option
     * @return FormWidget
     */
    public function listBox($name, $items, $size = '10', $allowMultiple = false, $option = array()) {
        $option['size'] = $size;
        $option['multiple'] = $allowMultiple;
        return $this->select($name, $items, $option);
    }

    
    public function button($value = '提交', $type = 'submit', $option = array()) {
        $option['type'] = $type;
        $option['value'] = $value;
        $this->_data['fields'][] = array(
            'type' => 'button',
            'option' => $option,
        );
        return $this;
    }


    public function end() {
        return $this->show($this->get());
    }
    
    public function __call($name, $arguments) {
        return $this->input($arguments[0], $name, isset($arguments[1]) ? $arguments[1] : array());
    }
}