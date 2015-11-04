<?php 
namespace App\Lib\Html;

class HTree implements IBase {
	private static $tree;
	
	public static function make($data) {
		self::$tree = array();
		if (empty($data)) {
			echo '<ul>暂无数据！</ul>';
			return;
		}
		foreach ($data as $value) {
			if (isset( self::$tree[$value['pid']])) {
				self::$tree[$value['pid']][] = $value; 
			} else {
				self::$tree[$value['pid']] = array($value);
			}
		}
		ksort(self::$tree);
		
		self::makeTree(self::$tree[0], FALSE);
		
		    /*<ul>
				<li>a1</li>
				<li data="1">a2</li>
				<li>a3
					<span class="more">+</span>
					<ul>
					<li>b1</li>
					<li>b2</li>
					<li>b3
						<span class="more">+</span>
						<ul>
						<li>c1</li>
						<li>c2</li>
						<li>c3</li>
						<li>c4</li>
						</ul>
					</li>
					<li>b4</li>
					</ul>
				</li>
				<li>a4</li>
			</ul>*/
	}
	
	private static function makeTree($tree, $more = true) {
		if (isset($tree['pid'])) {
			echo '<li data="', $tree['id'], '">', $tree['title'];
			if (isset(self::$tree[$tree['id']])) {
				self::makeTree(self::$tree[$tree['id']]);
			}
			echo '</li>';	
			return;		
		}
		echo $more ? '<span class="more">+</span>' : '', '<ul>';
		
		foreach ($tree as $value) {
			self::makeTree($value);
		}
		echo '</ul>';
	}
}