<?php

namespace Sleepy\Response;

class ServerErrorResponse extends AbstractResponse {

    public $statusCode = 500;
    public $contentType = "application/json";

    public function send($retval) {

        $this->sendContentHeader();
        echo json_encode($retval);

    }
}
