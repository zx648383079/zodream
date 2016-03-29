<?php
namespace Zodream\Domain\Response;
/**
 * 跳转
 * @author Jason
 */
use Zodream\Domain\Routing\UrlGenerator;

class Redirect {
	/**
	 * 跳转页面
	 *
	 * @access globe
	 *
	 * @param string $url 要跳转的网址
	 * @param int $time 停顿的时间 秒
	 * @param string $msg 显示的消息.
	 * @param string $code 显示的代码标志.
	 */
	public static function to($urls = '', $time = 0, $msg = '', $code = '') {
		$url = '';
		foreach ((array)$urls as $value) {
			$url .= UrlGenerator::to($value);
		}
		if (empty($msg)) {
			$msg    = "系统将在{$time}秒之后自动跳转到{$url}！";
		}
		if (!headers_sent()) {
			ResponseResult::sendRedirect($url, $time);
		} else {
			$str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
			//self::$response->set('meta', $str);
		}
		exit();
	}
}