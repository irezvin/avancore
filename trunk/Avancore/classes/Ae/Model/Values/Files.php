<?php

/**
 * Implements values list from the records
 */
class Ae_Model_Values_Files extends Ae_Model_Values {

    var $dirName = false;
    
    var $fileNameRegex = false;
    
    var $dirNameRegex = false;
    
    var $stripExtensions = false;
    
    var $recursive = false;
    
    function _doDefaultGetValueList() {
        
        $disp = & Ae_Dispatcher::getInstance();
        $dir = $disp->getDir();
        if (strlen($this->dirName)) $dir .= '/'.$this->dirName;
        $baseDir = realpath($dir).'/';
        if (DIRECTORY_SEPARATOR == '\\') $baseDir = str_replace(DIRECTORY_SEPARATOR, "/", $baseDir);
        $files = Ae_Util::listDirContents($dir, $this->recursive, array(), $this->fileNameRegex, $this->dirNameRegex);
        
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

?>