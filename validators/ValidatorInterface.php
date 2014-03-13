<?php

interface ValidatorInterface {
    public $type;
    public function validate($val) {}
}
