<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Infrastructure\Http\Request;
use Zodream\Service\Controller\ModuleController as BaseController;

abstract class Controller extends BaseController {
    protected function getActionArguments($action, $vars = array()) {
        if (!Request::isCli()) {
            return parent::getActionArguments($action, $vars);
        }
        $args = Request::argv('arguments');
        if (empty($args)) {
            return parent::getActionArguments($action, Request::argv('options'));
        }
        array_shift($args);
        return $args;
    }
}