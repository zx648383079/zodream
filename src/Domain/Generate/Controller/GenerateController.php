<?php
namespace Zodream\Domain\Generate\Controller;

use Zodream\Infrastructure\Http\Request;
use Zodream\Service\Controller\Controller;

class GenerateController extends Controller {
    protected function rules() {
        return [
            '*' => function() {
                return Request::ip() === 'unknown';
            }
        ];
    }

    public function indexAction() {
        return $this->show('index');
    }

    public function modelAction() {
        return $this->show('model');
    }

    public function crudAction() {
        return $this->show('crud');
    }

    public function controllerAction() {
        return $this->show('controller');
    }
}