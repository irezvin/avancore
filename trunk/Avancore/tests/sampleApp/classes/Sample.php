<?php 

class Sample extends Sample_DomainBase {
    
    function getAppClassFile() {
        return __FILE__;
    }

    /**
     * @return Sample
     */
    static function getInstance($id = null) {
        return Ac_Application::getApplicationInstance('Sample', $id);
    }

}
