<?php

namespace Sleepy\Response;

class ResponseFactory {

    public function make($type, $data) {

        if ($type === "application/json") return new Response\JsonResponse($data);
        else throw new Exception\ConfigurationException("No such response type configured");

    }
}
