<?php

namespace Sleepy\Library;

class Container {

    private $pimple;
    private $converter;

    public function __construct(\Pimple $pimple) {
        $this->pimple = $pimple;
    }


    public function __get($key) {
        return $this->pimple[$key];
    }

    public function __set($key, $value) {
        $this->pimple[$key] = $value;
    }

    public function getController(array $resource) {

        $name = $this->converter($resource);

        if ($this->pimple->offsetExists($name) === FALSE) {

            $this->pimple[$name] = function() use ($name) {
                return new $name;
            };

        }

        return $this->pimple[$name];

    }
}
