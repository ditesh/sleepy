<?php

class Session {

    private $id;
    private $config;

    public function __construct($config) {
        $this->config=$config;
    }

    public function __get($key) {
        if ($key === "id") return $this->id;
        else return $this->get($key);
    }

    public function __set($key,  $val) {
        $this->set($key, $val);
    }

    public function start($request) {

        $sid = $request->get("session-id");
        if (strlen($sid) > 0) session_id($sid);

        session_name($this->config["name"]);
        @session_set_cookie_params($this->config["ttl"], NULL, NULL, $this->config["secure"], $this->config["httponly"]);
        @session_start();
        $this->id = session_id();

    }

    public function end() {
        @session_write_close();
    }

    public function set($key, $val) {
        $_SESSION[$key]=$val;
    }

    public function get($key) {
        return $_SESSION[$key];
    }
}
