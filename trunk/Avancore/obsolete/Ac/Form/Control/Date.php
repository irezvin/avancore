<?php

class Ac_Form_Control_Date extends Ac_Form_Control {
    
    var $defaultExternalFormat = 'd.m.Y';
    
    var $defaultInternalFormat = 'Y-m-d';
    
    var $externalFormat = false;
    
    var $internalFormat = false;
    
    var $showCalendar = true;
    
    var $showCalendarButton = false;
    
    var $calendarDateFormat = false;
    
    var $defaultCalendarDateFormat = '%d.%m.%Y';
    
    var $templateClass = 'Ac_Form_Control_Template_Basic';
    
    var $templatePart = 'date';
    
    var $autoId = true;
    
    /**
     * @var string blue|blue2|brown|green|system|tas|win2k-1|win2k-2|win2k-cold-1|win2k-cold-2
     */
    var $calendarSkin = 'win2k-1';
    
    function getCalendarDateFormat() {
        if ($this->calendarDateFormat === false) {
            if ($prop = $this->getModelProperty()) {
                if (isset($prop->calendarDateFormat) && strlen($prop->calendarDateFormat))
                    $res = $prop->calendarDateFormat;
                elseif (isset($prop->outputDateFormat) && ($prop->outputDateFormat)) $res = $this->convertToCalendarFormat($prop->outputDateFormat);
                else $res = false;
            } else {
                $res = $this->defaultCalendarDateFormat;
            }
        } else $res = $this->calendarDateFormat;
        return $res;
    }
    
    function getExternalFormat() {
        if ($this->externalFormat === false) {
            if ($prop = $this->getModelProperty() && strlen($prop->outputDateFormat)) {
                $res = $prop->outputDateFormat;
            } else {
                $res = $this->defaultExternalFormat;
            }
        } else $res = $this->externalFormat;
        return $res;
    }
    
    function getInternalFormat() {
        if ($this->internalFormat === false) {
            if ($prop = $this->getModelProperty() && strlen($prop->internalDateFormat)) {
                $res = $prop->internalDateFormat;
            } else {
                $res = $this->defaultInternalFormat;
            }
        } else $res = $this->internalFormat;
        return $res;
    }
    
    function getValue() {
        $res = parent::getValue();
        if (($if = $this->getInternalFormat()) !== false) {
            $convertedValue = Ac_Util::date($res, $if, true, $wasZero);
            if (!$wasZero && strlen($convertedValue)) $res = $convertedValue;
        }
        return $res;
    }
    
    function getDisplayValue() {
        $res = parent::getValue();
        if (($ef = $this->getExternalFormat()) !== false) {
            $convertedValue = Ac_Util::date($res, $ef, true, $wasZero);
            if (!$wasZero && strlen($convertedValue)) $res = $convertedValue;
        }
        return $res;
    }
    
    function convertToCalendarFormat($dateFormat) {
        return strtr($dateFormat, array(
            'Y' => '%Y',
            'm' => '%m',
            'd' => '%d',
            'H' => '%H',
            'i' => '%M',
            's' => '%S',
        ));
    }
    
    function getExtraJson() {
        return array(
        );        
    }
    
}

?>