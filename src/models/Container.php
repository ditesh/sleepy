<?php

class Container {

    private $pimple;
    private $converter;

    public function __construct(Pimple $pimple) {
        $this->pimple = $pimple;
    }


    public function __get($key) {
        return $this->pimple[$key];
    }

    public function __set($key, $value) {
        $this->pimple[$key] = $value;
    }

    public function getController($noun, $verb) {

        $name = $this->converter($noun, "controller");

        if ($this->pimple->offsetExists($name) === FALSE) {

            // If a controller is defined in resource definition file, use that
            $controller = $name;
            $model = $this->getModel($noun, $verb);

            if (array_key_exists("controller", $this->pimple["resources"][$noun][$verb]))
                $controller = str_replace(" ", "",
                    ucwords(str_replace("-", " ",
                        $this->pimple["resources"][$noun][$verb]["controller"])))."ResourceController";

            $this->pimple[$name] = $this->pimple->share(function() use ($controller, $model) {

                $obj = new ReflectionClass($controller);
                return $obj->newInstanceArgs(array($model));

            });

        }

        return $this->pimple[$name];

    }

    public function getModel($noun, $verb) {

        $name = $this->converter($noun, "model");

        if ($this->pimple->offsetExists($name) === FALSE) {

            $model = $name;

            // If a model is defined in resource definition file, use that
            if (array_key_exists("model", $this->pimple["resources"][$noun][$verb])) $model = str_replace(" ",  "", ucwords(str_replace("-", " ", $this->pimple["resources"][$noun][$verb]["model"])))."ResourceModel";

            $this->pimple[$name] = $this->pimple->share(function($c) use ($model) {

                $obj = new ReflectionClass($model);
                return $obj->newInstanceArgs(array($c["db"], $c["logger"]));

            });
        }

        return $this->pimple[$name];

    }
}
