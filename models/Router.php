<?php

class Router {

    private $container;

    public function  __construct(Container $container) {
        $this->container = $container;
    }

    public function dispatch(RequestController $request, ResponseController $response, $session) {

        // Preflight the request
        $verb = $request->getParsed("verb");
        if ($verb === "OPTIONS") $response->preflight();

        $controller = $this->container->getController($request->getParsed("noun"), $request->getParsed("verb"));

        call_user_func(array($controller, "setup"), $request, $response, $session);
        call_user_func(array($controller, "validate"), $request, $response, $session);
        call_user_func(array($controller, strtolower($verb)), $request, $response, $session);

    }
}
