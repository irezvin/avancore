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
    
    protected function doGetCoreMixables() {
        require(dirname(__FILE__).'/Sample/Plugin.php');
        return array_merge(parent::doGetCoreMixables(), array(new Sample_Plugin()));
    }

}
