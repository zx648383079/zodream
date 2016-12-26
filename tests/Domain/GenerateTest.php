<?php
use Zodream\Domain\Generate\Model\GenerateModel;
class GenerateTest extends PHPUnit_Framework_TestCase {
    public function testSchema() {
        $table = GenerateModel::schema('aa')->table('zz');
        $table->set('id')->ai();
        $table->set('aa')->text();
        var_dump($table->getSql());
    }
}