<?php

abstract class Ae_File_Feature_MimeInfo_MimeContentType extends Ae_File_Feature_MimeInfo {

    abstract protected function doGetMime($path) {
        return mime_content_type($path);
    }
    
}