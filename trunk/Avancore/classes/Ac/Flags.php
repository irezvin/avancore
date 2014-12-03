<?php

class Ac_Flags extends Ac_Prototyped {
    
    protected $dir = false;

    function __construct(array $prototype = array()) {
        $this->dir = sys_get_temp_dir();
        parent::__construct($prototype);
    }
     
    function setDir($dir) {
        $this->dir = $dir;
    }

    function getDir() {
        return $this->dir;
    }    
    
    function touch($flag, $contents = false) {
        $f = $this->dir.'/'.$flag;
        if (!is_dir($d = dirname($f))) {
            if (!mkdir($d, 0777, true)) {
                throw new Exception("Cannot create directory '{$d}' for flag '{$flag}'");
            }
        }
        if ($contents !== false) file_put_contents ($f, $contents);
            else touch($f);
    }
    
    function getMtime($flag, & $contents = null) {
        clearstatcache(false, ($f = $this->dir.'/'.$flag));
        if (is_file($f)) {
            $res = filemtime ($f);
            if (func_num_args() > 1) $contents = file_get_contents($f);
        } else $res = false;
        return $res;
    }
    
}