<?php

class Logger {

    private $db;
    private $request;
    private $info = FALSE;
    private $debug = FALSE;
    private $warn = FALSE;
    private $error = FALSE;
    private $critical= FALSE;

    private function mask($val) {

        if (is_array($val) && array_key_exists("password", $val)) $val["password"] = "REDACTED";
        return $val;

    }

    public function __construct($level, DB $db, RequestController $request) {

        if (stristr($level, "INFO")) $this->info = TRUE;
        if (stristr($level, "DEBUG")) $this->debug = TRUE;
        if (stristr($level, "WARN")) $this->warn = TRUE;
        if (stristr($level, "ERROR")) $this->error = TRUE;
        if (stristr($level, "CRITICAL")) $this->critical = TRUE;

        $this->db = $db;
        $this->request = $request;

    }

    /* Magic method wrapper for various priority methods
     *
     * debug(): Detailed debug information
     * info(): Interesting events. Examples: User logs in.
     * warn(): Exceptional occurrences that are not errors. Examples: Use of deprecated APIs.
     * error(): Runtime errors that do not require immediate action
     * critical: Critical conditions, action must be taken immediately. Example: Entire website down, database unavailable.
     */
    public function __call($name, array $args) {

        $priorities = array("debug", "info", "warn", "error", "critical");

        if (in_array($name, $priorities) === FALSE) return FALSE;
        if (sizeof($args) === 0) return FALSE;
        if (sizeof($args) === 1) $args[] = array();

        if ($name === "debug" && $this->debug === FALSE) return FALSE;
        else if ($name === "info" && $this->info === FALSE) return FALSE;
        else if ($name === "warn" && $this->warn === FALSE) return FALSE;
        else if ($name === "error" && $this->error === FALSE) return FALSE;
        else if ($name === "critical" && $this->critical === FALSE) return FALSE;

        list(, $caller) = debug_backtrace(FALSE);
        $fn = $caller["function"];
        $class = $caller["class"];

        if (strlen($class) > 0) $fn = "$class::$fn";
        return $this->log($fn, $name, $this->mask($args[0]), $args[1]);

    }

    public function log($caller, $priority, $msg, $objs) {

        $cookie = array_map(function($t){ return is_string($t) ? utf8_encode($t) : $t; }, $this->request->getParsed("cookies"));

        return $this->db->logs->insert(array(
            "request"=>array(
                "ip"=>$this->request->getParsed("ip"),
                "uri"=>$this->request->getParsed("uri"),
                "verb"=>$this->request->getParsed("verb"),
                "body"=>$this->request->getParsed("body"),
                "cookie"=>$cookie,
                "user-agent"=>$this->request->getParsed("user-agent"),
            ),
            "message"=>$msg,
            "objects"=>$objs
        ));

    }
}
