<?php

class Ac_Image_Upload extends Ac_Upload_File {
    
    /**
     * @var Ac_Image_Upload_Manager
     */
    var $_manager = false;
    
    var $_width = false;
    
    var $_height = false;
    
    var $_thumbWidth = false;
    
    var $_thumbHeight = false;
    
    var $_thumbPath = false;
    
    var $_error = false;
    
    function _setUserData($data) {
        $this->_error = false;
        parent::_setUserData($data);
        if (!$data['error'] && !$this->_error && $this->_downloadPath) {
            $p = $this->getProcessor();
            $p->setFile($this->_downloadPath);
            $this->_error = $p->getError();
            $this->_width = $p->getWidth();
            $this->_height = $p->getHeight();
        }
    }
    
    function _setInternalData($internalData) {
        //Ac_Debug_FirePHP::getInstance()->log($internalData, 'image');
        parent::_setInternalData($internalData);
        if (isset($internalData['thumbnail']) && strlen($internalData['thumbnail'])) 
            $this->setThumbContent($internalData['thumbnail']);
        if (isset($internalData['width'])) $this->_width = $internalData['width'];
        if (isset($internalData['height'])) $this->_height = $internalData['height'];
        if ($this->isWithThumbnails()) {
            if (isset($internalData['thumbWidth'])) $this->_thumbWidth = $internalData['thumbWidth'];
            if (isset($internalData['thumbHeight'])) $this->_thumbHeight = $internalData['thumbHeight'];
        }
    }
    
    function getInternalData($withFile = true) {
        $res = parent::getInternalData();
        $res['width'] = $this->getWidth();
        $res['height'] = $this->getHeight();
        if ($this->isWithThumbnails()) {
            $res['thumbWidth'] = $this->getThumbWidth();
            $res['thumbHeight'] = $this->getThumbHeight();
            if ($withFile) $res['thumbnail'] = $this->getThumbnail();
        }
        return $res; 
    }
    
    function _doOnStore() {
        if ($this->_contentPath && !$this->_thumbPath) {
            $tp = $this->getThumbPath();
            $p = $this->getProcessor();
            $p->setFile($this->_contentPath);

            // Do not increase images that are smaller than thumbnails
            //if ($this->_manager->getThumbWidth() < $this->getWidth() || $this->_manager->getThumbHeight() < $this->getHeight()) {
                $p->makeThumbnail($tp, $this->_manager->getThumbWidth(), $this->_manager->getThumbHeight());
            //} else {
            //    $this->_thumbWidth = $this->getWidth();
            //    $this->_thumbHeight = $this->getHeight();
            //}
            $this->_thumbWidth = (int) $p->getThumbWidth();
            $this->_thumbHeight = (int) $p->getThumbHeight();
            if (strlen($e = $p->getError())) {
                $this->_error = $e;
                $tp = false;
                $this->_thumbPath = null;
            }
        }
    }
    
    function getWidth() {
        return $this->_width;
    }
    
    function getHeight() {
        return $this->_height;
    }
    
    function getThumbWidth() {
        return $this->_thumbWidth;
    }
    
    function getThumbHeight() {
        return $this->_thumbHeight;
    }
    
    function getThumbnail() {
        $res = false;
        if ($this->_thumbPath) $res = file_get_contents($this->_thumbPath);
        return $res;
    }
    
    function getError() {
        return $this->_error; 
    }
    
    function getThumbPath() {
        if ($this->_thumbPath === false) {
            do {
                $this->_thumbPath = $this->_manager->getUploadsCacheDir().'/tmb_'.md5(rand());
            } while (is_file($this->_thumbPath));
            touch($this->_thumbPath);
        }
        return $this->_thumbPath;
    }
    
    function hasThumbnail() {
        return strlen($this->_thumbPath) && is_file($this->_thumbPath) && filesize($this->_thumbPath);
    }

