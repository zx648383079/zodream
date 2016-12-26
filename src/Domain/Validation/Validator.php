<?php
namespace Zodream\Domain\Validation;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/12/6
 * Time: 12:16
 */
use Zodream\Infrastructure\Support\MessageBag;

class Validator {
    protected $data = [];
    protected $rules = [];
    /**
     * @var MessageBag
     */
    protected $message;

    public function setData($arg) {
        $this->data = (array)$arg;
        return $this;
    }

    public function setRules($args) {
        foreach ((array)$args as $key => $arg) {
            $this->rules[] = $this->converterRule($arg, $key);
        }
    }

    protected function converterRule($rule, $key = null) {

    }

    /**
     * Determine if the data passes the validation rules.
     *
     * @return bool
     */
    public function passes() {
        $this->message = new MessageBag;

        // We'll spin through each rule, validating the attributes attached to that
        // rule. Any error messages will be added to the containers with each of
        // the other error messages, returning true if we don't have messages.
        foreach ($this->rules as $attribute => $rules) {

        }

        return $this->message->isEmpty();
    }

    /**
     * Determine if the data fails the validation rules.
     *
     * @return bool
     */
    public function fails() {
        return ! $this->passes();
    }

    /**
     * Run the validator's rules against its data.
     *
     * @return void
     *
     * @throws ValidationException
     */
    public function validate() {
        if ($this->fails()) {
            throw new ValidationException($this);
        }
    }

    /**
     * Get the message container for the validator.
     *
     * @return MessageBag
     */
    public function messages() {
        if (! $this->message) {
            $this->passes();
        }
        return $this->message;
    }

}