<?php

class IntValidator extends ValidatorInterface {

    public function validate ($val, $options) {

        switch $this->type:

            case "int":
                return filter_var($val, FILTER_VALIDATE_INT);

            case "positive int":
                return filter_var($val, FILTER_VALIDATE_INT) && ((int) $val >= 0);

            case "negative int":
                return filter_var($val, FILTER_VALIDATE_INT) && ((int) $val <= 0);
    
            default: return FALSE;

    }
}
