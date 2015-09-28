<?php 
namespace App\Lib\Plugin;

class PLoader
{
	public static function findPlugins()
	{
		$plugins =array();
		foreach (get_declared_classes() as $class) {
			$reflectionClass = new ReflectionClass($class);
			if ($reflectionClass->implementsInterface('IBase')) {
				$plugins[] = $reflectionClass;
			}
		}
		
		return $plugins;
	}
	
	public static function menu()
	{
		$menu = array();
		foreach (findPlugins() as $plugin) {
			if ($plugin->hasMethod('getMenu')) {
				$reflectionMethod = $plugin->getMethod('getMenu');
				if($reflectionMethod->isStatic())
				{
					$items = $reflectionMethod->invoke(null);
				} else
				{
					$pluginInstance = $plugin->newInstance();
					$items= $reflectionMethod->invoke($pluginInstance);
				}
				$menu = array_merge($menu,$items);
			}
		}
		return $menu;
	}
}