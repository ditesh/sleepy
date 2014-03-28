<?php

namespace Sleepy\Response;

class JsonResponse extends AbstractResponse {

    public $contentType = "application/json";

    public function send($retval) {

        $this->sendContentHeader();
        echo json_encode($retval);

    }
}
