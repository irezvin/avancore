<?php

/**
 * Contains various meta-info about the mappers 
 */
class Ae_Model_MapperInfo {
    
    /**
     * Class of mapper that is described by this info
     * @var string
     */
    var $mapperClass = false;
    
    /**
     * Entity caption in singular form
     * @var string
     */
    var $singleCaption = false;
    
    /**
     * Entity caption in plural form
     * @var string
     */
    var $pluralCaption = false;
    
    /**
     * Whether entities should have their own admin UI
     * @var bool
     */
    var $hasUi = false;
    
    var $allowSubManagers = false;
    
    /**
     * Feature configs that will be applied by Ae_Admin_Manager
     * @var array
     */
    var $adminFeatures = array();
    
    function Ae_Model_MapperInfo ($mapperClass, $options = array()) {
        Ae_Util::simpleBindAll($options, $this);
        $this->mapperClass = $mapperClass;
    }
    
    
    
}

?>