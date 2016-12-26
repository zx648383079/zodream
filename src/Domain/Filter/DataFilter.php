<?php
namespace Zodream\Domain\Filter;

use Zodream\Infrastructure\Interfaces\FilterObject;
use Zodream\Infrastructure\ObjectExpand\StringExpand;

defined('PHP_INT_MIN') || define('PHP_INT_MIN', 0);
defined('PHP_INT_MAX') || define('PHP_INT_MAX', 99999);

class DataFilter {
    protected static $filterMap = array(
        'confirm',
        'email',
        'float',
        'enum',
        'int',
        'number',
        'ip',
        'phone',
        'raw',
        'required',
        'same',
        'string',
        'time',
        'unique',
        'url'
    );

    protected static $error = array();
    /**
     * GET ERRORS WHO VALIDATE FAIL
     * @param string $key
     * @return array
     */
    public static function getError($key = null) {
        if (empty($key)) {
            return self::$error;
        }
        if (!array_key_exists($key, self::$error)) {
            return array();
        }
        return self::$error[$key];
    }

    public static function getFirstError($key) {
        if (!array_key_exists($key, self::$error)) {
            return null;
        }
        return current(self::$error[$key]);
    }
    private static function setError($key, $error) {
        if (!array_key_exists($key, self::$error)) {
            self::$error[$key] = array();
        }
        self::$error[$key][] = $error;
    }
    /**
     *
     *
     *
     *
     * @param string|array $args
     * @param string|array $option
     * example:
     * 1. 123, int
     * 2. [
     *      123,
     *      44
     * ], [
     *      int,
     *      string
     * ]
     * 3. [
     *      a => 123,
     *      b => fgff
     * ], [
     *      a => int|string,
     *      b => string
     * ]
     * 4. [
     *      a => 233,
     *      b => gg
     * ], a,int:|string;b,string
     * @return array|bool
     */
    public static function filter($args, $option) {
        return self::runFilterOrValidate($args, $option, false);
    }
    /**
     * 验证
     * @param array $args
     * @param array $option
     * @return bool
     */
    public static function validate($args, $option) {
        return self::runFilterOrValidate($args, $option, true);
    }

    protected static function runFilterOrValidate($args, $option, $isValidate = true) {
        $args = (array)$args;
        $option = (array)$option;
        self::$error = array();
        $filters = self::getFilters($option);
        if ($isValidate) {
            return self::runValidate($filters, $args);
        }
        return self::runFilter($filters, $args);
    }
    /**
     * @param array $filters
     * @param array $args
     * @return array
     */
    protected static function runFilter(array $filters, array $args) {
        $results = array();
        foreach ($filters as $key => $value) {
            $result = isset($args[$key]) ? $args[$key] : null;
            foreach ($value as $val) {
                /**	@param FilterObject $val */
                $result = $val->filter($result, $args);
            }
            $results[$key] = $result;
        }
        return $results;
    }
    /**
     * @param array $filters
     * @param array $args
     * @return bool
     */
    protected static function runValidate(array $filters, array $args) {
        $results = array();
        foreach ($filters as $key => $value) {
            $result = true;
            foreach ($value as $val) {
                /** @param FilterObject $val  */
                if (!$val->validate(isset($args[$key]) ? $args[$key] : null, $args)) {
                    self::setError($key, $val->getError());
                    $result = false;
                }
            }
            $results[$key] = $result;
        }
        return !in_array(false, $results);
    }

    protected static function getFilters($options) {
        if (is_string($options)) {
            $options = self::splitKeyAndFilters($options);
        }
        $filters = array();
        foreach ($options as $key => $value) {
            $filter = self::getFiltersFromOne($value);
            if (!empty($filter)) {
                $filters[$key] = $filter;
            }
        }
        return $filters;
    }

    protected static function splitKeyAndFilters($option) {
        $options = explode(';', $option);
        $results = array();
        foreach ($options as $key => $value) {
            $temp = explode(',', $value, 2);
            if (count($temp) == 1) {
                $results[$key] = $value;
            } else {
                $results[$temp[0]] = $temp[1];
            }
        }
        return $results;
    }
    protected static function getFiltersFromOne($option) {
        if (is_string($option)) {
            $option = explode('|', $option);
        }
        $filters = array();
        foreach ((array)$option as $value) {
            $filter = self::createFilter($value);
            if (!empty($filter)) {
                $filters[] = $filter;
            }
        }
        return $filters;
    }
    /**
     * @param string $arg
     * @return FilterObject
     */
    public static function createFilter($arg) {
        list($filter, $option) = StringExpand::explode($arg, ':', 2);
        $filter = strtolower($filter);
        if (in_array($filter, self::$filterMap)) {
            $class = 'Zodream\\Domain\\Filter\\Filters\\'.ucfirst($filter).'Filter';
            return new $class($option);
        }
        return null;
    }
}