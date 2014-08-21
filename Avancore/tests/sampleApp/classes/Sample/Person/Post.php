<?php

class Sample_Person_Post extends Sample_Person_Post_Base_Object {

    function intResetReferences() {
        return parent::intResetReferences();
    }
    
    
    /*
    protected function getOwnPropertiesInfo() {
        return Ac_Util::m(parent::getOwnPropertiesInfo(), array(
            '' => array(
                'caption' => '',
                'dataType' => '',
                'controlType' => '',
            ),
        ));
    }
    
    protected function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), array(
            '', '',
        ));
    }
    
    protected function listOwnLists() {
        return array_merge(parent::listOwnLists(), array(
            '' => '', '' => '',
        ));
    }
    
    protected function listOwnAssociations() {
        return array_merge(parent::listOwnAssociations(), array(
            '' => '', '' => '',
        ));
    }
    
    */
}

