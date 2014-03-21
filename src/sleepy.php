<?php

namespace Sleepy;
use Sleepy\Controller;
use \Symfony\Component\Yaml\Parser;

require_once realpath(dirname(__FILE__)."/../vendor/autoload.php");
require_once realpath(dirname(__FILE__)."/SplClassLoader.php");

$loader = new SplClassLoader('Sleepy');
$loader->register();

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler);
$whoops->register();

class Sleepy {

    private $container;
    private $authCallback;

    public function __construct($options=[]) {

        $c = new \Pimple();
        $c["resources"] = [];
        $c["response"] = new Controller\ResponseController();
        $c["request"] = new Controller\RequestController($c["response"]);
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
                $this->container->resources[str_replace(".yml", "", basename($filename))] = $val;

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
    public function dispatch($converter) {

        $this->container->converter = $converter;
        $request = $this->container->request;

        $request->validate($c["validator"], $c["resources"]);
        $request->authenticate($this->authCallback);

        $dispatcher = new DispatchController($this->container);
        $dispatcher->dispatch();

    }
}
