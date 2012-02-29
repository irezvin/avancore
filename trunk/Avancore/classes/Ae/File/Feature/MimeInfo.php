<?php

abstract class Ae_File_Feature_MimeInfo extends Ae_File_Feature {

    const id = 'mimeInfo';
    
    var $id = Ae_File_Feature_MimeInfo::id;
    
    function detect(Ae_File $file) {
        return false;
    }
    
    function getMime($fileOrPath) {
        if ($fileOrPath instanceof Ae_File) $fileOrPath = $fileOrPath->getTranslatedPath();
        $res = $this->doGetMime($fileOrPath);
        return $res;
    }
    
    abstract protected function doGetMime($path);
    
    /**
     * @return Ae_File_Feature_MimeInfo
     */
    static function createDefault() {
        $res = null;
        if (class_exists('finfo')) $res = new Ae_File_Feature_MimeInfo_Finfo();
        return $res;
    }
    
}