<?php

class Ae_Indent_Cache_Default extends Ae_Indent_Cache {

    protected $path = false;

    protected $basename = false;

    function getPath() {
        return $this->path;
    }

    function setPath($path) {
        $this->path = $path;
        $this->cacheTable = false;
    }

    function setBasename($basename) {
        $this->basename = $basename;
    }

    function getBasename() {
        if ($this->basename === false) $res = md5($_SERVER['SCRIPT_NAME']);
            else $res = $this->basename;
        return $res;
    }

    function load() {
        if ($this->path !== false && is_file($fn = $this->path.'/'.$this->getBasename())) {
            $this->cacheTable = unserialize(file_get_contents($fn));
        }
    }

    function save($fromDestructor = false) {
        if ($this->path !== false && $this->cacheTable !== false)
            file_put_contents ($this->path.'/'.$this->getBasename(), serialize($this->cacheTable));
    }

    function clear() {
        $this->cacheTable = array();
        if ($this->path !== false && is_file($fn = $this->path.'/'.$this->getBasename())) {
            unlink($fn);
        }
    }

}