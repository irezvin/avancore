<?php

class Ac_Etl_Log_Stats_Html_Widget {

    protected $id = false;

    static $lastId = 0;
    
    protected $widgetsHtml = false;
    
    protected $widgets = false;
    
    function getWidgets($class = false) {
        if ($this->widgets === false) {
            $this->widgets = $this->doCreateWidgets();
            if (!is_array($this->widgets)) $this->widgets = array();
        }
        if ($class !== false) {
            foreach($this->widgets as $w) {
                if ($w instanceof $class) {
                    $res[] = $w;
                }
            }
        } else {
            $res = $this->widgets;
        }
        return $res;
    }
    
    protected function doCreateWidgets() {
    }
    
    function __construct() {
        $this->id = 'stats'.(self::$lastId++);
    }
    
    function getId() {
        return $this->id;
    }
    
    function getAssetLibs() {
        $res = array();
        foreach ($this->getWidgets() as $w) {
            $res = array_unique(array_merge($res, $w->getAssetLibs()));
        }
        return $res;
    }
    
    function getPreJs() {
        $res = array();
        foreach ($this->getWidgets() as $w) {
            $res = array_merge($res, $w->getPreJs());
        }
        return $res;
    }
    
    function getPostJs() {
        $res = array();
        foreach ($this->getWidgets() as $w) {
            $res = array_merge($res, $w->getPostJs());
        }
        return $res;
    }
    
    function getWidgetsHtml() {
        $res = array();
        foreach ($this->getWidgets() as $w) {
            $res[] = $w->getHtml();
        }
        return $res;
    }
    
    function getHtml() {
        return implode("\n", $this->getWidgetsHtml());
    }
    
    function showValue($value) {
        if ((string) $value === '0') $value = '&nbsp;';
        if (is_float($value) && $value != round($value)) {
            $value = round($value, 3);
        }
        echo $value;
    }
    
}