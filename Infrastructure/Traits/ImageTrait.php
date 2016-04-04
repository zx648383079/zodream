<?php
namespace Zodream\Infrastructure\Traits;

use Zodream\Domain\Response\Image;
trait ImageTrait {
	/**
	 * 显示图片
	 *
	 * @param resource $img
	 */
	protected function image($img) {
		Image::show($img);
	}
}