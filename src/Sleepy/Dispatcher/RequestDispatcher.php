<?php

namespace Sleepy\Dispatcher;
use \Sleepy\Library;
use \Sleepy\Exception;

class RequestDispatcher {

    private $container;
    private $handlerDispatchercontainer;

    public function  __construct(Container $container) {
        $this->container = $container;
        $this->handlerDispatcher = new HandlerDispatcher($container);
    }

    public function dispatch() {

        $handlerDispatcher->init();

        $request = $this->container->request;
        $response = $this->container->response;

        // Preflight the request
        $method = $request->method;
        if ($method === "OPTIONS") {
            $response->send(Response\ResponseFactory::make())->flush()->end();
        }

        $name = $this->container->converter($request->resource);
        $controller = $this->container->getController($request->resource);
        $controller->request = $request;
        $controller->response = $response;

        call_user_func_array(array($controller, "setup"), Library\Reflector::getParams($name, "setup"));
        call_user_func_array(array($controller, "validate"), Library\Reflector::getParams($name, "validate"));

        try {

            $retval = call_user_func_array(array($controller, $method), Library\Reflector::getParams($name, $method));

            if (is_object($retval) === FALSE || is_subclass_of($retval, "AbstractResponse") === FALSE)
                $retval = Response\ResponseFactory::make($this->container["options"]["content-encoding"], $retval);

        } catch (Exception $e) {
            $retval = new Response\ErrorResponse(500, $e);
        }

        $response->charset = $container["options"]["charset"];
        $response->send($retval);

        // Call all handlers on shutdown
        $handlerDispatcher->shutdown();

        // Flush out the response to the client
        $response->flush()->end();

    }
}
