<?php
use Zodream\Domain\Filter\DataFilter;
class FilterTest extends PHPUnit_Framework_TestCase {
    public function testValidate() {
        $this->assertEquals(true, DataFilter::validate(1, 'int'));
        $this->assertEquals(true, DataFilter::validate([
            1,
            '123@123.com'
        ], [
            'int',
            'email'
        ]));
        $this->assertEquals(true, DataFilter::validate([
            'a' => 555,
            'b' => '123@123.com'
        ], [
            'b' => 'email',
            'a' => 'int:2-666'
        ]));
        $this->assertEquals(true, DataFilter::validate([
            'a' => 555,
            'b' => '123'
        ], [
            [['a', 'b'], 'int:2-666']
        ]));
    }
}