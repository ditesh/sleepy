<?php

namespace Sleepy\Controller;

class ResponseController {

    public $charset;
    private $responseObject;

    private function presend() {

        $baseline = array();
        $headers = getallheaders();

        foreach ($headers as $k=>$v) $baseline[strtoupper($k)] = $v;

        $origin = "";
        $allowMethods = "";
        $allowHeaders = "";

        if (array_key_exists("ORIGIN", $baseline)) $origin = $baseline["ORIGIN"];
        if (array_key_exists("ACCESS-CONTROL-REQUEST-METHOD", $baseline)) $allowMethods = $baseline["ACCESS-CONTROL-REQUEST-METHOD"];
        if (array_key_exists("ACCESS-CONTROL-REQUEST-HEADERS", $baseline)) $allowHeaders = $baseline["ACCESS-CONTROL-REQUEST-HEADERS"];

        if (strlen($origin) > 0) {

	        header("Access-Control-Allow-Origin: $origin");
        	header("Access-Control-Allow-Credentials: true");

            if (strlen($allowMethods) > 0)  header("Access-Control-Allow-Methods: $allowMethods");
            if (strlen($allowHeaders) > 0)  header("Access-Control-Allow-Headers: $allowHeaders");

        }
    }

    public function send($responseObject) {
        $this->responseObject = $responseObject;
        return $this;
    }

    public function flush() {

        $this->responseObject->sendContentHeader();
        $this->responseObject->sendStatusHeader();
        $this->responseObject->sendPreflightHeaders();
        $this->responseObject->sendBody();

        return $this;

    }

    public function end() {
        exit;
    }
}
