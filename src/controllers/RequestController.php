<?php

class RequestController {

    private $parsed=[];
    private $body=[];
    private $params=[];
    private $files=[];
    private $response;

    public function __construct(ResponseController $response) {

        $this->response = $response;

        $uri = $_SERVER["REQUEST_URI"];
        $uri = (substr($uri, 0, 1) === "/") ? substr($uri, 1) : $uri;
        $uri = explode("/", $uri);

        $this->parsed["uri"] = parse_url($uri[$offset]);

        $this->parsed["noun"] = "";
        if (array_key_exists("path", $this->parsed["uri"]))  $this->parsed["noun"] = $this->parsed["uri"]["path"];

        $this->parsed["verb"] = $_SERVER['REQUEST_METHOD'];
        $this->parsed["request"] = $_GET;

        if ($this->parsed["verb"] === "GET") $this->body = $_GET;
        else if ($this->parsed["verb"] === "POST") $this->body = $_POST;
        else if ($this->parsed["verb"] === "PUT" || $this->parsed["verb"] === "DELETE") parse_str(file_get_contents("php://input"), $this->body);

        if (sizeof($_FILES) > 0) {
            
            list($k, $v) = each($_FILES);
            if (is_string($v["name"])) {

                $_FILES[$k]["name"][0] = $v["name"];
                $_FILES[$k]["type"][0] = $v["type"];
                $_FILES[$k]["size"][0] = $v["size"];
                $_FILES[$k]["error"][0] = $v["error"];
                $_FILES[$k]["tmp_name"][0] = $v["tmp_name"];

            }

            $this->files = $_FILES;

        }

        // Ensure charset is UTF-8
        foreach ($this->body as $key=>$val)
            $this->body[$key] = trim(iconv(mb_detect_encoding($val, mb_detect_order(), TRUE), "UTF-8", $val));

        if (array_key_exists("HTTP_USER_IP", $_SERVER)) $ip = $_SERVER["HTTP_USER_IP"];
        else if (array_key_exists("HTTP_X_REAL_IP", $_SERVER)) $ip = $_SERVER["HTTP_X_REAL_IP"];
        else if (array_key_exists("REMOTE_ADDR", $_SERVER)) $ip = $_SERVER["REMOTE_ADDR"];

        $this->parsed["ip"] = $ip;
        $this->parsed["user-agent"] = $_SERVER["HTTP_USER_AGENT"];
        $this->parsed["cookies"] = $_COOKIE;

    }

    // Magic method to get parsed data
    public function __get($key) {
        return $this->parsed[$key];
    }

    // Support for hyphenated keys, when the
    // magic method just ain't enough
    public function get($key) {
        return $this->__get($key);
    }

    // Support for getting files
    public function files($key) {
        return $this->files[$key];
    }

    public function validate(Validator $validator, array $resources, $session, $cb) {

        $noun = $this->parsed["noun"];
        $verb = $this->parsed["verb"];
        $response = $this->response;

        if ($verb === "OPTIONS") return;

        if (array_key_exists($noun, $resources) === FALSE) $response->notFound();
        else if (array_key_exists($verb, $resources[$noun]) === FALSE) $response->notFound();
        else {

            $failedKeys = array();
            $mandatory = array();
            $optional = array();

            if (array_key_exists("mandatory", $resources[$noun][$verb])) $mandatory = $resources[$noun][$verb]["mandatory"];
            if (array_key_exists("optional", $resources[$noun][$verb])) $optional = $resources[$noun][$verb]["optional"];

            if (is_array($mandatory))
            foreach ($mandatory as $key=>$constraints) {

                $val = NULL;
                if (sizeof($constraints) === 0) continue;
                
                if ($constraints[0] === "validate_upload") $val = $this->files[$key];
                else if (array_key_exists($key, $this->body)) $val = $this->body[$key];

                if ($validator->validate($val, $constraints) === FALSE) $failedKeys[] = $key;

            }

            if (is_array($optional))
            foreach ($optional as $key=>$constraints) {
                
                $val = NULL;
                if (sizeof($constraints) === 0) continue;

                if ($constraints[0] === "validate_upload") $val = $this->files[$key];
                else if (array_key_exists($key, $this->body)) {
                    
                    $val = $this->body[$key];

                    if (strlen($val) > 0 && $validator->validate($val, $constraints) === FALSE) $failedKeys[] = $key;

                }
            }

            if (sizeof($failedKeys) > 0) $response->forbidden($failedKeys);

            // If authentication is required, execute the callback and pass in the request, response and session
            if (array_key_exists("authenticated", $resources[$noun][$verb]) &&$resources[$noun][$verb]["authenticated"] === TRUE &&
                    $cb($this, $response, $session) === FALSE) $response->forbidden();

        }
    }
}