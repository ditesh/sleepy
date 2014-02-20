<?php

class Error {

    private function process($type, $error=NULL) {

        if ($error === NULL) return array("type"=>$type);
        else if (!is_array($error)) return FALSE;
        else if ($error["type"] === $type) return TRUE;
        else return FALSE;

    }

    public function isError($error) {

        if (!is_array($error)) return FALSE;
        if ($error["type"] === "none") return FALSE;

        return TRUE;

    }

    public static function check($error,$type) {
        if ($error["type"] === $type) return TRUE;
        return FALSE;
    }

    public static function getType($error) {
        if (is_array($error) && array_key_exists("type", $error)) return $error["type"];
        return FALSE;
    }

    public static function none($error=NULL) {
        return self::process("none", $error);
    }

    public static function notFound($error=NULL) {
        return self::process("notFound", $error);
    }

    public static function integrity($error=NULL) {
        return self::process("integrity", $error);
    }

    public static function database($error=NULL) {
        return self::process("database", $error);
    }

    public static function filename($error=NULL) {
        return self::process("filename", $error);
    }
}
