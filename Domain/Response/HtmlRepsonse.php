<?php
namespace Zodream\Domain\Response;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/16
 * Time: 15:15
 */
class HtmlResponse extends BaseResponse {

    protected $message;

    protected $status = 200;

    public function __construct($message, $status = 200) {
        $this->message = $message;
        $this->status = $status;
    }

    public function sendContent() {
        ResponseResult::make($this->message, 'html', $this->status);
    }
}