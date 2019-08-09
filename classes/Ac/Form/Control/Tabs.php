<?php

/**
 * Shows tabs/accordeon/etc and controls inside them
 */
class Ac_Form_Control_Tabs extends Ac_Form_Control_Composite {
    
    var $templateClass = 'Ac_Form_Control_Template_Tabs';
    
    var $templatePart = 'tabs';
    
    var $defaultChildClass = 'Ac_Form_Control_Tabs_Sheet';
    
    var $initialTab = false;
    
    function addDisplayChild($child) {
        if (!is_a($child, 'Ac_Form_Control_Tabs_Sheet'))
            trigger_error ("Only Ac_Form_Control_Tabs_Sheet controls are allowed to be inside the Tabs control");

        return parent::addDisplayChild($child);        
    }
    
    var $hasValue = false;
    
}

