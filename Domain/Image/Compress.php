<?php
/*打开图片*/
$src = "aa.jpg";
$info = getimagesize($src);
$type = image_type_to_extension($info[2],false);
$fun = "imagecreatefrom".$type;
$image = $fun($src);

/*操作图片*/
//1.内存中建立一个300,200真色彩图片
$image_thumb = imagecreatetruecolor(200,300);
//2.核心步骤，将原图复制到真色彩图片上
imagecopyresampled($image_thumb, $image, 0, 0, 0, 0, 200, 300, $info[0], $info[1]);
//3.销毁原始图片
imagedestroy($image);

/*输出图片*/
//浏览器
header("Content-type:".$info['mime']);
$fun = "image".$type;
$fun($image_thumb);
//保存图片
$fun($image_thumb,'aa_a.'.$type);
/*销毁图片*/
imagedestroy($image_thumb);