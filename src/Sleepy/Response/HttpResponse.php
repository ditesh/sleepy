<?php

namespace Sleepy\Response;

abstract class AbstractResponse {

    public $charset = "UTF-8";
    public $contentHeader = "text/html";

    public function sendContentHeader() {
        header($this->contentType."; charset=".$this->charset);
    }

    abstract public function send();

}
