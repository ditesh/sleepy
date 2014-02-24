<?php

class Container {

    public function __construct(Pimple $container) {
        $this->container = $container;
    }

    public function getController($noun, $verb) {

        // Convert resource names such as hotel-photos to HotelPhotos
        $name = str_replace(" ",  "", ucwords(str_replace("-", " ", $noun)))."ResourceController";

        if ($this->container->offsetExists($name) === FALSE) {

            // If a controller is defined in resource definition file, use that
            $controller = $name;
            $model = $this->getModel($noun, $verb);

            if (array_key_exists("controller", $this->container["resources"][$noun][$verb]))
                $controller = str_replace(" ", "",
                    ucwords(str_replace("-", " ",
                        $this->container["resources"][$noun][$verb]["controller"])))."ResourceController";

            $this->container[$name] = $this->container->share(function() use ($controller, $model) {

                $obj = new ReflectionClass($controller);
                return $obj->newInstanceArgs(array($model));

            });

        }

        return $this->container[$name];

    }

    public function getModel($noun, $verb) {

        // Convert resource names such as hotel-photos to HotelPhotos
        $name = str_replace(" ",  "", ucwords(str_replace("-", " ", $noun)))."ResourceModel";

        if ($this->container->offsetExists($name) === FALSE) {

            $model = $name;

            // If a model is defined in resource definition file, use that
            if (array_key_exists("model", $this->container["resources"][$noun][$verb])) $model = str_replace(" ",  "", ucwords(str_replace("-", " ", $this->container["resources"][$noun][$verb]["model"])))."ResourceModel";

            $this->container[$name] = $this->container->share(function($c) use ($model) {

                $obj = new ReflectionClass($model);
                return $obj->newInstanceArgs(array($c["db"], $c["logger"]));

            });
        }

        return $this->container[$name];

    }
}
