<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Infrastructure\Http\Request;
use Zodream\Service\Controller\Controller as BaseController;

abstract class Controller extends BaseController {

    public function init() {
        if (Request::ip() !== 'unknown') {
            $this->redirect('/');
        }
    }
}