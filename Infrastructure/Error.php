<?php 
namespace Zodream\Infrastructure;
/**
* 错误信息类
* 
* @author Jason
*/
use Zodream\Domain\Response\ResponseResult;
use Zodream\Domain\Routing\UrlGenerator;
use Zodream\Infrastructure\ObjectExpand\TimeExpand;

class Error{

	public static function outByError($errno, $errstr, $errfile, $errline) {
		if (function_exists('error_clear_last')) {
			error_clear_last();
		}
		self::out($errstr, $errfile, $errline);
	}

	public static function outByShutDown() {
		$error = error_get_last();
		if (empty($error)) {
			return;
		}
		self::out($error['message'], $error['file'], $error['line']);
	}

	public static function out($error, $file = null, $line = null) {
		$errorInfo = "ERROR: {$error} , in {$file} on line {$line}, URL:".UrlGenerator::to();
		Log::out(TimeExpand::now('Y-m-d').'.txt', TimeExpand::format().':'.$errorInfo. "\r\n");
		if (!defined('DEBUG') || !DEBUG) {
			ResponseResult::sendError();
		}
		ResponseResult::make(
			$errorInfo
		);
	}
}