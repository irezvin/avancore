<?php

/**
 * Implements values list from the records
 */
class Ac_Model_Values_Files extends Ac_Model_Values {

    var $dirName = false;
    
    var $dirNameCallback = false;
    
    var $fileNameRegex = false;
    
    var $dirNameRegex = false;
    
    var $stripExtensions = false;
    
    var $recursive = false;
    
    function _doDefaultGetValueList() {
        
        $dirName = $this->dirName;
        if ($dirName === false && $this->dirNameCallback) {
            $cb = $this->dirNameCallback;
            if (is_array($cb) && $cb[0] === true) $cb[0] = $this->data;
            $dirName = call_user_func($cb, $this);
        }
        $baseDir = realpath($dirName).'/';
        if (DIRECTORY_SEPARATOR == '\\') $baseDir = str_replace(DIRECTORY_SEPARATOR, "/", $baseDir);
        $files = Ac_Util::listDirContents($dir, $this->recursive, array(), $this->fileNameRegex, $this->dirNameRegex);
        
        if ($this->stripExtensions) foreach ($files as $k => $v) {
            $pi = pathinfo($v);
            $files[$k] = basename($pi['basename'], strlen($pi['extension'])? '.'.$pi['extension'] : '');
            if (strlen($pi['dirname'])) $files[$k] = $pi['dirname'] .'/'.$files[$k];
        }
        
        $res = array();
        foreach ($files as $k => $v) {
            if (!strncmp($v, $baseDir, $l = strlen($baseDir))) $v = substr($v, $l);
            $res[$v] = $v;
        }
        return $res;
        
    }
        
}

