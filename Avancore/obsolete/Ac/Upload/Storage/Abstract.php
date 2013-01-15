<?php

class Ac_Upload_Storage_Abstract {
    
    function Ac_Upload_Storage_Abstract ($options = array()) {
        if (!strcasecmp(get_class($this), 'Ac_Upload_Storage_Abstract')) trigger_error("Attempt to instantiate abstract class", E_USER_ERROR);
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
     * @param Ac_Upload_File $upload
     */
    function saveUpload(& $upload) {
        trigger_error("Call to abstract method", E_USER_ERROR);
    }
    
    function deleteUpload($id) {
        trigger_error("Call to abstract method", E_USER_ERROR);
    }
    
}

?>