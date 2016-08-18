<?php
namespace Zodream\Infrastructure\Http;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/17
 * Time: 13:49
 */
use Zodream\Infrastructure\Disk\File;

class Curl {
    /**
     * @var resource
     */
    protected $curl;

    /**
     * @var array
     */
    protected $header;

    protected $result = null;

    public function __construct($url) {
        $this->curl = curl_init((string)$url);
    }

    /**
     * @param string|array $option
     * @param mixed $value
     * @return $this
     */
    public function setOption($option, $value = null) {
        if (is_array($option)) {
            curl_setopt_array($this->curl, $option);
        } else {
            curl_setopt($this->curl, $option, $value);
        }
        return $this;
    }

    /**
     * SET COOKIE FILE
     * @param string|File $file
     * @return $this
     */
    public function setCookieFile($file) {
        $file = (string)$file;
        return $this->setOption(CURLOPT_COOKIEJAR, $file)->setOption(CURLOPT_COOKIEFILE, $file);
    }

    /**
     * SET HEADER
     * @param array $args
     * @return Curl
     */
    public function setHeader(array $args) {
        foreach ($args as $key => $item) {
            $key = implode('-', array_map('ucfirst', explode('-', strtolower($key))));
            if (empty($item)) {
                continue;
            }
            if (is_array($item)) {
                $item = implode(',', $item);
            }
            $header[] = $key. ':'. $item;
        }
        return $this->setOption(CURLOPT_HTTPHEADER, $header);
    }

    /**
     * EXECUTE AND CLOSE
     * @return mixed|null
     */
    public function execute() {
        $this->result = curl_exec($this->curl);
        $this->header = curl_getinfo($this->curl);
        $this->header['error'] = curl_error($this->curl);
        $this->header['errorNo'] = curl_errno($this->curl);
        $this->close();
        return $this->result;
    }

    /**
     * GET STATUS
     * @return mixed
     */
    public function getStatus() {
        return $this->header['http_code'];
    }

    /**
     * GET RESULT
     * @return mixed|null
     */
    public function getResult() {
        if (is_resource($this->curl) && is_null($this->result)) {
            return $this->execute();
        }
        return $this->result;
    }

    /**
     * GET METHOD
     * @return string
     */
    public function get() {
        return $this->execute();
    }

    /**
     * POST METHOD
     * @param array $data
     * @return mixed|null
     */
    public function post($data = array()) {
        if (!is_string($data)) {
            $data = http_build_query($data);
        }
        return $this->setOption(CURLOPT_POST, 1)
            ->setOption(CURLOPT_POSTFIELDS, $data)
            ->execute();
    }

    /**
     * PUT METHOD
     * @return mixed|null
     */
    public function put() {
        return $this->setOption(CURLOPT_CUSTOMREQUEST, 'PUT')->execute();
    }

    /**
     * PATCH METHOD
     * @param array $data
     * @return mixed|null
     */
    public function patch($data = array()) {
        return $this->setOption(CURLOPT_CUSTOMREQUEST, 'PATCH')
            ->setOption(CURLOPT_POSTFIELDS, $data)
            ->execute();
    }

    /**
     * DELETE METHOD
     * @return mixed|null
     */
    public function delete() {
        return $this->setOption(CURLOPT_CUSTOMREQUEST, 'DELETE')->execute();
    }

    /**
     * DOWNLOAD FILE
     * @param string|File $file
     * @return null
     */
    public function download($file) {
        if (!$file instanceof File) {
            $file = new File($file);
        }
        $fp = fopen((string)$file, 'w');
        $this->curl->setOption(CURLOPT_FILE, $fp)->execute();
        fclose($fp);
        return $this->result;
    }

    /**
     * CLOSE
     * @return $this
     */
    public function close() {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
        return $this;
    }

    /**
     * GET ERROR
     * @return string
     */
    public function getError() {
        return $this->header['error'];
    }

    /**
     * GET ERROR NO
     * @return int
     */
    public function getErrorNo() {
        return $this->header['errorNo'];
    }

    public function __destruct() {
        if (is_resource($this->curl)) {
            $this->close();
        }
    }

    public function __toString() {
        return $this->get();
    }
}