    /**
     * @return Ac_Image_Processor
     */
    function getProcessor() {
        return $this->_manager->getImageProcessor();
    }

    function getThumbUrl() {
        if ($this->_manager->getThumbLink() && strlen($this->getId())) $res = $this->_manager->getThumbLink().$this->getId();
            else $res = false;
        return $res; 
    }
    
    function getImgTag($attribs = array(), $forThumb = false, $withWidthAndHeight = false) {
        $res = false;
        if ($forThumb) {
            $u = $this->getThumbUrl();
            if ($withWidthAndHeight) {
                $attribs['width'] = $this->getThumbWidth();
                $attribs['height'] = $this->getThumbWidth();
            }
        } else { 
            $u = $this->getViewUrl();
            if ($withWidthAndHeight) {
                $attribs['width'] = $this->getWidth();
                $attribs['height'] = $this->getHeight();
            }
        }
        if (strlen($u) && !isset($attribs['src'])) $attribs['src'] = $u;
        if (isset($attribs['src'])) {
            $res = Ac_Util::mkElement('img', false, $attribs);
        }
        return $res;
    }
    
    function getDescr() {
        //$res = "{$this->_filename} ({$this->_width} x {$this->_height}, {$this->_mimeType}, Size: ".sprintf('%0.2f K', $this->getContentSize()/1024).")";
        $res = "{$this->_filename} ({$this->_width} x {$this->_height}, {$this->_mimeType})";
        if ($this->hasThumbnail()) $res = $this->getThumbWithImageLink(array('target' => '_blank')) . '<br />'. $res;
        //      if ($this->_manager->getDownloadLink()) {
//          $res .= ' '.$this->getDownloadLinkElement('Download');
//      }
//      if ($this->_manager->getViewLink()) {
//          $res .= ' '.$this->getViewLinkElement('View');
//      }
        return $res;
    }
    
    /**
     * @param string $kind image|thumb|auto 'auto' means 'thumb', if thumb is present 
     */
    function getImageTag($kind = 'image', $attribs = array()) {
        if ($kind !== 'image' && $kind !== 'thumb') $kind = $this->isWithThumbnails()? 'thumb' : 'image';
        switch ($kind) {
            
            case 'image': 
                $attribs['src'] = $this->getViewUrl();
                if (!isset($attribs['width']) && ($v = $this->getWidth())) $attribs['width'] = $v; 
                if (!isset($attribs['height']) && ($v = $this->getHeight())) $attribs['height'] = $v;
                break;
            
            case 'thumb': 
                $attribs['src'] = $this->getThumbUrl();
                if (!isset($attribs['width']) && ($v = $this->getThumbWidth())) $attribs['width'] = $v; 
                if (!isset($attribs['height']) && ($v = $this->getThumbHeight())) $attribs['height'] = $v;
                //if ($v = $this->getThumbWidth()) $attribs['width'] = $v; 
                //if ($v = $this->getThumbHeight()) $attribs['height'] = $v;
                break;
        }
        
        $res = Ac_Util::mkElement('img', false, $attribs);
        return $res;
    }
    
    function getThumbWithImageLink($linkAttribs = array(), $imgAttribs = array()) {
        $res = $this->getViewLinkElement($this->getImageTag('thumb', $imgAttribs), $linkAttribs);
        return $res;
    }
    
    function streamThumb($cleanAndDie = false, $download = false) {
        if (!$this->_contentPath) return false;
        if ($cleanAndDie) while(ob_get_level()) ob_end_clean();
        header('content-type: '.$this->_mimeType);
        if ($download) header('content-disposition: attachment; filename="'.$this->getFilename().'"');
        if ($t = $this->getThumbnail()) echo $t; else echo $this->getContent();
        if ($cleanAndDie) die();
    }
    
    function isWithThumbnails() {
        return $this->_manager->getWithThumbnails();
    }
    
    function setThumbContent($content) {
        file_put_contents($this->getThumbPath(), $content);
    }
    
}

