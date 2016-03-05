<?php
namespace Zodream\Domain\Template;

/**
 * 
 * 
{name}
{name.a}
{name,hh}

{for:name}
{for:name,value}
{for:name,key,value}
{for:name,key,value,length}
{for:name,key,value,>=h}
{/for}

{name=qq?v}
{name=qq?v:b}

{if:name=qq}
{if:name=qq,hh}
{if:name>qq,hh,gg}
{/if}
{else}
{elseif}

{switch:name}
{switch:name,value}
{case:hhhh>0}
{/switch}

{extend:file,hhh}

{name=value}
{arg,...=value,...}

' string
t f bool
0-9 int
[] array
 *
 *
 */
use Zodream\Infrastructure\ObjectExpand\ArrayExpand;

class Template {
	

	
	/**
	 * 提取使用方法 {extend:file,hhh} 必须带 : 
	 */
	public function extractFunction() {
		preg_replace_callback('/{([a-zA-Z0-9_]+):([^\{\}]*)}/is', array($this, '_functionCallback'), $subject);
	}
	
	private function _functionCallback($args) {
		
	}
	
	/**
	 * 提取lambda表达式 {name=qq?v:b}
	 */
	public function extractLambda() {
		preg_replace_callback('/{([^\{\}\?]+)\?([^\{\}\?]*)}/is', array($this, '_lambdaCallback'), $subject);
	}
	
	private function _lambdaCallback($args) {
		$values = explode(':', $args[2]);
	}
	
	/**
	 * 提取赋值语句  {arg,...=value,...}
	 */
	public function extractAssign() {
		preg_replace_callback('/{([^\{\}=:]+)=([^\{\}\?=:]*)}/is', array($this, '_assignCallback'), $subject);
	}
	
	private function _assignCallback($args) {
		array_combine(explode(',', $args[1]), explode(',', $args[2]));
		$this->set();
	}
	
	/**
	 * 提取 {name}
	 * {name.a}
	 * {name,hh}
	 */
	public function extractKey() {
		preg_replace_callback('/{([^\{\}]+)}/is', array($this, '_keyCallback'), $subject);
	}
	
	private function _keyCallback($args) {
		$keys = explode(',', $args[1]);
		return ArrayExpand::getChild($keys[0], $values, isset($keys[1]) ? $keys[1] : null);
	}
	
	
}