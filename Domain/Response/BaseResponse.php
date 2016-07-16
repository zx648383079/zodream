<?php
namespace Zodream\Domain\Response;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/16
 * Time: 12:55
 */
use Zodream\Infrastructure\DomainObject\ResponseObject;

class BaseResponse implements ResponseObject {
    
    protected $statusCode = 200;

    protected $version = '1.1';
    
    public function sendHeaders() {
        // headers have already been sent by the developer
        /*if (headers_sent()) {
            return $this;
        }

        if (!$this->hasHeader('Date')) {
            $this->setDate(\DateTime::createFromFormat('U', time()));
        }

        // headers
        foreach ($this->headers as $name => $values) {
            foreach ($values as $value) {
                header($name.': '.$value, false, $this->statusCode);
            }
        }

        // status
        header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText), true, $this->statusCode);

        // cookies
        foreach ($this->headers->getCookies() as $cookie) {
            setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
        }*/

        return $this;
    }
    
    public function sendContent() {
        
    }

    /**
     * 发送响应结果
     * @return boolean
     */
    public function send() {
        $this->sendHeaders();
        $this->sendContent();
        return true;
    }
}