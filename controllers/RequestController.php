<?php

class RequestController {

    private $data;
    private $response;

    public function __construct(ResponseController $response) {

        $this->response = $response;

		$uri = $_SERVER["REQUEST_URI"];
		$uri = (substr($uri, 0, 1) === "/") ? substr($uri, 1) : $uri;
        $uri = explode("/", $uri);

        // Lets check for versioning
        $offset = 1;
        $this->data["version"] = 1;
        if (ctype_digit($uri[1])) {

            $offset = 2;
            $this->data["version"] = $uri[1];

        }

        $this->data["uri"] = parse_url($uri[$offset]);
        $this->data["noun"] = $this->data["uri"]["path"];
        $this->data["verb"] = $_SERVER['REQUEST_METHOD'];
        $this->data["request"] = $_GET;

        if ($this->data["verb"] === "GET") $this->data["body"] = $_GET;
        else if ($this->data["verb"] === "POST") $this->data["body"] = $_POST;
        else if ($this->data["verb"] === "PUT" || $this->data["verb"] === "DELETE") parse_str(file_get_contents("php://input"), $this->data["body"]);

        if (sizeof($_FILES) > 0) {
            
            list($k, $v) = each($_FILES);
            if (is_string($v["name"])) {

                $_FILES[$k]["name"][0] = $v["name"];
                $_FILES[$k]["type"][0] = $v["type"];
                $_FILES[$k]["size"][0] = $v["size"];
                $_FILES[$k]["error"][0] = $v["error"];
                $_FILES[$k]["tmp_name"][0] = $v["tmp_name"];

            }

            $this->data["files"] = $_FILES;

        }

        // Ensure charset is UTF-8
        foreach ($this->data["body"] as $key=>$val)
            $this->data["body"][$key] = trim(iconv(mb_detect_encoding($text, mb_detect_order(), TRUE), "UTF-8", $val));

        $ip = $_SERVER["HTTP_USER_IP"];
        if (strlen($ip) === 0) $ip = $_SERVER["HTTP_X_REAL_IP"];
        if (strlen($ip) === 0) $ip = $_SERVER["REMOTE_ADDR"];

        $this->data["ip"] = $ip;
        $this->data["user-agent"] = $_SERVER["HTTP_USER_AGENT"];
        $this->data["cookies"] = $_COOKIE;

    }

    // Magic method to get 
    public function __get($key) {
        return $this->data["body"][$key];
    }

    // Support for hyphenated keys
    public function get($key) {
        return $this->__get($key);
    }

    // Support for getting files
    public function files($key) {
        return $this->data["files"][$key];
    }

    public function getParsed($key) {
        return $this->data[$key];
    }

    public function validate(Validator $validator, array $resources, $cb) {

        $noun = $this->data["noun"];
        $verb = $this->data["verb"];
        $response = $this->response;

        if ($verb === "OPTIONS") return;

        if ($resources[$noun][$verb] === NULL) $response->notFound();
        else {

            $failedKeys = array();
            $mandatory = $resources[$noun][$verb]["mandatory"];
            $optional = $resources[$noun][$verb]["optional"];

            if (is_array($mandatory))
            foreach ($mandatory as $key=>$constraints) {

                if (sizeof($constraints) === 0) continue;
                
                if ($constraints[0] === "validate_upload") $val = $this->data["files"][$key];
                else $val = $this->data["body"][$key];

                if ($validator->validate($val, $constraints) === FALSE) $failedKeys[] = $key;

            }

            if (is_array($optional))
            foreach ($optional as $key=>$constraints) {
                
                if (sizeof($constraints) === 0) continue;

                if ($constraints[0] === "validate_upload") $val = $this->data["files"][$key];
                else $val = $this->data["body"][$key];

                if (strlen($val) > 0 && $validator->validate($val, $constraints) === FALSE) $failedKeys[] = $key;

            }

            if (sizeof($failedKeys) > 0) $response->forbidden($failedKeys);
            if ($resources[$noun][$verb]["authenticated"] === TRUE && $cb($this, $response) === FALSE) $response->forbidden();

        }
    }
}
