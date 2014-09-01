<?php

class Sample_Person extends Sample_Person_Base_Object {

    static $destructed = array();
    
    static $lastInstanceId = 0;
    
    var $instanceId = 0;
    
    function __construct($mapperOrMapperClass = null) {
        parent::__construct($mapperOrMapperClass);
        $this->instanceIf = self::$lastInstanceId++;
    }
    
    function intResetReferences() {
        return parent::intResetReferences();
    }
    
    function __destruct() {
        self::$destructed[$this->instanceId] = true;
    }
    
    /*
    function getOwnPropertiesInfo() {
        return Ac_Util::m(parent::getOwnPropertiesInfo(), array(
            '' => array(
                'caption' => '',
                'dataType' => '',
                'controlType' => '',
            ),
        ));
    }
    
    function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), array(
            '', '',
        ));
    }
    
    function listOwnLists() {
        return array_merge(parent::listOwnLists(), array(
            '' => '', '' => '',
        ));
    }
    
    function listOwnAssociations() {
        return array_merge(parent::listOwnAssociations(), array(
            '' => '', '' => '',
        ));
    }
    
    */
}

