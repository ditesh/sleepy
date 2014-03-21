<?php

class DispatcherController {

    private $container;
    private function getFunctionParams($resource, $method) {

        $retval = [];
        $name = $this->converter($resource);
        $f = new ReflectionFunction($name, $method);
        foreach ($f->getParameters() as $param) $retval[] = $param->name;   
        return $retval;

    }

    public function  __construct(Container $container) {
        $this->container = $container;
    }

    public function dispatch() {

        $request = $this->container->request;
        $response = $this->container->response;

        // Preflight the request
        $method = $request->method;
        if ($method === "OPTIONS") $response->preflight();

        $controller = $this->container->getController($request->resource);
        $controller->request = $request;
        $controller->response = $response;

        call_user_func_array(array($controller, "setup"), $this->getFunctionParams($resource, "setup"));
        call_user_func_array(array($controller, "validate"), $this->getFunctionParams($resource, "validate"));

        try {

            $retval = call_user_func_array(array($controller, $method), $this->getFunctionParams($resource, $method));
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
