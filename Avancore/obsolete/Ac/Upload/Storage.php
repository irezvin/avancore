<?php

class Ac_Upload_Storage {

    /**
     * @var Path where the files are stored
     */
    var $storagePath = false;
    
    function __construct ($options = array()) {
        Ac_Util::bindAutoparams($this, $options);
        if ($this->storagePath === false) {
            $app = Ac_Application::getDefaultInstance();
            if ($app) $this->storagePath = $app->getAdapter()->getVarCachePath();
        }
    }

    function _id2filename($id) {
        return $this->storagePath.md5($id);
    }
    
    function _getAutoId() {
        do {
            $id = rand();
        } while (is_file($this->_id2filename($id)));
        return $id; 
    }
    
    /**
     * @param Ac_Upload_File $upload
     */
    function saveUpload($upload) {
        if (!strlen($id = $upload->getId())) $upload->setId($id = $this->_getAutoId());
        $res = file_put_contents($this->_id2filename($id), serialize($upload->getInternalData()));
        return $res;
    }
    
    /**
     * @return array | false
     */
    function loadInternalData($id) {
        $n = $this->_id2filename($id);
//        Ac_Debug_FirePHP::getInstance()->log($n, "id2filename");
        $res = false;
        if (is_file($n)) {
            $res = unserialize(file_get_contents($n));
        }
        return $res;
    }
    
    function deleteUpload($id) {
        $n = $this->_id2filename($id);
        if (is_file($n)) {
            $res = unlink($n);
        } else {
            $res = true;
        }
        return $res;
    }
    
    function validateId($id) {
        $res = preg_match('#^\w+$#', $id);
        return $res;
    }
    
//  function getStoragePath() {
//      
//  }
    
}

