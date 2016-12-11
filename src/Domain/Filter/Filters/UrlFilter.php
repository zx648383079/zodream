<?php
namespace Zodream\Domain\Filter\Filters;

use Zodream\Infrastructure\Interfaces\FilterObject;

class UrlFilter extends FilterObject {
    protected $error = '不是合法的网址！';

    protected $_defaultOption = array(
        'path'  => false,
        'query' => false
    );

    public function filter($arg) {
        $flags = 0;
        if ($this->_option['path']) {
            $flags |= FILTER_FLAG_PATH_REQUIRED;
        }
        if ($this->_option['query']) {
            $flags |= FILTER_FLAG_QUERY_REQUIRED;
        }
        return filter_var($arg, FILTER_VALIDATE_URL, $flags);
    }
}