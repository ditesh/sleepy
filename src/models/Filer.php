<?php

class Filer {

    public static $config = array();

    public static function add($filename) {

        if (!file_exists($filename)) return Error::filename();
        $publicPath = self::$config["public_path"];

        $hash = md5($filename);
        $l1 = $hash[0];
        $l2 = $hash[1];
        $hash = substr($hash, 2);

        // Determine mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $filename);

        // Set correct extension
        if ($type === "image/png") $extension = ".png";
        else if ($type === "image/jpg") $extension = ".jpg";
        else if ($type === "image/jpeg") $extension = ".jpg";
        else if ($type === "image/gif") $extension = ".gif";
        else return Error::filename();

        $directory = "$publicPath/$l1/$l2/";
        $newfilename = "$directory$hash$extension";

        if (!is_writable($directory)) return Error::filename();
        if (file_exists($newfilename)) return Error::filename();

        if (@move_uploaded_file($filename, $newfilename) === FALSE) return Error::filename();
        return "/$l1/$l2/$hash$extension";

    }

    public static function delete($filename) {

        $publicPath = self::$config["public_path"];
        $filename = realpath("$publicPath/$filename");
        $basepath = dirname($filename);

        // Ensure we are within the correct directory (avoid traversal attacks)
        if (realpath("$basepath/../../") !== realpath($publicPath)) return Error::filename();

        // Permission check
        if (!is_writable($filename)) return Error::filename();

        if (@unlink($filename) === FALSE) return Error::filename();
        return Error::none();

    }
}
