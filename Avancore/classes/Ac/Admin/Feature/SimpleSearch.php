<?php

class Ac_Admin_Feature_SimpleSearch extends Ac_Admin_Feature {
    
    var $partName = 'simpleSearch';
    
    var $placeholder = 'Filter...';
    
    var $colNames = array();

    var $controlExtra = array();
    
    var $partExtra = array();

    function applyToFilterFormSettings(& $formSettings) {
        
        if (!$this->colNames) return;

        Ac_Util::ms($formSettings, array(
            'controls' => array(
                $this->partName => Ac_Util::m(array(
                    'class' => 'Ac_Form_Control_Text',
                    'caption' => 'Filter',
                    'htmlAttribs' => array(	
                        'placeholder' => $this->placeholder,
                        'onchange' => 'document.aForm.submit();',
                        'size' => 20,
                    ),
                ), $this->controlExtra),
            ),
        ));
        
    }
    
    function getSqlSelectSettings() {
        if (!$this->colNames) return array();
        return array(
            'parts' => array(
                $this->partName => Ac_Util::m(array(
                    'class' => 'Ac_Sql_Filter_Substring',
                    'colNames' => $this->colNames,
                ), $this->partExtra),
            ),
        );
    }
    
    
    
    
}