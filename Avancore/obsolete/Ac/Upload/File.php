<?php

class Ac_Upload_File {
    
    var $_id = false;
    
    /**
     * Name of file that is shown to the user
     */
    var $_filename = false;
    
    /**
     * Path of the downloaded file (if it wasn't renamed)
     * @var string
     */
    var $_downloadPath = false;
    
    var $_mimeType = false;
    
    var $_contentPath = false;
    
    /**
     * timestamp: when the file was creates/uploaded/downloaded to the file cache
     */
    var $_createdTs = false;
    
    /**
     * Whether the file has been uploaded to the web client by the user instead of being downloaded from the RPC server 
     */
    var $_providedByUser = false;
    
    /**
     * @var Ac_Upload_Manager
     */ 
    var $_manager = false;
    
    var $_error = false;
    
    function __construct($options = array()) {
        Ac_Util::bindAutoparams($this, $options, true, array('uploadManager'));
    }
        
    function _setUserData($data) {
        if (!isset($data['error']) || !$data['error']) {
            $this->_providedByUser = true;
            $this->_filename = $data['name'];
            $this->_downloadPath = $data['tmp_name'];
            $this->_mimeType = $data['type'];
        } else {
            $this->_error = $data['error'];
        }
    }
    
    function _setInternalData($internalData) {
        $this->_filename = $internalData['filename'];
        $this->_mimeType = $internalData['mimeType'];
        $this->_id = $internalData['id'];
        $this->_providedByUser = false;
        if (isset($internalData['content'])) $this->setContent($internalData['content']);
    }
    
    function setContent($content) {
        file_put_contents($this->_getContentPath(), $content);
    }
    
    function getInternalData($withFile = true) {
        $res = array();
        $res['mimeType'] = $this->getMimeType();
        $res['filename'] = $this->getFilename();
        if ($withFile) $res['content'] = $this->getContent();
        if (strlen($this->_id)) $res['id'] = $this->_id;
        return $res;
    } 
    
    function getContent() {
        $res = false;
        if ($this->_contentPath) $res = file_get_contents($this->_contentPath);
        return $res;
    }
    
    function getContentSize() {
        $res = false;
        if ($this->_contentPath && is_file($this->_contentPath)) $res = filesize($this->_contentPath);
        return $res;
    }
    
    function getMimeType() {
        return $this->_mimeType;    
    }
    
    function getFilename() {
        return $this->_filename;
    }
    
    function getDescr() {
        $res = $this->_filename.' ('.$this->_mimeType.', Size : '.sprintf('%0.2f K', $this->getContentSize()/1024).')';
        if ($this->_manager->showDownloadAndViewLinks) {
            if ($this->_manager->getDownloadLink()) {
                $res .= ' '.$this->getDownloadLinkElement($this->_manager->lngDownloadLabel);
            }
            if ($this->_manager->getViewLink()) {
                $res .= ' '.$this->getViewLinkElement($this->_manager->lngViewLabel);
            }
        }
        return $res;
    }
    
    function _getContentPath() {
        if ($this->_contentPath === false) {
            do {
                $this->_contentPath = $this->_manager->getUploadsCacheDir().'/content_'.md5(rand());
            } while (is_file($this->_contentPath));
        }
        return $this->_contentPath;
    }
    
    /**
     * @param Ac_Upload_Manager $manager
     */
    function setUploadManager($manager) {
        if ($manager !== false && !is_a($manager, 'Ac_Upload_Manager')) trigger_error('$manager should be either FALSE or an instance of Ac_Upload_Manager');
        $this->_manager = $manager;
    }
    
    function __sleep() {
        if ($this->_providedByUser && strlen($this->_downloadPath) && !strlen($this->_contentPath)) {
            $contentPath = $this->_getContentPath();
            if (is_uploaded_file($this->_downloadPath)) move_uploaded_file($this->_downloadPath, $contentPath);
                else copy($this->_downloadPath, $contentPath);
            $this->_downloadPath = false;
            $this->_doOnStore();
        }
        $r = array_diff(array_keys(get_object_vars($this)), array('_manager'));
        return $r;
    }
    
    function _doOnStore() {
    }
    
    function stream($cleanAndDie = false, $download = false) {
        if (!$this->_contentPath) return false;
        if ($cleanAndDie) while (ob_get_level()) ob_end_clean();
        header('content-type: '.$this->_mimeType);
        if ($download) header('content-disposition: attachment; filename="'.$this->getFilename().'"');
        echo $this->getContent();
        if ($cleanAndDie) die();
    }
    
    function setId($id) {
        $this->_id = $id;
    }
    
    function getId() {
        return $this->_id;
    }
    
    function save() {
        return $this->_manager->storeUpload($this);
    }
    
    function clean() {
        if ($this->_contentPath) unlink($this->_contentPath);
    }

    function getViewUrl() {
        if ($this->_manager->getViewLink() && strlen($this->getId())) $res = $this->_manager->getViewLink().$this->getId();
            else $res = false;
        return $res; 
    }
    
    function getViewLinkElement($body, $attribs = array()) {
        if (!isset($attribs['href'])) $attribs['href'] = $this->getViewUrl();
        $res = Ac_Util::mkElement('a', $body, $attribs);
        return $res;
    }
    
    function getDownloadUrl() {
        $res = false;
        if (strlen($this->_id) && $l = $this->_manager->getDownloadLink()) {
            $res = $l . $this->_id;
        }
        return $res;
    }
    
    function getDownloadLinkElement($body, $attribs = array()) {
        $attribs['href'] = $this->getDownloadUrl();
        $res = Ac_Util::mkElement('a', $body, $attribs);
        return $res;
    }
    
    function getError() {
        return $this->_error;
    }
    
}

