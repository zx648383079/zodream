<?php
namespace Zodream\Infrastructure\Base;

/**
 * THIS IS CONFIG TO CREATE OBJECT
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/9/1
 * Time: 14:13
 */
use Zodream\Infrastructure\Config;
abstract class ConfigObject {

    /**
     * CONFIGS
     * @var array
     */
    protected $configs = [];
    /**
     * KEY IN CONFIG
     * @var string
     */
    protected $configKey;

    /**
     * SET CONFIGS
     * @param array $args
     * @return $this
     */
    public function setConfigs(array $args) {
        $this->configs = array_merge($this->configs, $args);
        return $this;
    }

    public function loadConfigs() {
        if (!empty($this->configKey)) {
            $this->setConfigs(Config::getValue($this->configKey));
        }
    }


}