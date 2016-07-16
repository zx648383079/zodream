<?php
namespace Zodream\Domain\Response;

use Zodream\Domain\Image\Image;

class ImageResponse extends BaseResponse {

	/**
	 * @var Image
	 */
	protected $image;
	
	public function __construct($image, $type = 'png') {
		$this->image = $image instanceof Image ? $image : new Image($image);
		if (empty($this-$image->getRealType())) {
			$this->image->setRealType($type);
		}
	}

	/**
	 * 显示图片
	 */
	public function sendContent() {
		ResponseResult::prepare(array('image', $this->image->getRealType()));
		$this->image->saveAs();
		$this->image->close();
		ResponseResult::finish();
	}
}