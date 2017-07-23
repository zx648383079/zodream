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
     * @param null $message
     * @return Response
     */
    public function jsonSuccess($data = null, $message = null) {
        if (!is_array($message)) {
            $message = ['message' => $message];
        }
        if ($data instanceof ArrayAble) {
            $data = $data->toArray();
        }
        return $this->json(array_merge(array(
            'code' => 200,
            'status' => 'success',
            'data' => $data
        ), $message));
    }

    /**
     * ajax 失败返回
     * @param string|array $message
     * @param int $code
     * @return Response
     */
    public function jsonFailure($message = '', $code = 400) {
        if (is_array($message)) {
            return $this->json(array(
                'code' => $code,
                'status' => 'failure',
                'errors' => $message
            ));
        }
        return $this->json(array(
            'code' => $code,
            'status' => 'failure',
            'message' => $message
        ));
    }

    protected function getActionName($action) {
        if (Request::expectsJson()) {
            return $this->getAjaxActionName($action);
        }
        return parent::getActionName($action);
    }

    protected function getAjaxActionName($action) {
        $arg = parent::getActionName($action).'Json';
        return method_exists($this, $arg) ? $arg : parent::getActionName($action);
    }

    public function hasMethod($action) {
        return array_key_exists($action, $this->actions())
            || method_exists($this, $this->getActionName($action));
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