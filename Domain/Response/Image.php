<?php
namespace Zodream\Domain\Response;

class Image {
	/**
	 * 显示图片
	 * @param string $img
	 */
	public static function showPng($img) {
		header('Content-type:image/png');
		imagepng($img);
		imagedestroy($img);
	}


}