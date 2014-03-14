<?php

class ContentTypeValidator extends ValidatorInterface {

    public function match($val, $against) {

        if ($val === $against) return TRUE;
        return FALSE;

    }
}
