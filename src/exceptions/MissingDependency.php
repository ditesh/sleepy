<?php

class MissingDependencyException extends Exception {

    protected $message = "A required dependency was missing";

    public function __construct($message = null, $code = 0, Exception $previous = null) {
        $this->message = $message;
    }

}
