<?php

namespace Sleepy\Response;

abstract class AbstractResponse {

    public $charset = "UTF-8";
    public $statusCode = 200;
    public $contentHeader = "text/html";

    public function sendContentHeader() {
        header($this->contentType."; charset=".$this->charset);
    }

    public function sendPreflightHeaders() {

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

    public function sendStatusHeader() {

        $code = $this->statusCode;

        switch ($code) {

            case 204:
                header("HTTP/1.1 204 No Content");
                break;

            case 403:
                header("HTTP/1.1 403 Forbidden");
                break;

            case 404:
                header("HTTP/1.1 404 Not Found");
                break;

            case 500:
                header("HTTP/1.1 500 Internal Server Error");
                break;

            case 503:
                header("HTTP/1.1 503 Service Unavailable");
                break;

            default: break;

        }

    }

    abstract public function sendBody();

}

// Workaround for FCGI environment
// http://www.php.net/manual/en/function.getallheaders.php#84262
if (!function_exists("getallheaders")) {

    function getallheaders() {

        foreach ($_SERVER as $name => $value)
            if (substr($name, 0, 5) == "HTTP_")
                $headers[str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($name, 5)))))] = $value;

        return $headers;

    }
}
