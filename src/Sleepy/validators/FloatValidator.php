<?php

class FloatValidator extends AbstractValidator {

    public function validate($val) {

        switch ($this->type) {

            case "float":
                return filter_var($val, FILTER_VALIDATE_FLOAT);

            case "positive float":
                return filter_var($val, FILTER_VALIDATE_FLOAT) && ((float) $val >= 0);

            case "negative float":
                return filter_var($val, FILTER_VALIDATE_FLOAT) && ((float) $val <= 0);
    
            default: return FALSE;

        }
    }
}
