<?php
namespace Zodream\Domain\Response;
/**
 * 导出类
 *
 * @author Jason
 */
class Export {
	public static function csv($text, $file) {
		ResponseResult::sendContentType('csv');
		ResponseResult::sendContentDisposition($file.'.csv');
		ResponseResult::sendCacheControl('must-revalidate,post-check=0,pre-check=0');
		ResponseResult::sendExpires(0);
		ResponseResult::sendPragma('public');
		echo $text;
	}
	
	
	public static $readerObj;
	public static $charset = 'utf-8';

	/**
	 * 输出切换编码
	 * @param string $output
	 * @return string
	 */
	public static function excelExportIconv($output){
		return iconv(self::$charset, 'GBK', $output);
	}
	
	/**
	 * 导出假的excel文件
	 * @param $fileName  string
	 * @param $title     string
	 * @param $firstRow  array
	 *          如：array('name'=>'名字', 'title' => '标题') 键名与后面的数组$data的子元素键名关联
	 * @param $data      array
	 */
	public static function exportFile(
		$fileName, $title = '', $firstRow = array(), $data = array()) {
		ResponseResult::sendContentType('application/vnd.ms-execl');
		ResponseResult::sendContentDisposition($fileName . '.xls');
		ResponseResult::sendPragma('no-cache');
		ResponseResult::sendExpires(0);
		if (!empty($title)) {
			echo self::excelExportIconv($title) . "\t\n";
		}
		/**
		 *  第一行与后面的数据以键名关联
		 */
		if (empty($firstRow) || !is_array($firstRow)) {
			if (!empty($data) && is_array($data)) {
				foreach ($data as $item) {
					foreach ($item as $val) {
						echo self::excelExportIconv($val) . "\t";
					}
					echo "\n";
				}
				echo "\n";
			}
			return;
		}
		//输出第一行内容
		foreach ($firstRow as $first) {
			echo self::excelExportIconv($first) . "\t";
		}
		echo "\n";

		if (empty($data) || !is_array($data)) {
			return;
		}
		foreach ($data as $item) {
			foreach ($firstRow as $_key => $_val) {
				if (isset($item[$_key])) {
					echo self::excelExportIconv($item[$_key]) . "\t";
				} else {
					echo self::excelExportIconv('') . "\t";
				}
			}
			echo "\n";
		}
	}
}