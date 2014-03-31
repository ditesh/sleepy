<?php

namespace Sleepy\Response;

class ResponseFactory {

    public function make($type=NULL, $data=NULL) {

        if (is_null($type)) return new Response\NullResponse();
        else if ($type === "application/json") return new Response\JsonResponse($data);
        else throw new Exception\ConfigurationException("No such response type configured");

    }
}
