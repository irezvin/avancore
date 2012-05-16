<?php

class Ac_Debug_Log_FileWriter extends Ac_Debug_Log_AbstractWriter {
    
    protected $filename = false;
    
    protected $file = false;

    protected $dir = false;
    
    var $closeOnWrite = false;

    function setDir($dir) {
        if ($dir !== ($oldDir = $this->dir)) {
            $this->dir = $dir;
            $this->close();
        }
    }

    function getDir() {
        return $this->dir;
    }
    
    function setFilename($filename) {
        if ($filename !== ($oldFilename = $this->filename)) {
            $this->filename = $filename;
            $this->close();
        }
    }
    
    function getFilename() {
        return $this->filename;
    }
    
    function guessFilename() {
        if (session_id()) {
            $res = 'sess_'.session_id().'.log'; 
        } else {
            $res = date('Y-m-d');
            if (isset($_SERVER['REMOTE_ADDR'])) $res .= '_'.$_SERVER['REMOTE_ADDR'];
            $res = $res.'.log';
        }
        return $res;
    }
    
    function guessDir() {
        if (class_exists('Ac_Dispatcher', false) && Ac_Dispatcher::hasInstance())
            $res = Ac_Dispatcher::getInstance()->config->cachePath;
        elseif (defined('_DEPLOY_CACHE_PATH')) $res = _DEPLOY_CACHE_PATH;
        elseif (defined('_PAX_TMP_PATH')) $res = _PAX_TMP_PATH;
        else $res = '.';
        return $res;
    }
    
    function getFullFilename() {
        $f = strlen($this->filename)? $this->filename : $this->guessFilename();
        if (substr($f, 0, 1) !== '/') {
            $dir = strlen($this->dir)? $this->dir : $this->guessDir();
            $f = rtrim($dir, '/').'/'.$f;
        }
        return $f;
    }
    
    protected function getFileHandle() {
        if ($this->file === false) {
            $this->file = fopen($this->getFullFilename(), "a");
        }
        return $this->file;
    }
    
    function close() {
        if ($this->file) {
            fclose($this->file);
            $this->file = false;
        }
    }

    function write(Ac_Debug_Log $log = null, array $args) {
        if (($f = $this->getFileHandle())) {
            fputs($f, $this->format($args));
        }
        if ($this->closeOnWrite) {
            $this->close();
        }
    }
    
}