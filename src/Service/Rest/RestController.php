<?php
namespace Zodream\Service\Rest;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/11/28
 * Time: 18:22
 */
use Zodream\Service\Controller\BaseController;
use Zodream\Service\Factory;
use Zodream\Infrastructure\Http\Requestquest;

abstract class RestController extends BaseController  {

    /**
     * @return string
     */
    protected function format() {
        return 'json';
    }

    protected function rules() {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }

    protected function beforeFilter($action) {
        $rules = $this->rules();
        if (!array_key_exists($action, $rules)) {
            return true;
        }
        if (in_array(Request::method(), $rules[$action])) {
            return true;
        }
        return $this->ajaxError('ERROE REQUEST METHOD!');
    }

    /**
     * @param array $data
     * @param int $statusCode
     * @return \Zodream\Infrastructure\Response
     */
    protected function ajax($data, $statusCode = 200) {
        Factory::response()->setStatusCode($statusCode);
        switch (strtolower($this->format())) {
            case 'xml':
                return Factory::response()->sendXml($data);
            case 'jsonp':
                return Factory::response()->sendJsonp($data);
            default:
                return Factory::response()->sendJson($data);
        }
    }

    /**
     * @param string $error
     * @param int $statusCode
     * @return \Zodream\Infrastructure\Response
     */
    protected function ajaxError($error, $statusCode = 400) {
        return $this->ajax([
            'error' => $error
        ], $statusCode);
    }
}