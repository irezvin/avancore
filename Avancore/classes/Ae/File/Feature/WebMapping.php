<?php

class Ae_File_Feature_WebMapping extends Ae_File_Feature {

    var $id = 'webMapping';
    
    var $dirToWebMap = array();
    
    static $rpCache = array();
    
    function listClonedProps() {
        return array_merge(parent::listClonedProps(), array('dirToWebMap'));
    }
    
    function getFileUrl($translatedPath = false) {
        if ($translatedPath === false) $translatedPath = $this->file->getTranslatedPath();
        if (file_exists($translatedPath)) {
            $rp = realpath($translatedPath);
        } else {
            $rp = $translatedPath;
        }
        $res = false;
        foreach ($this->dirToWebMap as $dir => $web) {
            if (isset(self::$rpCache[$dir])) $dp = self::$rpCache[$dir];
            else { 
                $dp = realpath($dir);
                if ($dp === false) $dp = $dir;
                self::$rpCache[$dir] = $dp;
            }
            if (substr($rp, 0, strlen($dp)) == $dp) {
                $res = $web.substr($rp, strlen($dp));
                break;
            }
        }
        return $res;
    }
    
}