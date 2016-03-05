<?php
namespace Zodream\Domain\Response;

class Image {
	/**
	 * 显示图片
	 * @param string $img
	 */
	public static function show($img, $type = 'png') {
		ResponseResult::prepare(array('image', $type));
		imagepng($img);
		imagedestroy($img);
		ResponseResult::finish();
	}


}