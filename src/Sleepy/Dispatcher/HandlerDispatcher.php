<?php

namespace Sleepy\Dispatcher;

class HandlerDispatcher {

    private $container;

    public function __construct($container) {
        $this->container = $container;
    }

    private function dispatch($method) {

        foreach ($this->container->handlers as $handler) $handler->$method();

    }

    public function init() {
        $this->dispatch("init");
    }

    public function shutdown() {
        $this->dispatch("shutdown");
    }

}
