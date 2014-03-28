<?php

namespace Sleepy\Library;

class Reflector {

   public static function getParams($class, $method) {

       $retval = [];
       $f = new ReflectionFunction($class, $method);
       foreach ($f->getParameters() as $param) $retval[] = $param->name;   
       return $retval;
   
   }

}
