<?php

class Sample_Person extends Sample_Person_Base_Object {

    static $destructed = array();
    
    static $lastInstanceId = 0;
    
    var $instanceId = 0;
    
    // override default value for testing purposes
    var $isSingle = 0;
    
    function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), array('birthYear'));
    }
    
    function getBirthYear() {
        return Ac_Util::date($this->birthDate, 'Y');
    }
    
    function setBirthYear() {
    }
    
    function __construct($mapperOrMapperClass = null) {
        parent::__construct($mapperOrMapperClass);
        $this->instanceId = self::$lastInstanceId++;
    }
    
    function intResetReferences() {
        return parent::intResetReferences();
    }
    
    function __destruct() {
        parent::__destruct();
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

