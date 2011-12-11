<?php

class Ae_Upload_Storage_Abstract {
    
    function Ae_Upload_Storage_Abstract ($options = array()) {
        if (!strcasecmp(get_class($this), 'Ae_Upload_Storage_Abstract')) trigger_error("Attempt to instantiate abstract class", E_USER_ERROR);
    }
    
    function validateId($id) {
        trigger_error("Call to abstract method", E_USER_ERROR);
    }
    
    /**
     * @return array | false
     */
    function & loadInternalData($id) {
        trigger_error("Call to abstract method", E_USER_ERROR);
    }
    
    /**
     * @param Ae_Upload_File $upload
     */
    function saveUpload(& $upload) {
        trigger_error("Call to abstract method", E_USER_ERROR);
    }
    
    function deleteUpload($id) {
        trigger_error("Call to abstract method", E_USER_ERROR);
    }
    
}

?>