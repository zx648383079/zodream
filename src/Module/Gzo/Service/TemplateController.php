<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Module\Gzo\Domain\GenerateModel;

class TemplateController extends Controller {

    public function indexAction() {
        return $this->show('index');
    }

    public function configAction() {
        return $this->show('config');
    }

    public function modelAction() {
        return $this->show('model');
    }

    public function controllerAction() {
        return $this->show('controller');
    }

    /**
     * 生成基控制器
     * @param $module
     * @return string
     */
    protected function baseController($module) {
        return $this->renderHtml('BaseController', array(
            'module' => $module
        ));
    }

    /**
     * 生成控制器
     * @param string $name
     * @param string $module
     * @return bool
     */
    protected function makeController($name, $module) {
        return $this->renderHtml('Controller', [
            'module' => $module,
            'name' => $name
        ]);
    }

    /**
     * 生成数据模型
     * @param string $name
     * @param string $table
     * @param array $columns
     * @param $module
     * @return bool
     */
    protected function makeModel($name, $table, array $columns, $module) {
        $data = GenerateModel::getFill($columns);
        return $this->renderHtml('Model', [
            'name' => $name,
            'table' => $table,
            'rules' => $data[1],
            'pk' => $data[0],
            'labels' => $data[2],
            'property' => $data[3],
            'module' => $module
        ]);
    }

    /**
     * 是有 _ 的表名采用驼峰法表示
     * @param string $table
     * @return string
     */
    protected function getName($table) {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $table)));
    }

    /**
     * 生成配置文件
     * @param array $configs
     * @return bool
     */
    public function makeConfig(array $configs) {
        return $this->renderHtml('config', array('data' => $configs));
    }


    /**
     * 生成主视图列表
     * @param string $name
     * @param array $columns
     * @return bool
     */
    protected function viewIndex($name, array $columns) {
        $data = [];
        foreach ($columns as $value) {
            $data[$value['Field']] = $value['Field'];
        }
        return $this->renderHtml('index', array(
            'data'   => $data,
            'name'   => $name
        ));
    }

    /**
     * 生成编辑视图
     * @param string $name
     * @param array $columns
     * @return bool
     */
    protected function viewEdit($name, array $columns) {
        $data = [];
        foreach ($columns as $value) {
            $data = $this->_viewForm($value);
        }
        return $this->renderHtml('add', array(
            'data'   => $data,
            'name'   => $name
        ));
    }

    /**
     * 生成单页查看视图
     * @param string $name
     * @param array $columns
     * @return bool
     */
    private function viewView($name, array $columns) {
        $data = [];
        foreach ($columns as $key => $value) {
            $data[] = $value['Field'];
        }
        return $this->renderHtml('view', array(
            'data'   => $data,
            'name'   => $name
        ));
    }

    /**
     * 视图中表单的生成
     * @param $value
     * @return string
     */
    private function _viewForm($value) {
        $required = null;
        if ($value['Null'] === 'NO') {
            $required = ", 'required' => true";
        }
        switch (explode('(', $value['Type'])[0]) {
            case 'enum':
                $str = rtrim(substr($value['Type'], strpos($value['Type'], '(')), ')');
                return "select('{$value['Field']}', [{$str}])";
            case 'text':
                return "textArea('{$value['Field']}', ['label' => '{$value['Field']}'{$required}])";
            case 'int':
            case 'varchar':
            case 'char':
            default:
                return "text('{$value['Field']}', ['label' => '{$value['Field']}'{$required}])";
        }
    }

}