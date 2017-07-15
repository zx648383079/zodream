<?php
namespace Zodream\Infrastructure\Disk;

use Exception;

class FileException extends Exception {

    public function __construct($message, $code = 99, Exception $previous = null) {
        if ($message instanceof FileObject) {
            $message .= ' HAS ERROR!';
        }
        parent::__construct($message, $code, $previous);
    }

    public function getName() {
        return 'FileException';
    }
}