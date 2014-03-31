<?php

namespace Sleepy\Response;

class JsonResponse extends AbstractResponse {

    public $contentType = "application/json";

    public function sendBody($retval) {
        echo json_encode($retval);
    }
}
