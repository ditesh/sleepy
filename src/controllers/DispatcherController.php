<?php

class DispatcherController {

    private $container;

    public function  __construct(Container $container) {
        $this->container = $container;
    }

    public function dispatch($controllerParams, $modelParams) {

        $request = $this->container->request;
        $response = $this->container->response;

        // Preflight the request
        $method = $request->method;
        if ($method === "OPTIONS") $response->preflight();

        $controller = $this->container->getController($request->resource, $request->method);
        $controller->request = $request;
        $controller->response = $response;
        foreach ($controllerParams as $v) $controller->{$v} = $container->{$v};

        call_user_func(array($controller, "setup"), $request->params, $request->body);
        call_user_func(array($controller, "validate"), $request->params, $request->body);

        try {

            $retval = call_user_func(array($controller, strtolower($method)), $request->params, $request->body);
            $response->json($retval);

        } catch (NoResponseException $e) {

            // Do nothing :-)

        } catch (ServerErrorException $e) {

            $response->serverError($e);

        } catch (ClientErrorException $e) {

            $response->clientError($e);

        }
    }
}
