<?php
namespace Zodream\Domain\Response;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/16
 * Time: 12:53
 */

class BadResponse extends HtmlResponse {

    public function __construct($message = '/(ㄒoㄒ)/~~PAGE NOT FIND!', $status = 404) {
        parent::__construct($message, $status);
    }
    
}