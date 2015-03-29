<?php

class Ac_Model_Association_Referencing extends Ac_Model_Association_ModelObject {
    
    /**
     * @param Ac_Model_Object $object
     */
    function beforeSave($object, & $errors) {
    }
    
    /**
     * @param Ac_Model_Object $object
     */
    function afterSave($object, & $errors) {
        $res = null;
        
        if (!$object instanceof Ac_Model_Object) throw Ac_E_InvalidCall::wrongClass('object', $object, 'Ac_Model_Object');
        
        $f = $this->getInMemoryField();
        if (($val = $object->$f)) {
            if (!$this->storeReferencing($object, $val, $errors)) $res = false;
        }
        
        return $res;        
    }
    
    protected function storeReferencing(Ac_Model_Object $object, $recordOrRecords, & $errors) {
        $res = true;
        
        $errorKey = $this->getErrorKey();
        $fieldLinks = $this->getFieldLinks();

        if (is_array($recordOrRecords)) $r = $recordOrRecords;
            else $r = array($recordOrRecords);
            
        foreach (array_keys($r) as $k) {
            $rec = $r[$k];
            foreach ($fieldLinks as $sf => $df) $rec->$df = $object->$sf;
            if ($rec->getChanges() && !$rec->_isDeleted) {
                if (!$rec->store()) {
                    $errors[$errorKey][$k] = $rec->getErrors();
                    $res = false;
                }
            }
        }
        
        return $res;
    }
    
}