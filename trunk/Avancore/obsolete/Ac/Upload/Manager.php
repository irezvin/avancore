<?php

class Ac_Upload_Manager {
    
    var $_uploadsLifetime = 3600;
    
    var $_uploadsCacheDir = false;
    
    var $_uploadClass = 'Ac_Upload_File';
    
    var $_downloadLink = false;
    
    var $_viewLink = false;
    
    /**
     * @var Ac_Upload_Storage_Abstract
     */
    var $_storage = false;
    
    var $storageOptions = array('class' => 'Ac_Upload_Storage');
    
    var $lngDownloadLabel = 'Download';
    
    var $lngViewLabel = 'View';
    
    var $showDownloadAndViewLinks = true;
    
    function Ac_Upload_Manager ($options = array()) {
        Ac_Util::bindAutoparams($this, $options);
    }
    
    function _setUploadsCacheDir($value) {
        $this->_uploadsCacheDir = $value;
    }
    
    function getUploadsCacheDir() {
        return $this->_uploadsCacheDir;
    }
    
    function validateId($id) {
        if ($s = $this->getStorage()) {
            $res = $s->validateId($id);
        } else {
            $res = preg_match('#^\w+$#', $id);
        }
        return $res;
    }
    
    function _getCacheFileName($id) {
        $res = $this->_uploadsCacheDir.'/u-'.$id;
        return $res;
    }
    
    /**
     * @return Ac_Upload_File
     */
    function getUpload($id, $load = false) {
        $res = false;
//        Ac_Debug_FirePHP::getInstance()->log($id, 'getUpload()');
        if ($this->validateId($id)) {
            if (is_file($fn = $this->_getCacheFileName($id)) && filesize($fn)) {
                $r = unserialize(file_get_contents($fn));
                if (is_a($r, $this->_uploadClass)) {
                    $r->setUploadManager($this);
                    $res = $r;
                }
            } else if ($load && ($internalData = $this->_doLoadInternalData($id))) {
//                Ac_Debug_FirePHP::getInstance()->log($fn, 'loading internal data');
                $res = $this->factory(array('internalData' => $internalData));
                $this->_cacheUpload($res);
            }
//            Ac_Debug_FirePHP::getInstance()->log($fn, 'getUpload()');
        } else {
//            Ac_Debug_FirePHP::getInstance()->log('id invalid', 'getUpload()');
        }
        return $res;
    }
    
    /**
     * @param Ac_Upload_File $upload
     */
    function storeUpload(& $upload) {
        return $this->_doStoreUpload($upload);
    }
    
    function tempStoreUpload(& $upload) {
        return $this->_cacheUpload($upload);
    }
    
    /**
     * @param Ac_Upload_File $upload
     */
    function deleteUpload($id) {
        $res = $this->_doDeleteUpload($id);
        $this->purgeUploads($id);
        return $res;
    }
    
    function _doLoadInternalData($id) {
        if ($s = $this->getStorage()) {
            $res = $s->loadInternalData($id);
        } else {
            trigger_error ("Attempt to getUpload() with unitialized storage", E_USER_WARNING);
            $res = false;
        }
        return $res;
    }
    
    /**
     * @param Ac_Upload_File $upload
     */
    function _doStoreUpload(& $upload) {
        if ($s = $this->getStorage()) {
            $res = $s->saveUpload($upload);
        } else {
            trigger_error ("Attempt to storeUpload() with unitialized storage", E_USER_WARNING);
            $res = false;
        }
        return $res;
    }
    
    function _doDeleteUpload($id) {
        if ($s = $this->getStorage()) {
            $res = $s->deleteUpload($id);
        } else {
            trigger_error ("Attempt to deleteUpload() with unitialized storage", E_USER_WARNING);
            $res = false;
        }
        return $res;
    }
    
    /**
     * @param Ac_Upload_File $upload
     */
    function _cacheUpload(& $upload) {
        if (!strlen($id = $upload->getId())) {
            $upload->setId($id = $this->getNextUploadId());
        }
        return file_put_contents($this->_getCacheFileName($id), serialize($upload));
    }
    
    function purgeUploads($id = false) {
        if ($id === false) {
            $ff = scandir($this->uploadsCacheDir);
            foreach ($ff as $f) {
                $fn = $this->uploadsCacheDir.'/'.$f;
                $t = time();
                if (!strncmp($f, 'u-', 2) && is_file($fn) && $t - filemtime($fn) > $this->uploadsLifetime) {
                    $this->purgeUploads($id = substr($f, 2));
                }
            }
        } else {
            if (is_file($fn = $this->_getCacheFileName($id))) {
                $u = $this->getUpload($id);
                $u->clean();
                unlink($fn);
            } else {
            }
        }
    }
    
    function getNextUploadId() {
        do {
            $id = md5(rand().microtime());
        } while (is_file($f = $this->_getCacheFileName($id)));
        touch($f);
        return $id;
    }
    
    function setDownloadLink($downloadLink) {
        $this->_downloadLink = $downloadLink;
    }
    
    function getDownloadLink() {
        return $this->_downloadLink;
    }

    function setViewLink($viewLink) {
        $this->_viewLink = $viewLink;
    }
    
    function getViewLink() {
        return $this->_viewLink;
    }
    
    /**
     * @return Ac_Upload_Storage_Abstract
     */
    function getStorage() {
        if ($this->_storage === false) {
            if (is_array($this->storageOptions)) $this->_storage = Ac_Util::factoryWithOptions($this->storageOptions, 'Ac_Upload_Storage', 'class', false);
        }
        return $this->_storage;
    }
    
    /**
     * @param Ac_Upload_Storage_Abstract $storage
     */
    function setStorage(& $storage) {
        $this->_storage = $storage;
    }
    
    /**
     * @return Ac_Upload_File
     */
    function & factory ($options = array()) {
        $options['uploadManager'] = $this;
        $res = Ac_Util::factoryWithOptions($options, $this->_uploadClass);
        return $res;
    }
    
}

?>