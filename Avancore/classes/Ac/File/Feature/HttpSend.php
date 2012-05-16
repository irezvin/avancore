<?php

class Ac_File_Feature_HttpSend extends Ac_File_Feature {

    var $sendNotModified = true;
    
    var $sendFileName = true;
    
    var $download = false;
    
    protected function listClonedProps() {
        return array_merge(parent::listClonedProps(), array('sendNotModified', 'sendFileName', 'download'));
    }
    
    function getIfModifiedSince() {
        $res = false;
        if (isset($_SERVER) && isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $res = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
        }
        return $res;
    }
    
    function stream() {
        if ($this->file->exists()) {
            if ($this->sendNotModified) {
                if (($ms = $this->getIfModifiedSince())) {
                    $mt = $this->file->getMTime();
                    if ($mt <= $ms) $this->replyNotModified();
                        else $this->replyStream($withFilename);
                } else {
                    $this->replyStream($withFilename);
                }
            }
        } else {
            throw new Exception("Cannot stream() non-existing file");
        }
    }
    
    function replyNotModified() {
        while (ob_get_level()) ob_end_clean();
        header('HTTP/1.1 304 Not Modified', true, 304);
        exit();
    }
    
    function replyStream() {
        while (ob_get_level()) ob_end_clean();
        if (($mf = $this->manager->getMimeFeature())) {
            $mime = $mf->getMime($this->file);
            if (strlen($mime)) {
                header('Content-Type: '.$mime);
            }
            header('Content-Length: '.$this->file->getFileInfo()->getSize());
            header('Last-Modified: '.date('r', $this->file->getMtime()));
        }
        $h = false;
        if ($this->download) {
            $h = 'Content-Disposition: attachment';
        } 
        if ($this->sendFileName) {
            if (!strlen($h)) $h = 'Content-Disposition: inline';
            $h .= '; filename='.basename($this->file->getTranslatedPath());
        }
        if (strlen($h)) header($h);
        readfile($this->file->getTranslatedPath());
        exit();
    }
    
}