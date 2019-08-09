<?php

class Ac_Etl_Column_Joiner extends Ac_Etl_Column {

    
    /**
     * @var array
     */
    protected $colList = array();
    
    protected $glue = false;
    
    /**
     * @var bool
     */
    protected $unset = false;
    
    function apply(Ac_I_Param_Source $source, array & $destRecords, array & $errors = array()) {
        
        $values = array();
        
        foreach($this->colList as $col) {
            $p = array($this->destTableId, 0, $col);
            $values[] = Ac_Util::getArrayByPath($destRecords, $p);
            if ($this->unset) Ac_Util::unsetArrayByPath($destRecords, $p);
        }
        
        $val = implode($this->glue, $values);
        
        $this->putData($val, $destRecords);
        
        $res = true;
        
        return $res;
    }    

    function setLeaveOriginalValue($leaveOriginalValue) {
        $this->leaveOriginalValue = (bool) $leaveOriginalValue;
    }

    /**
     * @return bool
     */
    function getLeaveOriginalValue() {
        return $this->leaveOriginalValue;
    }    

    function setColList(array $colList) {
        $this->colList = $colList;
    }

    /**
     * @return array
     */
    function getColList() {
        return $this->colList;
    }    
 
    function setGlue($glue) {
        $this->glue = $glue;
    }

    function getGlue() {
        return $this->glue;
    }    

    function setUnset($unset) {
        $this->unset = (bool) $unset;
    }

    /**
     * @return bool
     */
    function getUnset() {
        return $this->unset;
    }    
   
    
}