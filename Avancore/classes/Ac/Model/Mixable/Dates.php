<?php

class Ac_Model_Mixable_Dates extends Ac_Mixable {

    /**
     * @var Ac_Model_Object
     */
    protected $mixin = false;
    
    protected $mixinClass = 'Ac_Model_Object';
    
    protected $dateCreatedField = 'dateCreated';

    protected $dateModifiedField = 'dateModified';

    protected $dateDeletedField = 'dateDeleted';
    
    protected $deletedField = false;
    
    /**
     * @var bool
     */
    protected $useGmt = false;

    protected $dateFormat = 'Y-m-d H:i:s';
    
    protected $useSetFields = false;

    function setDateCreatedField($dateCreatedField) {
        $this->dateCreatedField = $dateCreatedField;
    }

    function getDateCreatedField() {
        return $this->dateCreatedField;
    }

    function setDateModifiedField($dateModifiedField) {
        $this->dateModifiedField = $dateModifiedField;
    }

    function getDateModifiedField() {
        return $this->dateModifiedField;
    }

    function setDateDeletedField($dateDeletedField) {
        $this->dateDeletedField = $dateDeletedField;
    }

    function getDateDeletedField() {
        return $this->dateDeletedField;
    }    
 
    function setDeletedField($deletedField) {
        $this->deletedField = $deletedField;
    }

    function getDeletedField() {
        return $this->deletedField;
    }    
   
    /**
     * @param bool $useGmt
     */
    function setUseGmt($useGmt) {
        $this->useGmt = $useGmt;
    }

    /**
     * @return bool
     */
    function getUseGmt() {
        return $this->useGmt;
    }    

    function setDateFormat($dateFormat) {
        $this->dateFormat = $dateFormat;
    }

    function getDateFormat() {
        return $this->dateFormat;
    }    
     
    function listNonMixedMethods() {
        return array(
            'setDateCreatedField', 'getDateCreatedField', 
            'setDateModifiedField', 'getDateModifiedField', 
            'setDateDeletedField', 'getDateDeletedField',
            'setDateFormat', 'getDateFormat',
            'setDeletedField', 'getDeletedField',
            'setUseGmt', 'getUseGmt',
        );
    }

    protected function getDate() {
        $res = $this->useGmt? gmdate($this->dateFormat) : date($this->dateFormat);
        return $res;
    }
    
    function onCreate() {
        if ($this->dateCreatedField !== false && !$this->mixin->isChanged($this->dateCreatedField, false)) 
            $this->mixin->setField($this->dateCreatedField, $this->getDate(), false, true);
    }
    
    function onBeforeSave(& $result) {
        if ($this->deletedField !== false && $this->dateDeletedField !== false) {
            if (($deleted = $this->mixin->getField($this->deletedField)) 
                && $this->mixin->isChanged($this->deletedField, false)) {
                if (!$this->mixin->isChanged($this->dateDeletedField, false))
                    $this->mixin->setField($this->dateDeletedField, $this->getDate(), false, true);
            }
        }
        if (($this->dateModifiedField !== false) && !$this->mixin->isChanged($this->dateModifiedField, false) && $this->mixin->getChanges()) {
            $this->mixin->setField($this->dateModifiedField, $this->getDate(), false, true);
        }
    }
    
}