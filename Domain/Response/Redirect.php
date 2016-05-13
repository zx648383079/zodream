<?php
namespace Zodream\Domain\Response;
/**
 * 跳转
 * @author Jason
 */
use Zodream\Domain\Routing\Url;

class Redirect {
	/**
	 * 跳转页面
	 *
	 * @access globe
	 *
	 * @param string|array $url 要跳转的网址
	 * @param int $time 停顿的时间 秒
	 * @param string $msg 显示的消息.
	 * @param int $status 显示的代码标志.
	 */
	public static function to($url = null, $time = 0, $msg = null, $status = 200) {
		$url = Url::to($url);
		// 当 $urls = null 时，防止$url 为空
		if (empty($url)) {
			$url = Url::to($url);
		}
		if (!headers_sent() && empty($msg)) {
			ResponseResult::sendRedirect($url, $time);
		}
		ResponseResult::sendError(array(
			'_extra' => "<meta http-equiv='Refresh' content='{$time};URL={$url}'>",
			'message' => empty($msg) ? "系统将在{$time}秒之后自动跳转到{$url}！" : $msg
		), $status, '跳转');
	}
}