<?php

namespace Sleepy;

define("BASE_PATH", realpath(dirname(__FILE__)));
define("MODEL_PATH", BASE_PATH."/models");
define("CONTROLLER_PATH", BASE_PATH."/controllers");

use Symfony\Component\Yaml\Parser;

require_once BASE_PATH."/compat/compat.php";
require_once BASE_PATH."/autoload.php";

class Sleepy {

    private $container;
    private $authCallback;

    private function checkDependencies() {

        $fns = ["mb_strlen"];

        foreach ($fns as $fn)
            if (!function_exists($fn))
                throw new MissingDependencyException($fn);

    }

    public function __construct($options=[]) {

        try {
            $this->checkDependencies();
        } catch (MissingDependencyException $e) {
            throw $e;
        }

        $c = new Pimple();
        $c["resources"] = [];
        $c["validator"] = new Validator();
        $c["response"] = new ResponseController();
        $c["request"] = new RequestController($c["response"]);
        $c["options"] = $this->initOptions($options);

        $this->container = new Container($c);

    }

    public function __get($key) {
        return $this->container[$key];
    }

    public function __set($key, $value) {
        $this->container[$key] = $value;
    }

    public function initOptions($options) {

        $retval = [];
        $retval["content-type"] = "application/json";
        $retval["accept-charset"] = "utf-8";

        if (array_key_exists("content-type", $options)) $retval["content-type"] = $options["content-type"];
        if (array_key_exists("accept-charset", $options)) $retval["accept-charset"] = $options["accept-charset"];
        return $retval;

    }

    public function initResources(array $resources) {

        $filenames = [];
        $this->container->resources = [];

        if (is_dir($resources)) $filenames = glob("$resources/*");
        else $filenames[] = $resources;

        foreach ($filenames as $filename) {

            try {

                $val = Yaml::parse(file_get_contents($filename));
                $this->container->resources[{str_replace(".yml", "", basename($filename)})] = $val;

            } catch (ParseException $e) {
                throw InvalidResourceException($e);
            }

        }
    }
    
    public function initPlugins() {}

    public function setAuthCallback($cb, $params=[]) {

        $vals = [];
        foreach ($params as $param) $vals[$param] = $this->container->{$param};

        $this->authCallback = function() use ($vals) {
            call_user_func_array($cb, $vals);
        };

    }

    /* Request dispatcher
    * @param callback $cb Authentication callback
    */
    public function dispatch($fn, $controllerParams=["request", "response"], $modelParams=[]) {

        $this->container->converter = $fn;
        $request = $this->container->request;

        $request->validate($c["validator"], $c["resources"]);
        $request->authenticate($this->authCallback);

        $dispatcher = new DispatchController($this->container);
        $dispatcher->dispatch($controllerParams, $modelParams);

    }
}
