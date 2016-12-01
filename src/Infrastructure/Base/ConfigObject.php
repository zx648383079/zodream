<?php
namespace Zodream\Infrastructure\Base;

/**
 * THIS IS CONFIG TO CREATE OBJECT
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/9/1
 * Time: 14:13
 */
use Zodream\Infrastructure\Traits\ConfigTrait;

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

    use ConfigTrait;


}