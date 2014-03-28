<?php

class SessionHandler extends AbstractHandler {

    public function init($response) {
        @session_start();
    }

    public function shutdown() {
        @session_write_close();
    }
}
