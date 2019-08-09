<?php

abstract class Ac_File_Feature_MimeInfo extends Ac_File_Feature {

    const id = 'mimeInfo';
    
    var $id = Ac_File_Feature_MimeInfo::id;
    
    function detect(Ac_File $file) {
        return false;
    }
    
    function getMime($fileOrPath) {
        if ($fileOrPath instanceof Ac_File) $fileOrPath = $fileOrPath->getTranslatedPath();
        $res = $this->doGetMime($fileOrPath);
        return $res;
    }
    
    abstract protected function doGetMime($path);
    
    /**
     * @return Ac_File_Feature_MimeInfo
     */
    static function createDefault() {
        $res = null;
        if (class_exists('finfo')) $res = new Ac_File_Feature_MimeInfo_Finfo();
        return $res;
    }
    
}