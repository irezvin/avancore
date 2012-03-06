<?php

/**
 * Allows to visually group several controls together
 */
class Ae_Form_Control_Tabs_Sheet extends Ae_Form_Control_Group {
    
    var $sheetAttribs = array();
    
    var $headerAttribs = array();
    
    function getHeaderAttribs() {
        $res = Ae_Util::m(array('href' => '#'), $this->headerAttribs);
        if (isset($res['rel'])) unset($res['rel']);
        return $res;
    }

    
    function getSheetAttribs() {
        return Ae_Util::m(array('class' => 'tabContent'), $this->sheetAttribs);
    }

}

?>