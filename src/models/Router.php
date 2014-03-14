<?php

class Router {

    private $container;

    public function  __construct(Container $container) {
        $this->container = $container;
    }

    public function dispatch($controllerParams, $modelParams) {

        $request = $this->container->request;
        $response = $this->container->response;

        // Preflight the request
        $verb = $request->getParsed("verb");
        if ($verb === "OPTIONS") $response->preflight();

        $controller = $this->container->getController($request->noun, $request->verb);
        foreach ($controllerParams as $v) $controller->{$v} = $container->{$v};

        call_user_func(array($controller, "setup"), $request->params, $request->body);
        call_user_func(array($controller, "validate"), $request->params, $request->body);

        try {

            $retval = call_user_func(array($controller, strtolower($verb)), $request->params, $request->body);
            $response->json($retval);

        } catch (NoResponseException $e) {

            // Do nothing :-)

        } catch (ServerErrorException $e) {

            $response->serverError();

        } catch (ClientErrorException $e) {

            $response->clientError();

        }
    }
}
