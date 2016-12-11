<?php
namespace Zodream\Domain\Filter;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/15
 * Time: 21:36
 */
use Zodream\Domain\Model\Model;
use Zodream\Infrastructure\Interfaces\FilterObject;

class ModelFilter extends DataFilter {

    /**
     * 验证
     * @param Model $model
     * @param array $rules
     * @return bool
     */
    public static function validate($model, $rules) {
        if (!$model instanceof Model) {
            return parent::validate($model, $rules);
        }
        $result = true;
        foreach ($rules as $key => $item) {
            $result = $result && self::validateOne($model, $item, $key) === true;
        }
        return $result;
    }

    /**
     * 验证一条
     * @param Model $model
     * @param array|string $arg
     * @param string|integer $key
     * @return bool
     */
    protected static function validateOne(Model $model, $arg, $key = null) {
        if (empty($key) || is_integer($key)) {
            if (!is_array($arg)) {
                return false;
            }
            $key = array_shift($arg);
        }
        $filter = self::createFilter($arg);
        $result = true;
        foreach ((array)$key as $item) {
            if (empty($item)) {
                continue;
            }
            if (!$filter instanceof FilterObject) {
                if (is_string($filter) && method_exists($model, $filter)) {
                    $result = $result &&
                        call_user_func([$model, $filter], $model->$item) !== false;
                }
                continue;
            }
            if ($filter->validate($model->$item, $model)) {
                continue;
            }
            $result = false;
            $model->setError($item, str_replace('{key}', $item, $filter->getError()));
        }
        return $result;
    }

    /**
     * @param string $arg
     * @return FilterObject|string
     */
    public static function createValidate($arg) {
        $message = null;
        $arg = (array)$arg;
        if (array_key_exists('message', $arg)) {
            $message = $arg['message'];
        }
        $filter = parent::createFilter($arg[0]);
        if (empty($filter)) {
            return $arg[0];
        }
        $filter->setError($message);
        return $filter;
    }
}