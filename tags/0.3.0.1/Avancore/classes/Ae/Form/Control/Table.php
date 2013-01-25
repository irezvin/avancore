<?php

Ae_Dispatcher::loadClass('Ae_Form_Control');

/**
 * Text field, textarea, rte, hidden
 */
class Ae_Form_Control_Table extends Ae_Form_Control {
    
    var $templateClass = 'Ae_Form_Control_Template_Table';
    
    var $templatePart = 'table';
    
    var $tableOptions = array('editorSize' => 25, 'tablePrototype' => array('rows' => array(''), 'columns' => array('')));
    
    function getTableJson() {
        $res = $this->tableOptions;
        $res['inputNamePrefix'] = $this->_context->mapParam('value');
        return $res;
    }
    
    function getDataJson() {
        $res = array('tables' => $this->getValue());
        return $res;
    }
    
}

?>