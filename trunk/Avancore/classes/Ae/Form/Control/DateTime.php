<?php

class Ae_Form_Control_DateTime extends Ae_Form_Control_Date {
    
    var $defaultExternalFormat = 'd.m.Y H:i:s';
    
    var $defaultInternalFormat = 'Y-m-d H:i:s';
    
    var $calendarDateFormat = false;
    
    var $defaultCalendarDateFormat = '%d.%m.%Y %H:%M:%S';
    
    function getExtraJson() {
        return array(
            'showsTime' => true,
        );        
    }
    
}

?>