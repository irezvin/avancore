<?php

class Ac_Form_RteAdapter extends Ac_Prototyped {
    
    protected static $defaultInstance = null;
    
    static function getDefaultInstance() {
        if (!self::$defaultInstance) {
            $config = Ac_Dispatcher::getInstance()->config->getValue('rteDefaultInstance', false);
            if (!is_array($config) || !$config) self::$defaultInstance = new Ac_Form_RteAdapter_TinyMce();
            else self::$defaultInstance = Ac_Prototyped::factory($config, 'Ac_Form_RteAdapter');
        }
        return self::$defaultInstance;
    }
    
    static function setDefaultInstance($defaultInstance = null) {
        self::$defaultInstance = $defaultInstance;
    }
    
    function getHtmlForEditor(Ac_Form_Control_Text $editor, & $id = false, Ac_Legacy_Controller_Response_Html $response = null) {
        $tpl = new Ac_Form_Control_Template_Basic();
        ob_start();
        $tpl->_showTextArea($editor, $id);
        $html = ob_get_clean();
        
        if (($s = $this->getInitJavascript($id)) !== false) {
            $html .= new Ac_Js_Script($s);
        }
        
        if (!$response) $response = Ac_Legacy_Controller_Response_Global::r();
        
        foreach ($this->getJsLibs() as $lib) $response->addJsLib ($lib, false);
        foreach ($this->getCssLibs() as $lib) $response->addCssLib ($lib, false);
        
        return $html;
    }
        
    function getJsLibs() {
        return array();
    }
    
    function getCssLibs() {
        return array();
    }

    function getInitJavascript($editorIdAttribute) {
        return false;
    }
        
}