<?php

interface Ac_I_ArraySerializable_Extended extends Ac_I_ArraySerializable {
 
    /**
     * @return array(parentClass, parentMemberName)
     */
    function getSerializationParentInfo();
    
    /**
     * @return array (myProperty => array(arrayKey, defaultClass, crArgs))
     * crArgs => array(keyA, keyB, keyC) <- constructor args map
     * crArgs = false -- just copy $this->$myProperty to/from $array[$arrayKey]
     */
    function getSerializationMap();
    
    
}