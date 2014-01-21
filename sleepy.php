<?php

define("BASE_PATH", realpath(dirname(__FILE__)));
define("MODEL_PATH", BASE_PATH."/models");
define("CONTROLLER_PATH", BASE_PATH."/controllers");
define("CONFIG_PATH", BASE_PATH."/configuration");

// Compatibility includes
require_once BASEPATH."/compat/compat.php";

class Sleepy {

    public $container;

    public function __construct(array $options=array()) {

        if (array_key_exists("resources", $options)) require_once($options["resources"]);
        if (array_key_exists("models", $options)) define("RESOURCE_MODEL_PATH", $options["models"]);
        if (array_key_exists("controllers", $options)) define("RESOURCE_CONTROLLER_PATH", $options["controllers"]);

        // Register the autoloader
        spl_autoload_register(function ($name) {

            if (strstr($name, "ResourceController")) require_once RESOURCE_CONTROLLER_PATH."/$name.php";
            else if (strstr($name, "Controller")) require_once CONTROLLER_PATH."/$name.php";
            else if (strstr($name, "ResourceModel")) require_once RESOURCE_MODEL_PATH."/$name.php";
            else require_once MODEL_PATH."/$name.php";

        });

        require_once CONFIG_PATH."/config.php";

        $c = new Pimple();
        $c["config"] = $config;
        $c["resources"] = $resources;
        $c["validator"] = new Validator();
        $c["response"] = new ResponseController();
        $c["request"] = new RequestController($c["response"]);

        $c["db"] = $c->share(function($c) {
            try {
                return new DB($c["config"]["mongo"]);
            } catch (Exception $e) {
                $c["response"]->error(500);
            }
        });

        $c["logger"] = $c->share(function($c) {
            return new Logger($c["config"]["mongo"]["level"], $c["db"],  $c["request"]);
        });

        $this->container = $c;

    }

    public function dispatch($cb) {

        $c = $this->container;
        $c["request"]->validate($c["validator"], $c["resources"], $cb);

        $router = new Router(new Container($c));
        $router->dispatch($c["request"],  $c["response"], $c["session"]);

    }
}
