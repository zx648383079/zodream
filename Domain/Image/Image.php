<?php
namespace Zodream\Domain\Image;

use Zodream\Infrastructure\ObjectExpand\ArrayExpand;
class Image{
	
	protected $allowTypes = array(
		'jpeg' => array(
				'jpg',
				'jpeg',
				'jpe',
				'jpc',
				'jpeg2000',
				'jp2',
				'jb2'
		),
		'webp' => 'webp',
		'png' => 'png',
		'gif' => 'gif',
		'wbmp' => 'wbmp',
		'xbm' => 'xbm',
		'gd' => 'gd',
		'gd2' => 'gd2'
	);
	
	protected $file;
	
	protected $width;
	
	protected $height;
	
	protected $type;
	
	protected $realType;

	/**
	 * @var resource
	 */
	public $image;
	
	public function __construct($file = null) {
		if (null != $file) {
			$this->open($file);
		}
	}
	
	public function open($file) {
		if ($this->check($file)) {
			$imageInfo = getimagesize($file);
			$this->width = $imageInfo[0];
			$this->height = $imageInfo[1];
			$this->type = image_type_to_extension($imageInfo[2], false);
			$this->setRealType($this->type);
			if (false !== $this->realType) {
				$this->image = call_user_func('imagecreatefrom'.$this->realType, $file);
			}
		}
	}
	
	public function create($width, $height, $type = 'jpeg') {
		$this->type = $type;
		$this->realType = ArrayExpand::inArray($this->type, $this->allowTypes);
		if ($this->type == 'gif') {
			$this->image = imagecreate($width, $height);
		} else {
			$this->image = imagecreatetruecolor($width, $height);
		}
		$this->height = $height;
		$this->width = $width;
	}
	
	public function setImage($image) {
		if (is_string($image)) {
			$this->image = imagecreatefromstring($image);
		} elseif(is_resource($image)) {
			$this->image = $image;
		}
		$this->height = imagesy($this->image);
		$this->width = imagesx($image);
	}
	
	public function getHeight() {
		return $this->height;
	}
	
	public function getWidth() {
		return $this->width;
	}
	
	public function getRealType() {
		return $this->realType;
	}
	
	public function setRealType($type) {
		if (!empty($type)) {
			$this->realType = ArrayExpand::inArray($type, $this->allowTypes);
		}
	}
	
	public function getSize() {
		return array(
				$this->getWidth(),
				$this->getHeight()
		);
	}

	/**
	 * 获取文字转化成图片的尺寸
	 * @param string $text
	 * @param int $fontSize
	 * @param int $angle 角度
	 * @param int|string $fontFamily
	 * @return \number[] [宽, 高]
	 */
	public function getTextSize($text, $fontSize = 16, $angle = 0, $fontFamily = 5) {
		$textInfo = imagettfbbox($fontSize, $angle, $fontFamily, $text);
		return array(
				$textInfo[2] - $textInfo[6],
				$textInfo[3]-$textInfo[7]
		);
	}
	
	public function getRGB($color = '#000000') {
		if (count_chars($color) == 4) {
			$red = substr($color, 1, 1);
			$green = substr($color, 2, 1);
			$blue = substr($color, 3, 1);
			$red .= $red;
			$green .= $green;
			$blue .= $blue;
		} else {
			$red = substr($color, 1, 2);
			$green = substr($color, 3, 2);
			$blue = substr($color, 5, 2);
		}
		return array(
				hexdec($red),
				hexdec($green),
				hexdec($blue)
		);
	}
	
	public function getColorWithRGB($color) {
		if (func_num_args() == 1 && is_int($color)) {
			return $color;
		}
		if (is_string($color)) {
			$color = $this->getRGB($color);
		} elseif(func_num_args() == 3) {
			$color = func_get_args();
		}
		return imagecolorallocate($this->image, $color[0], $color[1], $color[2]);
	}
	
	public function getHashValue() {
		$w = 8;
		$h = 8;
		$image = new Image();
		$image->create($w, $h);
		$image->copyFromWithResampling($this);
		$total = 0;
		$array = array();
		for( $y = 0; $y < $h; $y++) {
			for ($x = 0; $x < $w; $x++) {
				$gray = (imagecolorat($image->image, $x, $y) >> 8) & 0xFF;
				if(!isset($array[$y])) $array[$y] = array();
				$array[$y][$x] = $gray;
				$total += $gray;
			}
		}
		$image->close();
		$average = intval($total / ($w * $h * 2));
		$hash = '';
		for($y = 0; $y < $h; $y++) {
			for($x = 0; $x < $w; $x++) {
				$hash .= ($array[$y][$x] >= $average) ? '1' : '0';
			}
		}
		return $hash;
	}

