<?php

class AbstractHandler {

    private $config = [];
    public function __construct($config=[]) {
        $this->config = $config;
    }

    abstract public function init();
    abstract public function shutdown();

}
