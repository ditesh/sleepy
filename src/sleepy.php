<?php

namespace Sleepy;

define("BASE_PATH", realpath(dirname(__FILE__)));
define("MODEL_PATH", BASE_PATH."/models");
define("CONTROLLER_PATH", BASE_PATH."/controllers");
define("CONFIG_PATH", BASE_PATH."/configuration");

use Symfony\Component\Yaml\Parser;

// Compatibility includes
require_once BASE_PATH."/compat/compat.php";

class Sleepy {

    private $container;

    public function __construct() {

        $c = new Pimple();
        $c["validator"] = new Validator();
        $c["response"] = new ResponseController();
        $c["request"] = new RequestController($c["response"]);

        $this->container = new Container($c);

    }

    public function __get($key) {
        return $this->container[$key];
    }

    public function __set($key, $value) {
        $this->container[$key] = $value;
    }

    public function initResources($resources) {

        if (!is_null($resources)) {

            $filenames = [];

            if (is_dir($resources)) $filenames = glob("$resources/*");
            else $filenames[] = $resources;

            foreach ($filenames as $filename) {

                try {
                    $value = Yaml::parse(file_get_contents($filename));
                    var_dump($value);
                } catch (ParseException $e) {
                    throw InvalidResourceException($e);
                }

            }

        }
    }

    /* Request dispatcher
    * @param callback $cb Authentication callback
    */
    public function dispatch($fn, $controllerParams=["request", "response"], $modelParams=[], $cb=NULL) {

        $this->container->converter = $fn;
        if ($cb === NULL) $cb = function() {};

        $c = $this->container;
        $c["request"]->validate($c["validator"], $c["resources"], $c["session"], $cb);

        $router = new Router($this->container);
        $router->dispatch($controllerParams, $modelParams);

    }
}
