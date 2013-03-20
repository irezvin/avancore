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
        touch($this->dir.'/'.$flag);
    }
    
    function getMtime($flag) {
        clearstatcache(false, ($f = $this->dir.'/'.$flag));
        if (is_file($f)) $res = filemtime ($f);
            else $res = false;
        return $res;
    }
    
}