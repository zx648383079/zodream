<?php
namespace Zodream\Infrastructure\Security;



class Rsa {
    protected $privateKey;

    protected $publicKey;

    public function setPublicKey($key) {
        $this->publicKey = openssl_pkey_get_public($key);
        return $this;
    }

    public function setPrivateKey($key) {
        $this->privateKey = openssl_pkey_get_private($key);
        return $this;
    }

    public function getPublicKey() {
        return $this->publicKey;
    }

    public function getPrivateKey() {
        return $this->privateKey;
    }

    /**
     * 私钥加密
     * @param string $data 要加密的数据
     * @return string 加密后的字符串
     */
    public function privateKeyEncrypt($data) {
        if (empty($this->privateKey)) {
            return false;
        }
        $encrypted = '';
        openssl_private_encrypt($data, $encrypted, $this->privateKey);
        return base64_encode($encrypted);
    }

    /**
     * 公钥加密
     * @param string $data 要加密的数据
     * @return string 加密后的字符串
     */
    public function publicKeyEncrypt($data) {
        if (empty($this->publicKey)) {
            return false;
        }
        $encrypted = '';
        openssl_public_encrypt($data, $encrypted, $this->privateKey);
        return base64_encode($encrypted);
    }

    /**
     * 用公钥解密私钥加密内容
     * @param string $data 要解密的数据
     * @return string 解密后的字符串
     */
    public function decryptPrivateEncrypt($data) {
        if (empty($this->privateKey)) {
            return false;
        }
        $encrypted = '';
        openssl_public_decrypt(base64_decode($data), $encrypted, $this->publicKey);
        return $encrypted;
    }
    /**
     * 用私钥解密公钥加密内容
     * @param string $data  要解密的数据
     * @return string 解密后的字符串
     */
    public function decryptPublicEncrypt($data) {
        if (empty($this->privateKey)) {
            return false;
        }
        $encrypted = '';
        openssl_private_decrypt(base64_decode($data), $encrypted, $this->publicKey);
        return $encrypted;
    }
}