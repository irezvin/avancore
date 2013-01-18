<?php

/**
 * Converts model properties into controls
 *
 */
class Ac_Form_Converter { 
    
    /**
     * @param Ac_Model_Property $property
     */
    function getControlSettings($property) {
        $conf = array();
        
        if (isset($property->controlType) && strlen($property->controlType)) 
            $ct = $property->controlType;
        else
            $ct = false;

        if (isset($property->dataType) && strlen($property->dataType)) 
            $dt = $property->dataType;
        else
            $dt = false;
            
        switch(true) {
            
            case isset($property->controlClass) && strlen($property->controlClass):
                $conf['class'] = $property->controlClass;
                break;

            case $property->dataType == 'dateTime':
                $conf['class'] = 'Ac_Form_Control_DateTime';
                break;
                
            case $property->dataType == 'date':
                $conf['class'] = 'Ac_Form_Control_Date';
                break;
                
            case $property->dataType == 'bool':
                $conf['class'] = 'Ac_Form_Control_Toggle';
                break;
            
            case $property->controlType === 'selectList':
                $conf['class'] = 'Ac_Form_Control_List';
                $rec = $property->srcObject;
                if (isset($property->objectPropertyName) && strlen($opn = $property->objectPropertyName)) {
                    $op = $rec->getPropertyInfo($opn);
                    if (strlen($op->caption)) $conf['caption'] = $op->caption;
                    if (isset($op->description) && strlen($op->description)) $conf['description'] = $op->description;
                }
                if (isset($property->valuesGetter)) $conf['valuesGetter'] = $property->valuesGetter;
                
                break;
            default:
                $conf['class'] = 'Ac_Form_Control_Text';
                break;
        }
        
        if (isset($property->displayOrder)) $conf['displayOrder'] = $property->displayOrder;
        
        return $conf;
    }
    
}

?>
