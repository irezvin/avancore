<?php

class Ae_Upload_Storage {

    /**
     * @var Path where the files are stored
     */
    var $storagePath = false;
    
    function Ae_Upload_Storage ($options = array()) {
        Ae_Util::bindAutoparams($this, $options);
        if ($this->storagePath === false && class_exists('Ae_Dispatcher')) {
            $d = & Ae_Dispatcher::getInstance();
            if ($d) {
                $this->storagePath = $d->getCacheDir() . '/';
            }
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
     * @param Ae_Upload_File $upload
     */
    function saveUpload(& $upload) {
        if (!strlen($id = $upload->getId())) $upload->setId($id = $this->_getAutoId());
        $res = file_put_contents($this->_id2filename($id), serialize($upload->getInternalData()));
        return $res;
    }
    
    /**
     * @return array | false
     */
    function & loadInternalData($id) {
        $n = $this->_id2filename($id);
//        Ae_Debug_FirePHP::getInstance()->log($n, "id2filename");
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

?>