<?php
use Zodream\Service\RequestUri;
class RequestUriTest extends PHPUnit_Framework_TestCase {
    public function testSetPath() {
        $uri = new RequestUri();
        $uri->setPath('/index.php/path');
        $this->assertEquals('/index.php', $uri->getScript());
        $this->assertEquals('/path', $uri->getPath());
    }
}