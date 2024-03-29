<?php

/**
 * Responsible for connection of Ac_Admin_Manager with underlying model.
 */
class Ac_Admin_Datalink extends Ac_Prototyped {
    
   /**
    * Manager that datalink works with 
    * @var Ac_Admin_Manager
    * @access protected
    */
   var $_manager = false;
   
   /**
    * Processing object that datalink works with
    * @var Ac_Admin_Processing
    */
   var $_processing = false;
   
   function getCacheId() {
       return false;
   }
   
   /**
    * Sets manager that datalink will be used with 
    * @param Ac_Admin_Manager $manager
    */
   function setManager($manager) {
       $this->_manager = $manager;
   }
   
   /**
    * Sets processing that datalink will be used with 
    * @param Ac_Admin_Processing $processing
    */
   function setProcessing($processing) {
       $this->_processing = $processing;
   }
   
   /**
    * Tells whether this record can be processed by the form or the processing command
    *
    * @param Ac_Model_Object $record
    * @return bool
    */ 
   function canProcessRecord($record) {
       return true; 
   }
   
   /**
    * Sets default values for the new record
    *
    * @param Ac_Model_Object $record
    */
   function setRecordDefaults($record) {
   }
   
   /**
    * Executes code before record is bound from the request. Can be useful to set some defaults that were
    * not provided in the request. Can also change request data.
    *
    * @param Ac_Model_Object $record
    * @param array $requestData Data from the form (not bound to the record yet)
    */
   function doBeforeBindRecord(& $record, $requestData) {
       
   }
   
   /**
    * Executes code after record is bound from the request. Can be useful to set some values that should
    * mandatorily be in the record. Can also change request data (but it doesn't seem very useful).
    *
    * @param Ac_Model_Object $record
    * @param array $requestData Data from the form (that was already bound to the record)
    */
   function doAfterBindRecord(& $record, $requestData) {
       
   }
   
   /**
    * Returns criteria that is _always_ applied to the records that have to be processed, edited or
    * displayed in the list. Should return FALSE if no such criteria should be applied.  
    *
    * @return string|bool
    */
   function getQueryPart() {
       return false;
   }
   
   /**
    * @param array $formPreset
    */
   function onManagerFormPreset(& $formPreset) {
   }
   
   /**
    * @param Ac_Form $form
    */
   function onManagerFormCreated(& $form) {
   }
   
   /**
    * @param array $columns
    */
   function onManagerColumnsPreset(& $columns) {
       
   }
    
}

