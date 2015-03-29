<?php

class Sample_Shop_Product extends Sample_Shop_Product_Base_Object {

    var $pageTitle = '';
    var $metaDescription = '';
    var $metaKeywords = '';
    var $metaNoindex = '';
    var $upcCode = '';

    protected function listOwnProperties() {
        return array_merge(
            parent::listOwnProperties(), 
            array_keys($this->getMapper()->getMixable('meta')->getDefaults()),
            array_keys($this->getMapper()->getMixable('upc')->getDefaults())
        );
    }
    
    protected function listOwnDataProperties() {
        return array_merge(
            parent::listOwnDataProperties(), 
            array_keys($this->getMapper()->getMixable('meta')->getDefaults()),
            array_keys($this->getMapper()->getMixable('upc')->getDefaults())
        );
    }
    
    function tracksPk() {
        return true;
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

