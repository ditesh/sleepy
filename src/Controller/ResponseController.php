<?php

namespace Sleepy\Controller;

class ResponseController {

    private $code;
    private $body;
    private $options = array("autounlock" => TRUE);

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

        header('X-Powered-By: Pure Thought');

    }

    public function __construct() {}

    public function __get($key) {

        if ($key === "code") return $this->code;
        else if ($key === "body") return $this->body;
        return NULL;

    }

    public function options(array $options) {
        foreach($options as $k=>$v) $this->options[$k] = $v;
    }

    public function preflight() {
        $this->presend();
        exit;
    }

    public function notFound() {
        $this->error(404);
    }

    public function forbidden($msg=NULL) {
        $this->error(403, $msg);
    }

    public function serverError($msg=NULL) {
        $this->error(500, $msg);
    }

    public function custom(int $code, $msg=NULL) {

        $this->presend();
        
        if ($code === 204) header("HTTP/1.1 204 No Content");
        if (!is_null($msg)) echo $msg;

        exit;
 
    }

    public function error($code=NULL, $msg=NULL) {

        $this->presend();
        $this->sendContentHeader("json");

        if ($code === NULL) $code = 500;
        $this->code = $code;
        $this->body = $msg;

        if ($code === 404) header("HTTP/1.1 404 Not Found");
        else if ($code === 403) header("HTTP/1.1 403 Forbidden");
        else if ($code === 503) header("HTTP/1.1 503 Service Unavailable");
        else if ($code === 500) header("HTTP/1.1 500 Internal Server Error");

        if ($msg !== NULL) echo json_encode($msg);
        exit;

    }

    public function json($data=NULL) {

        $this->presend();
        $this->sendContentHeader("json");

        if ($data !== NULL) {

            $this->code = 200;
            $this->body = $data;

            echo json_encode($data);

        }

        exit;

    }

    public function image($data=NULL) {

        $this->presend();
        $this->sendContentHeader("image");

        $this->code = 200;

        if ($data !== NULL) echo (readfile($data));
        exit;

    }

    public function sendContentHeader($type) {

        if ($type === "image") header("Content-Type: image/png; charset=UTF-8");
        else if ($type === "json") header("Content-Type: application/json; charset=UTF-8");

    }

    public function send($msg) {

        $type = Error::getType($msg);

        if ($type === FALSE) $this->json($msg);
        else if ($type === "none") $this->json();
        else if ($type === "database" || $type === "filename") $this->serverError();
        else if ($type === "integrity") $this->forbidden();
        else if ($type === "notFound") $this->notFound();
        else $this->serverError();

        exit;

    }
}
