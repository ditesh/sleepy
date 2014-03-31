<?php

namespace Sleepy\Response;

class RedirectResponse extends AbstractResponse {

    public $statusCode = 301;
    public $contentType = "application/json";

    public function __construct($statusCode) {
        $this->statusCode = $statusCode;
    }

    public function sendBody($retval) {

        $this->sendContentHeader();
        echo json_encode($retval);

    }
}
