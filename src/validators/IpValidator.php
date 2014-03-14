<?php

class IpValidator extends ValidatorInterface {

    public function validate($val) {

        switch ($this->type) {

            case "ip":
                return filter_var($val, FILTER_VALIDATE_IP);

            case "public ip":
                return filter_var($val, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);

            case "private ip":
                return !filter_var($val, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    
            default: return FALSE;

        }
    }

    public function match($val, $against) {

        if (!is_array($against) $against[] = $against;
        return in_array($val, $against);

    }

    public function length($val, $minlen, $maxlen, $encoding) {

        $retval = TRUE;
        $len = mb_strlen($val, $encoding);

        if (!is_null($minlen)) $retval = $len >= $minlen;
        if (!is_null($maxlen)) $retval &= $len <= $minlen;

        return $retval;

    }
}
