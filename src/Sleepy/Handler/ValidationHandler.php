<?php

/*
 * Valid types are:
 * ip, private ip, public ip (done)
 * float, positive float, negative float (done)
 * int, positive int, negative int (done)
 * currency, currency code
 * country code
 * price
 * upload
 * alphabets, alphanumeric
 * float, int and other validate filters
 * ctype_print, ctype_alpha, ctype_alnum, ctype_*
 * validate_country
 * validate_mongo_id
 * validate_mysql_id
 * validate_date
 * validate_price
 * validate_upload
 */
class ValidationHandler extends AbstractHandler {

    public function init($request, $response, $resources) {

        $method = $request->method;
        $resource = $request->resource;

        $optional = [];
        $mandatory = [];
        $failedKeys = [];

        if (array_key_exists("mandatory", $resources[$resource][$method])) $mandatory = $resources[$resource][$method]["mandatory"];
        if (array_key_exists("optional", $resources[$resource][$method])) $optional = $resources[$resource][$method]["optional"];

        if (is_array($mandatory))
        foreach ($mandatory as $key=>$constraints) {

            $val = NULL;
            if (sizeof($constraints) === 0) continue;

            if ($constraints[0] === "validate_upload") $val = $this->files[$key];
            else if (array_key_exists($key, $this->body)) $val = $this->body[$key];

            if ($this->validate($val, $constraints) === FALSE) $failedKeys[] = $key;

        }

        if (is_array($optional))
        foreach ($optional as $key=>$constraints) {

            $val = NULL;
            if (sizeof($constraints) === 0) continue;

            if ($constraints[0] === "validate_upload") $val = $this->files[$key];
            else if (array_key_exists($key, $this->body)) {

                $val = $this->body[$key];

                if (strlen($val) > 0 && $this->validate($val, $constraints) === FALSE) $failedKeys[] = $key;

            }
        }

        if (sizeof($failedKeys) > 0) $response->forbidden($failedKeys);

    }

    public function shutdown() {}

    private function checkType($val, $type) {

        // No type specified
        if (is_null($type)) return TRUE;

        // It's a boolean choice
        if (is_array($type)) {

            foreach ($type as $t)
                if ($val === $t)
                    return TRUE;

            return FALSE;

        }

        // It's a boolean choice
        $ctypes = array("ctype_print", "ctype_alpha", "ctype_alnum");
        if (in_array($type, $ctypes)) {

            $retval = call_user_func($type, $val);
            if ($retval === FALSE) return FALSE;
            return TRUE;

        }

        // It's a validate filter
        if (is_string($type)) {

            if ($type === "validate_country") {

                return Data::check("countries", $val);

            } else if ($type === "validate_currency") {

                return Data::check("currencies", $val);

            } else if ($type === "validate_mongoid") {

                if (strlen($val) === 24) return TRUE;
                else return FALSE;

            } else if ($type === "validate_mysql_id") {

                return ctype_digit($val);

            } else if ($type === "validate_date") {

                $retval = date_parse_from_format("Y-m-d", $val);
                if ($retval["error_count"] > 0) return FALSE;
                return TRUE;

            } else if ($type === "validate_price") {

                if (filter_var($val, FILTER_VALIDATE_FLOAT) === FALSE) return FALSE;
                $val = explode(".", $val);

                if (sizeof($val) === 1) return TRUE;
                if (strlen($val[1]) <= 2) return TRUE;

                return FALSE;

            } else if ($type === "validate_upload") {

                if (!is_array($val)) return FALSE;

                foreach ($val["error"] as $k=>$v)
                    if ($v !==  UPLOAD_ERR_OK)
                        return FALSE;

                return TRUE;

            } else {

                $id = filter_id($type);
                $retval = filter_var($val, $id);
                if ($retval === FALSE) return FALSE;
                return TRUE;

            }
        }

        return FALSE;

    }

    private function validate($val, array $constraints) {

        $len = sizeof($constraints);

        // $val has to exist
        if ($len === 0) return FALSE;

        $type = $constraints[0];

        // Type checking
        if ($this->checkType($val, $type) === FALSE) return FALSE;

        // If it's an upload, ensure constraints are respected
        if ($type === "validate_upload") {

            foreach ($val["name"] as $k=>$v) {

                // Validate minimum upload size
                if ($len > 1) {

                    $min = intval($constraints[1]);
                    if ($val["size"][$k] < $min) return FALSE;

                }

                // Validate maximum upload size
                if ($len > 2) {

                    $max = intval($constraints[2]);
                    if ($val["size"][$k] > $max) return FALSE;

                }

                // Validate mime type
                if ($len > 3) {

                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimetype = finfo_file($finfo, $val["tmp_name"][$k]);

                    $validMimeType= $constraints[3];
                    if ($validMimeType === "image") $validMimeTypes = array("image/png", "image/jpg", "image/jpeg", "image/gif");

                    if  (!in_array($mimetype, $validMimeTypes)) return FALSE;

                }
            }

            return;

        }

        // If it's an upload, ensure constraints are respected
        if ($type === "validate_date") {

            if ($len > 0) {

                $val = strtotime($val);
                $today = strtotime("today");
                $options = $constraints[1];

                if (!in_array("past", $options)) if (($today -$val) > 0) return FALSE;
                if (!in_array("present", $options)) if (($today -$val) === 0) return FALSE;
                if (!in_array("future", $options)) if (($today -$val) < 0) return FALSE;

            }

            return TRUE;

        }

        if ($len > 1) {
            
            $min = intval($constraints[1]);

            if (($type === "float" || $type === "validate_price") && $val < $min) return FALSE;
            else if (strlen($val) < $min) return FALSE;

        }

        if ($len > 2) {

            $max = intval($constraints[2]);

            if (($type === "float" || $type === "validate_price") && $val > $max) return FALSE;
            else if(strlen($val) > $max) return FALSE;

        }

        return TRUE;

    }
}
