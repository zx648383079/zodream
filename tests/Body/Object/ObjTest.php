<?php 

use App\Body\Object\Obj;

class ObjTest extends PHPUnit_Framework_TestCase {
	
	function __construct() {
		parent::__construct();
		$this->obj = new Obj();
	}
	
	private $obj;
	
	/**
	* @depend testSet
	*/
	public function testGet() {
		//$this->assertEquals($this->obj->get('a'), 2);
		$data = $this->obj->get();
		$this->assertEmpty($data);
	}
	
	public function testSet() {
		$this->obj->set(array(
			'a' => 1,
			'b' => 2,
			'c' => 'hhhh'
		));
	}
	
	/**
	* @depend testSet
	*/
	public function testHas() {
		$this->assertTrue($this->obj->has('d'));
	}
}