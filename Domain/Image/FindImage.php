<?php
<?php

class ImageHash {
	 
	const FILE_NOT_FOUND = '-1';
	const FILE_EXTNAME_ILLEGAL = '-2';
	 
	private function __construct() {}
	 
	public static function run($src1, $src2) {
		 
		static $self;
		if(!$self) $self = new static;
		 
		if(!is_file($src1) || !is_file($src2)) exit(self::FILE_NOT_FOUND);

		$hash1 = $self->getHashValue($src1);
		$hash2 = $self->getHashValue($src2);
		 
		if(strlen($hash1) !== strlen($hash2)) return false;
		 
		$count = 0;
		$len = strlen($hash1);
		for($i = 0; $i < $len; $i++) if($hash1[$i] !== $hash2[$i]) $count++;
		return $count <= 10 ? true : false;

	}
	 
	public function getImage($file) {
		 
		$extname = pathinfo($file, PATHINFO_EXTENSION);
		if(!in_array($extname, ['jpg','jpeg','png','gif'])) exit(self::FILE_EXTNAME_ILLEGAL);
		$img = call_user_func('imagecreatefrom'. ( $extname == 'jpg' ? 'jpeg' : $extname ) , $file);
		return $img;
		 
	}
	 
	public function getHashValue($file) {
		 
		$w = 8;
		$h = 8;
		$img = imagecreatetruecolor($w, $h);
		list($src_w, $src_h) = getimagesize($file);
		$src = $this->getImage($file);
		imagecopyresampled($img, $src, 0, 0, 0, 0, $w, $h, $src_w, $src_h);
		imagedestroy($src);
		 
		$total = 0;
		$array = array();
		for( $y = 0; $y < $h; $y++) {
			for ($x = 0; $x < $w; $x++) {
				$gray = (imagecolorat($img, $x, $y) >> 8) & 0xFF;
				if(!isset($array[$y])) $array[$y] = array();
				$array[$y][$x] = $gray;
				$total += $gray;
			}
		}
		 
		imagedestroy($img);
		 
		$average = intval($total / ($w * $h * 2));
		$hash = '';
		for($y = 0; $y < $h; $y++) {
			for($x = 0; $x < $w; $x++) {
				$hash .= ($array[$y][$x] >= $average) ? '1' : '0';
			}
		}
		 
		var_dump($hash);
		return $hash;
		 
	}
	 
}