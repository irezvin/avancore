<?php

class Ac_File_Feature_MimeInfo_Finfo extends Ac_File_Feature_MimeInfo {
    
    /**
     * @var finfo
     */
    protected $finfo = false;
    

    function setFinfo(finfo $finfo = null) {
        $this->finfo = $finfo;
    }

    /**
     * @return finfo
     */
    function getFinfo() {
        if (!$this->finfo) {
            $this->finfo = new finfo();
        }
        return $this->finfo;
    }    
    
    protected function doGetMime($path) {
        return $this->getFinfo()->file($path, FILEINFO_MIME);
    }
    
}