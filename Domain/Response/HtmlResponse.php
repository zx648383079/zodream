<?php
namespace Zodream\Domain\Response;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/16
 * Time: 15:15
 */
use Zodream\Infrastructure\ObjectExpand\StringExpand;

class HtmlResponse extends BaseResponse {

    protected $message;

    protected $status = 200;

    public function __construct($message, $status = 200) {
        $this->message = $message;
        $this->status = $status;
    }

    public function sendContent() {
        ResponseResult::make(StringExpand::value($this->message), 'html', $this->status);
    }
}