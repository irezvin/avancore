<?php

abstract class Ae_Indent_Cache extends Ae_Autoparams {

    protected $cacheTable = false;

    function addEntry($file, $line, $indent, $fileMtime = false) {
        if ($this->cacheTable === false) {
            $this->cacheTable = array();
            $this->load();
        }
        if ($fileMtime === false) $fileMtime = filemtime($file);
        $this->cacheTable[$file]['mtime'] = $fileMtime;
        $this->cacheTable[$file][$line] = $indent;
    }

    function getIndent($file, $line, $fileMtime = false) {
        if ($this->cacheTable === false) {
            $this->cacheTable = array();
            $this->load();
        }
        $res = false;
        if (isset($this->cacheTable[$file])) {
            if ($fileMtime && $this->cacheTable[$file]['mtime'] < $fileMtime) {
                unset($this->cacheTable[$file]);
            } else {
                if (isset($this->cacheTable[$file][$line]))
                    $res = $this->cacheTable[$file][$line];
            }
        }
        return $res;
    }

    abstract function load();

    abstract function save($fromDestructor = false);

    abstract function clear();

    function __destruct() {
        $this->save(true);
    }

}