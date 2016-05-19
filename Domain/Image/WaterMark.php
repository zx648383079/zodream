<?php
namespace Zodream\Domain\Image;

/**
 * 加水印
 * @author zx648
 *
 */
class WaterMark extends Image{

	const Top = 0;
	const RightTop = 1;
	const Right = 2;
	const RightBottom = 3;
	const Bottom = 4;
	const LeftBottom = 5;
	const Left = 6;
	const LeftTop = 7;
	const Center = 8;

	/**
	 * 根据九宫格获取坐标
	 * @param $direction
	 * @return array
	 */
	public function getPointByDirection($direction) {
		$width = $this->getWidth() / 3;
		$height = $this->getHeight() / 3;
		return array(
			$direction % 3 * $width,
			$direction / 3 * $height,
			$width,
			$height
		);
	}

	public function addTextByDirection($text, $direction = self::Top, $fontSize = 16, $color = '#000', $fontFamily = 5) {
		list($x, $y) = $this->getPointByDirection($direction);
		$this->addText($text, $x, $y, $fontSize, $color, $fontFamily);
	}

	/**
	 * 加文字
	 * @param string $text
	 * @param int $x
	 * @param int $y
	 * @param int $fontSize
	 * @param string $color
	 * @param int $fontFamily
	 * @param int $angle 如果 $fontFamily 为 int，则不起作用
	 */
	public function addText($text, $x = 0, $y = 0, $fontSize = 16, $color = '#000', $fontFamily = 5, $angle = 0) {
		$color = $this->getColorWithRGB($color);
		if (is_string($fontFamily) && is_file($fontFamily)) {
			imagettftext($this->image, $fontSize, $angle, $x, $y, $color, $fontFamily, $text);
		} else {
			imagestring($this->image, $fontFamily, $x, $y, $text, $color);
		}
	}

	public function addImageByDirection($imageFile, $direction = self::Top, $opacity = 50) {
		list($x, $y) = $this->getPointByDirection($direction);
		$this->addImage($imageFile, $x, $y, $opacity);
	}

	/**
	 * 加水印图片
	 * @param string $imageFile
	 * @param int $x
	 * @param int $y
	 * @param int $opacity 透明度，对png图片不起作用
	 */
	public function addImage($imageFile, $x = 0, $y = 0, $opacity = 50) {
		$image = new Image($imageFile);
		if ($image->getRealType() == 'png') {
			$this->copyFrom($image, 0, 0, $x, $y);
		} else {
			$this->copyAndMergeFrom($image, $x, $y, $opacity);
		}
	}
}