<?php
namespace Zodream\Domain\Response;
/**
 * 跳转
 * @author Jason
 */
use Zodream\Domain\Routing\Url;

class RedirectResponse extends BaseResponse {

	protected $url;

	protected $time;

	protected $message;

	protected $status;

	/**
	 * RedirectResponse constructor.
	 * @param string|array $url 要跳转的网址
	 * @param int $time 停顿的时间 秒
	 * @param string $msg 显示的消息.
	 * @param int $status 显示的代码标志.
	 */
	public function __construct($url = null, $time = 0, $msg = null, $status = 200) {
		$this->url = Url::to($url);
		$this->time = $time;
		$this->message = $msg;
		$this->status = $status;
	}

	/**
	 * 跳转页面
	 *
	 * @access globe
	 *
	 *
	 */
	public function sendContent() {
		if (!headers_sent() && empty($msg)) {
			ResponseResult::sendRedirect($this->url, $this->time);
		}
		/*ResponseResult::sendError(array(
			'_extra' => "<meta http-equiv='Refresh' content='{$time};URL={$url}'>",
			'message' => empty($msg) ? "系统将在{$time}秒之后自动跳转到{$url}！" : $msg
		), $status, '跳转');*/
	}
}