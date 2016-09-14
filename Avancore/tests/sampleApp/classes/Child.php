<?php 

class Child extends Child_DomainBase {
    
    function getAppClassFile() {
        return __FILE__;
    }

    /**
     * @return Child 
     */
    static function getInstance($id = null) {
        return Ac_Application::getApplicationInstance('Child', $id);
    }

}
