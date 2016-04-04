<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2015/12/25
 * Time: 16:08
 */
namespace Zodream\Domain\Filter;

use Zodream\Domain\Filter\Filters\NoneFilter;
use Zodream\Infrastructure\DomainObject\FilterObject;
use Zodream\Infrastructure\ObjectExpand\StringExpand;

defined('PHP_INT_MIN') or define('PHP_INT_MIN', 0);
defined('PHP_INT_MAX') or define('PHP_INT_MAX', 99999);

class DataFilter {
    private static $_filtersInstance = array(
        'confirm', 'email', 'float', 'int', 'number', 'ip', 'phone', 'raw', 'required', 'same', 'string', 'time', 'unique', 'url'
    );

	private static $_error = array();

	public static function getError() {
		return self::$_error;
	}

	private static function _setError($key, $error) {
		if (!array_key_exists($key, self::$_error)) {
			self::$_error[$key] = array();
		}
		self::$_error[$key][] = $error;
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
     */
    public static function filter($args, $option) {
		return self::_runFilterOrValidate($args, $option, false);
    }

	/**
	 * @param array $args
	 * @param array $option
	 * @return bool
	 */
    public static function validate($args, $option) {
    	return self::_runFilterOrValidate($args, $option, true);
    }
    
    private static function _runFilterOrValidate($args, $option, $isValidate = true) {
		self::$_error = array();
    	$filters = self::_getFilters($option);
    	if ($isValidate) {
    		return self::_runValidate($filters, $args);
    	}
    	return self::_runFilter($filters, $args);
    }

	/**
	 * @param array $filters
	 * @param array $args
	 * @return array
	 */
    private static function _runFilter(array $filters, array $args) {
    	$results = array();
    	foreach ($filters as $key => $value) {
    		$result = isset($args[$key]) ? $args[$key] : null;
    		foreach ($value as $val) {
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
    private static function _runValidate(array $filters, array $args) {
    	$results = array();
    	foreach ($filters as $key => $value) {
    		$result = true;
    		foreach ($value as $val) {
				if (!$val->validate(isset($args[$key]) ? $args[$key] : null, $args)) {
					self::_setError($key, $val->getError());
					$result = false;
				}
    		}
    		$results[$key] = $result;
    	}
    	return !in_array(false, $results);
    }
    
    private static function _getFilters($options) {
    	if (is_string($options)) {
    		$options = self::_splitKeyAndFilters($options);
    	}
    	foreach ($options as $key => &$value) {
    		$value = self::_getFiltersFromOne($value);
    	}
    	return $options;
    }
    
    private static function _splitKeyAndFilters($option) {
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

    private static function _getFiltersFromOne($option) {
    	if (is_string($option)) {
    		$option = explode('|', $option);
    	}
    	foreach ($option as $key => &$value) {
    		$value = self::_splitFilter($value);
    	}
    	return $option;
    }

	/**
	 * @param string $value
	 * @return FilterObject
	 */
    private static function _splitFilter($value) {
        list($filter, $option) = StringExpand::explode($value, ':', 2);
        $filter = strtolower($filter);
        if (in_array($filter, self::$_filtersInstance)) {
            $class = 'Zodream\\Domain\\Filter\\Filters\\'.ucfirst($filter).'Filter';
            return new $class($option);
        }
        return new NoneFilter();
    }
}