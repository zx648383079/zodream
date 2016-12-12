<?php
use Zodream\Infrastructure\Security\Des;
class SecurityTest extends PHPUnit_Framework_TestCase {
    public function testDes() {
        $data = '我是有123456';
        $iv = '';//'w2wJCnct';
        $key = base64_decode('oScGU3fj8m/tDCyvsbEhwI91M1FcwvQqWuFpPoDHlFk=');
        $this->assertEquals(base64_encode(openssl_encrypt($data, 'DES-ECB', $key, OPENSSL_RAW_DATA, $iv)),
            base64_encode(mcrypt_encrypt(
                MCRYPT_RIJNDAEL_256,
                $key,
                $data,
                MCRYPT_MODE_ECB,
                $iv
            )));
    }
}