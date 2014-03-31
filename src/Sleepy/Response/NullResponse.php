<?php

namespace Sleepy\Response;

class NullResponse extends AbstractResponse {

    public $contentType = "";

    public function sendBody($retval) {
        return;
    }
}
