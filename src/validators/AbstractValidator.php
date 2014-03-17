<?php

abstract class AbstractValidator {

    abstract function validate($val) {}

    public function match($val, $against) {

        if (!is_array($against)) $against[] = $against;
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
