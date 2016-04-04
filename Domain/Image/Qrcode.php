<?php
namespace Zodream\Domain\Image;
/**
 * 二维码
 *
 * @author Jason
 * @time 2015-12-1
 */
class Qrcode {
	/**
	 * 显示图片
	 *
	 * @access public
	 *
	 * @param array $value 内容
	 * @param array|string $logo 是否放logo
	 * @param array|int $level 容错率
	 * @param array|int $size 尺寸
	 * @return int 返回图片数据,
	 */
	public static function show($value , $logo ='',$level = 0 , $size = 6) {
		$errorCorrectionLevel = $level;//容错级别
		$matrixPointSize      = $size;//生成图片大小
		//生成二维码图片
		$QR = \QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize, 2);
	
		if (is_file($logo)) {
			//$QR = imagecreatefromstring(file_get_contents($QR));
			$logo           = imagecreatefromstring(file_get_contents($logo));
			$QR_width       = imagesx($QR);//二维码图片宽度
			$QR_height      = imagesy($QR);//二维码图片高度
			$logo_width     = imagesx($logo);//logo图片宽度
			$logo_height    = imagesy($logo);//logo图片高度
			$logo_qr_width  = $QR_width / 5;
			$scale          = $logo_width / $logo_qr_width;
			$logo_qr_height = $logo_height / $scale;
			$from_width     = ($QR_width - $logo_qr_width) / 2;
			//重新组合图片并调整大小
			imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
					$logo_qr_height, $logo_width, $logo_height);
		}
	
		return $QR;
	}
}