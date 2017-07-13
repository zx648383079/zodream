<?php
namespace Zodream\Service\Controller;

use Zodream\Infrastructure\Http\Request;

abstract class ModuleController extends Controller {

    /**
     * Module config setting
     */
    public function configAction() {}

    /**
     * ajax 成功返回
     * @param null $data
     * @return Response
     */
    public function ajaxSuccess($data = null) {
        return $this->ajax([
            'code' => 200,
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * ajax 失败返回
     * @param string|array $message
     * @param int $code
     * @return Response
     */
    public function ajaxFailure($message = '', $code = 400) {
        if (is_array($message)) {
            return $this->ajax(array(
                'code' => $code,
                'status' => 'failure',
                'errors' => $message
            ));
        }
        return $this->ajax(array(
            'code' => $code,
            'status' => 'failure',
            'message' => $message
        ));
    }

    protected function getActionName($action) {
        if (Request::isAjax()) {
            return $this->getAjaxActionName($action);
        }
        return parent::getActionName($action);
    }

    protected function getAjaxActionName($action) {
        $arg = parent::getActionName($action.'Ajax');
        return method_exists($this, $arg) ? $arg : $action;
    }

    protected function getViewFile($name = null) {
        if (is_null($name)) {
            $name = $this->action;
        }
        if (strpos($name, '/') !== 0) {
            $pattern = '.*?Service.(.+)'.APP_CONTROLLER;
            $name = preg_replace('/^'.$pattern.'$/', '$1', get_called_class()).'/'.$name;
        }
        return $name;
    }
}