	/**
	 * 复制图片的一部分
	 * @param Image $srcImage
	 * @param int $srcX
	 * @param int $srcY
	 * @param int $x
	 * @param int $y
	 * @param int $srcWidth 如果是0则取原图的宽
	 * @param int $srcHeight 如果是0则取原图的高
	 */
	public function copyFrom(Image $srcImage, $srcX = 0, $srcY = 0, $x = 0, $y = 0, $srcWidth = 0, $srcHeight = 0) {
		if (empty($srcWidth)) {
			$srcWidth = $srcImage->getWidth();
		}
		if (empty($srcHeight)) {
			$srcHeight = $srcImage->getHeight();
		}
		imagecopy($this->image, $srcImage->image, $x, $y, $srcX, $srcY, $srcWidth, $srcHeight);
	}

	/**
	 * 从。。。复制一部分图片并融入本图片
	 * @param Image $srcImage
	 * @param int $x
	 * @param int $y
	 * @param int $opacity 透明度 0-100
	 * @param int $srcX
	 * @param int $srcY
	 * @param int $srcWidth
	 * @param int $srcHeight
	 */
	public function copyAndMergeFrom(Image $srcImage, $x = 0, $y = 0, $opacity = 50, $srcX = 0, $srcY = 0, $srcWidth = 0, $srcHeight = 0) {
		if (empty($srcWidth)) {
			$srcWidth = $srcImage->getWidth();
		}
		if (empty($srcHeight)) {
			$srcHeight = $srcImage->getHeight();
		}
		imagecopymerge($this->image, $srcImage->image, $x, $y, $srcX, $srcY, $srcWidth, $srcHeight, $opacity);
	}

	/**
	 * 用灰度从。。。复制一部分图片并融入本图片
	 * @param Image $srcImage
	 * @param int $x
	 * @param int $y
	 * @param int $opacity 透明度 0-100
	 * @param int $srcX
	 * @param int $srcY
	 * @param int $srcWidth
	 * @param int $srcHeight
	 */
	public function copyAndMergeFromWithGray(Image $srcImage, $x = 0, $y = 0, $opacity = 50, $srcX = 0, $srcY = 0, $srcWidth = 0, $srcHeight = 0) {
		if (empty($srcWidth)) {
			$srcWidth = $srcImage->getWidth();
		}
		if (empty($srcHeight)) {
			$srcHeight = $srcImage->getHeight();
		}
		imagecopymergegray($this->image, $srcImage->image, $x, $y, $srcX, $srcY, $srcWidth, $srcHeight, $opacity);
	}

	/**
	 * 使用重绘复制并调整图片的一部分
	 * @param Image $srcImage 本图
	 * @param int $srcX
	 * @param int $srcY
	 * @param int $x
	 * @param int $y
	 * @param int $srcWidth 如果是0则取原图的宽
	 * @param int $srcHeight 如果是0则取原图的高
	 * @param int $width 如果是0则取本图的宽
	 * @param int $height 如果是0则取本图的高
	 */
	public function copyFromWithResampling(Image $srcImage, $srcX = 0, $srcY = 0, $x = 0, $y = 0, $srcWidth = 0, $srcHeight = 0, $width = 0, $height = 0) {
		if (empty($srcWidth)) {
			$srcWidth = $srcImage->getWidth();
		}
		if (empty($srcHeight)) {
			$srcHeight = $srcImage->getHeight();
		}
		if (empty($width)) {
			$width = $this->getWidth();
		}
		if (empty($height)) {
			$height = $this->getHeight();
		}
		imagecopyresampled($this->image, $srcImage->image, $x, $y, $srcX, $srcY, $width, $height, $srcWidth, $srcHeight);
	}

	/**
	 * 复制并调整图片的一部分
	 * @param Image $srcImage 本图
	 * @param int $srcX
	 * @param int $srcY
	 * @param int $x
	 * @param int $y
	 * @param int $srcWidth 如果是0则取原图的宽
	 * @param int $srcHeight 如果是0则取原图的高
	 * @param int $width 如果是0则取本图的宽
	 * @param int $height 如果是0则取本图的高
	 */
	public function copyFromWithResize(Image $srcImage, $srcX = 0, $srcY = 0, $x = 0, $y = 0, $srcWidth = 0, $srcHeight = 0, $width = 0, $height = 0) {
		if (empty($srcWidth)) {
			$srcWidth = $srcImage->getWidth();
		}
		if (empty($srcHeight)) {
			$srcHeight = $srcImage->getHeight();
		}
		if (empty($width)) {
			$width = $this->getWidth();
		}
		if (empty($height)) {
			$height = $this->getHeight();
		}
		imagecopyresized($this->image, $srcImage->image, $x, $y, $srcX, $srcY, $width, $height, $srcWidth, $srcHeight);
	}
	
	public function scale() {
		
	}
	
	public function save() {
		if (!empty($this->file)) {
			call_user_func('image'.$this->realType, $this->image, $this->file);
		}
	}
	
	public function saveAs($output, $type = null) {
		$this->setRealType($type);
		if (!empty($output)) {
			call_user_func('image'.$this->realType, $this->image, $output);
		}
	}
	
	public function close() {
		if (!empty($this->image)) {
			imagedestroy($this->image);
		}
		unset($this);
	}

	/**
	 * 验证图片是否合法
	 * @param string $file
	 * @return bool
	 */
	protected function check($file) {
		return is_file($file) && getimagesize($file) && extension_loaded('gd');
	}
	
}