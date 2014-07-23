<?php

class Ac_Flags {
    
    protected $dir = false;

    function __construct() {
        $this->dir = sys_get_temp_dir();
    }
    
    function setDir($dir) {
        $this->dir = $dir;
    }

    function getDir() {
        return $this->dir;
    }    
    
    function touch($flag) {
        $f = $this->dir.'/'.$flag;
        if (!is_dir($d = dirname($f))) {
            if (!mkdir($d, 0777, true)) {
                throw new Exception("Cannot create directory '{$d}' for flag '{$flag}'");
            }
        }
        touch($f);
    }
    
    function getMtime($flag) {
        clearstatcache(false, ($f = $this->dir.'/'.$flag));
        if (is_file($f)) $res = filemtime ($f);
            else $res = false;
        return $res;
    }
    
}