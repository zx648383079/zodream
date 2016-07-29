<?php
namespace Zodream\Domain\Routing;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/16
 * Time: 23:17
 */
use Zodream\Infrastructure\DomainObject\RouterObject;

class RegexRouter extends BaseRouter implements RouterObject {

    /**
     * 生成url
     * @param string $file
     * @return string|array
     */
    public function to($file) {
        return $file;
    }
    
    public function run() {
        return $this->runByUri();
    }
}