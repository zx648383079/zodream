<?php
namespace Zodream\Service\Controller;

abstract class ModuleController extends Controller {

    /**
     * Module config setting
     */
    public function configAction() {}

    /**
     * ajax 成功返回
     * @param null $data
     * @return \Zodream\Infrastructure\Http\Response
     */
    public function ajaxSuccess($data = null) {
        return $this->ajax([
            'code' => 0,
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * ajax 失败返回
     * @param string $message
     * @param int $code
     * @return \Zodream\Infrastructure\Http\Response
     */
    public function ajaxFailure($message = '', $code = 1) {
        return $this->ajax(array(
            'code' => $code,
            'status' => 'failure',
            'msg' => $message
        ));
    }
}