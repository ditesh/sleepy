<?php

define("BASE_PATH", realpath(dirname(__FILE__)));
define("MODEL_PATH", BASE_PATH."/models");
define("CONTROLLER_PATH", BASE_PATH."/controllers");
define("CONFIG_PATH", BASE_PATH."/configuration");

// Compatibility includes
require_once BASE_PATH."/compat/compat.php";

class Sleepy {

    public $container;

    public function __construct(array $options=array()) {

        if (array_key_exists("resource-path", $options)) require_once($options["resource-path"]);
        else throw new Exception("Resource path not set");

        if (array_key_exists("model-path", $options)) define("RESOURCE_MODEL_PATH", $options["model-path"]);
        else throw new Exception("Model path not set");

        if (array_key_exists("controller-path", $options)) define("RESOURCE_CONTROLLER_PATH", $options["controller-path"]);
        else throw new Exception("Controller path not set");

        if (array_key_exists("logger", $options)) $logger = $options["logger"];
        else $logger = NULL;

        if (array_key_exists("session", $options)) $session = $options["session"];
        else $session = NULL;
        
        // Register the autoloader
        spl_autoload_register(function ($name) {

            if (strstr($name, "ResourceController")) require_once RESOURCE_CONTROLLER_PATH."/$name.php";
            else if (strstr($name, "Controller")) require_once CONTROLLER_PATH."/$name.php";
            else if (strstr($name, "ResourceModel")) require_once RESOURCE_MODEL_PATH."/$name.php";
            else require_once MODEL_PATH."/$name.php";

        });

        $c = new Pimple();
        $c["session"] = $session;
        $c["resources"] = $resources;
        $c["validator"] = new Validator();
        $c["response"] = new ResponseController();
        $c["request"] = new RequestController($c["response"]);

        $logger->request = $c["request"];
        $c["logger"] = $logger;

        $this->container = $c;

    }

    public function setDatabase($db) {
        $this->container["logger"]->db = $db;
        $this->container["db"] = $db;
    }

    /* Request dispatcher
    * @param callback $cb Authentication callback
    */
    public function dispatch($cb=NULL) {

        if ($cb === NULL) $cb = function() {};

        $c = $this->container;
        $c["request"]->validate($c["validator"], $c["resources"], $c["session"], $cb);

        $router = new Router(new Container($c));
        $router->dispatch($c["request"],  $c["response"], $c["session"]);

    }
}
