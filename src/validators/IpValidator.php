<?php

class IpValidator extends ValidatorInterface {

    public function validate ($val, $options) {

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
}
