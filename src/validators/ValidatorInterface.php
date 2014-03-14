<?php

interface ValidatorInterface {
    public function validate($val);
    public function match($val, $against);
}
