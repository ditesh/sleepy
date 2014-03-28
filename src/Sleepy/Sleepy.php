<?php

namespace Sleepy;
use Sleepy\Exception;
use Sleepy\Controller;
use Sleepy\Dispatcher;
use Sleepy\Library;
use \Symfony\Component\Yaml\Yaml;

require_once realpath(dirname(__FILE__)."/../../vendor/autoload.php");
require_once realpath(dirname(__FILE__)."/Library/AutoLoader.php");

/* Instantiate and register the SPL class loader
 */
$loader = new Library\AutoLoader("Sleepy", realpath(dirname(__FILE__)."/../"));
$loader->register();

/* Instantiate and register Whoops error handler
 */
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler);
$whoops->register();

class Sleepy {

    private $container;
    private $configurator;

    public function __construct($options=[]) {

        $c = new \Pimple();
        $c["resources"] = [];
        $c["response"] = new Controller\ResponseController();
        $c["request"] = new Controller\RequestController($c["response"]);
        $c["options"] = $this->initOptions($options);

        $this->container = new Library\Container($c);
        $this->configurator = new Library\Configurator($c);

    }

    public function __get($key) {
        return $this->container[$key];
    }

    public function __set($key, $value) {
        $this->container[$key] = $value;
    }

    public function configure($options) {

        try {
            $this->configurator->configureOptions($options);
        } catch (Exception\ConfigurationException $e) {
            throw $e;
        }

        return $this;

    }

    public function configureResources($resources) {

        try {
            $this->configurator->configureResources($resources);
        } catch (Exception\ConfigurationException $e) {
            throw $e;
        }

        return $this;

    }
 
    public function configureHandlers($handlers) {

        try {
            $this->configurator->configureHandlers($handlers);
        } catch (Exception\ConfigurationException $e) {
            throw $e;
        }

        return $this;

    }
    
    /* Request dispatcher
    * @param callback $converter resource to classname converter
    */
    public function dispatch($converter) {

        $this->container->converter = $converter;

        // Ensure the request is a valid request
        $this->container->request->validate();

        // Dispatch the request to the correct controller
        $dispatcher = new Dispatcher\RequestDispatcher($this->container);
        $dispatcher->dispatch();

    }
}
