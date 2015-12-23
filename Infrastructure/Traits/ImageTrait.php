<?php
namespace Zodream\Infrastructure\Traits;

use Zodream\Infrastructure\Response\Image;
trait ImageTrait {
	/**
	 * 显示图片
	 *
	 * @param $img
	 */
	protected function image($img) {
		Image::view($img);
	}
}