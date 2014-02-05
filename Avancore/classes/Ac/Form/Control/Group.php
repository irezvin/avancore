<?php

/**
 * Allows to visually group several controls together
 */
class Ac_Form_Control_Group extends Ac_Form_Control_Composite {
    
    var $templateClass = 'Ac_Form_Control_Template_Basic';
    
    var $templatePart = 'controlGroup';
    
    /**
     * How controls are arranged
     *
     * @var string 'table' | 'list' | 'horizontal'
     */
    var $style = 'table';
    
    var $preHtml = false;
    
    var $postHtml = false;
    
    var $groupTemplatePart = false;
    
    var $hasValue = true;
        
}

