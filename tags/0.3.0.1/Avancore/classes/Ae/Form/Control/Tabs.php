<?php

Ae_Dispatcher::loadClass('Ae_Form_Control_Composite');

/**
 * Shows tabs/accordeon/etc and controls inside them
 */
class Ae_Form_Control_Tabs extends Ae_Form_Control_Composite {
    
    var $templateClass = 'Ae_Form_Control_Template_Tabs';
    
    var $templatePart = 'tabs';
    
    function addDisplayChild(& $child) {
        if (!is_a($child, 'Ae_Form_Control_Tabs_Sheet'))
            trigger_error ("Only Ae_Form_Control_Tabs_Sheet controls are allowed to be inside the Tabs control");

        return parent::addDisplayChild($child);        
    }
    
    var $hasValue = false;
    
}

?>