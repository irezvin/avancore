<?php

class Ac_Util_Cli extends Ac_Prototyped {
    
    /**
     * @var array
     */
    protected $allowedArgs = false;
    
    protected $args = array();
    
    static function getPositional(array $array) {
        return array_intersect_key($array, array_flip(array_filter(array_keys($array), 'is_numeric')));
    }
    
    static function shift(array & $arr) {
        $res = null;
        foreach (array_keys($arr) as $k) {
            if (is_numeric($k)) {
                $res = $arr[$k];
                unset($arr[$k]);
                return $res;
            }
        }
        return $res;
    }
    
    function setAllowedArgs(array $allowedArgs) {
        $this->allowedArgs = $allowedArgs;
    }

    /**
     * @return array
     */
    function getAllowedArgs() {
        return $this->allowedArgs;
    }
    
    function acceptArgs(array $argv = array()) {
        $this->args = $this->parseArgv();
    }
    
    function get($arg, $default = null) {
        return isset($this->args[$arg])? $this->args[$arg] : $default;
    }
    
    function parseArgv(array $argv = array()) {
        if (func_num_args() === 0)
            $argv = isset($_SERVER['argv'])? $_SERVER['argv'] : array();
        unset($argv[0]);
        $allowedArgs = $this->getAllowedArgs();
        $posArgs = false;
        $res = array();
        foreach ($argv as $arg) {
            if ($arg == '--') {
                $posArgs = true;
                continue;
            }
            if ($posArgs) $res[] = $arg;
            elseif (substr($arg, 0, 2) == '--') {
                $x = explode('=', substr($arg, 2), 2);
                $k = $x[0];
                $v = isset($x[1])? $x[1] : true;
                $res[$k] = $v;
            } else {
                $res[] = $arg;
            }
        }
        foreach (array_diff(array_keys($res), $allowedArgs) as $k) {
            if (!is_numeric($k)) throw new Ac_E_Cli("Invalid argument: '{$k}'");
        }
        return $res;
    }
    
}