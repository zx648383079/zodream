<?php
namespace Zodream\Infrastructure\I18n;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/25
 * Time: 17:15
 */
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Disk\Directory;
use Zodream\Infrastructure\MagicObject;

abstract class I18n extends MagicObject {

    protected $fileName;

    protected $language = 'ZH-CN';

    /**
     * @var Directory
     */
    protected $directory;

    public function __construct() {
        $this->setDirectory(Config::getValue('i18n.directory', APP_DIR.'/lang/'));
    }


    public function setDirectory($directory) {
        if (!$directory instanceof Directory) {
            $directory = new Directory($directory);
        }
        $this->directory = $directory;
    }

    /**
     * 设置应用程序语言包
     * @param string $arg 语言
     */
    public function setLanguage($arg = null) {
        if (empty($arg)) {
            $language = Request::server('HTTP_ACCEPT_LANGUAGE', 'ZH-CN');
            preg_match('/[\w-]+/', $language, $match);
            $arg = $match[0];
        }
        $this->language = $arg;
    }

    /**
     * 获取语言类型
     *
     * @return string 返回语言,
     */
    public function getLanguage() {
        return $this->language;
    }

    public function translate($message, $param = [], $name = 'app') {
        if (empty($message)) {
            return $message;
        }
        if ($name != $this->fileName) {
            $this->fileName = $name;
            $this->reset();
        }
    }

    public function format($message, $param = []) {
        if ($param == []) {
            return $message;
        }
        $args = [];
        foreach ((array)$param as $key => $item) {
            $args['{'.$key.'}'] = $item;
        }
        // 替换
        return strtr($message, $args);
    }

    /**
     * 修改源
     */
    abstract public function reset();
}