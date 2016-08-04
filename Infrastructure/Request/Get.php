<?php
namespace Zodream\Infrastructure\Request;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/3
 * Time: 9:29
 */
use Zodream\Infrastructure\ObjectExpand\StringExpand;
use Zodream\Infrastructure\Request;

class Get extends BaseRequest {
    public function __construct() {
        if (!Request::isCli()) {
            $this->setValues($_GET);
            return;
        }
        // SET ARGV TO GET PARAM, IF NO '=' , VALUE IS '', YOU CAN USE IS_NULL JUDGE
        $args = Request::server('argv');
        unset($args[0]);
        foreach ($args as $arg) {
            list($key, $item) = StringExpand::explode($arg, '=', 2, '');
            $this->_data[$key] = $item;
        }
    }
}