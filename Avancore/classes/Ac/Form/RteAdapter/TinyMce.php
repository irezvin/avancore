<?php

class Ac_Form_RteAdapter_TinyMce extends Ac_Form_RteAdapter {
    
    var $jsOptions = array(
        'theme' => 'advanced',
        'plugins' => 'table,contextmenu,autoresize,inlinepopups',
        'theme_advanced_buttons3_add'  => "table",
    );
    
    var $jsPath = '{AE}/web/assets/vendor/tinymce/jscripts/tiny_mce/tiny_mce.js';
    
    function hasPublicVars() {
        return true;
    }
        
    function getJsLibs() {
        return array();
    }
    
    function getCssLibs() {
        return array();
    }

    function getInitJavascript($editorIdAttribute) {
        $options = $this->jsOptions;
        $options['mode'] = "exact";
        $options['elements'] = $editorIdAttribute;
        return new Ac_Js_Call("tinyMCE.init", array($options));
    }

}