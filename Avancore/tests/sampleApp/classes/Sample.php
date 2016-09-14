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
        //require(dirname(__FILE__).'/Sample/Plugin.php');
        return array_merge(parent::doGetCoreMixables(), array(array('class' => 'Sample_Plugin')));
    }
    
    protected function doGetMapperPrototypes() {    
        return Ac_Util::m(parent::doGetMapperPrototypes(), array(
            'Sample_Shop_Spec_Mapper_Food' => array('class' => 'Sample_Shop_Spec_Mapper_Food'),
            'Sample_Shop_Spec_Mapper_Computer' => array('class' => 'Sample_Shop_Spec_Mapper_Computer'),
            'Sample_Shop_Spec_Mapper_Laptop' => array('class' => 'Sample_Shop_Spec_Mapper_Laptop'),
        ));
    }

}
