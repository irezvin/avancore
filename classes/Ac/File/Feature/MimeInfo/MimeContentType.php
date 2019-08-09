<?php

abstract class Ac_File_Feature_MimeInfo_MimeContentType extends Ac_File_Feature_MimeInfo {

    abstract protected function doGetMime($path) {
        return mime_content_type($path);
    }
    
}