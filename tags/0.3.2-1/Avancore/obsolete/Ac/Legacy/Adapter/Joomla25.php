<?php

class Ac_Legacy_Adapter_Joomla25 extends Ac_Legacy_Adapter_Joomla15 {
    
    function getManagerJsAssets() {
        return array(
            '{AE}util.js',
            '{AE}avanManager.js',
            '{AE}managerRendererJ25.js',
        );
    }
      
    function getDefaultImagePrefix() {
        return 'templates/'.JApplication::getInstance('administrator')->getTemplate().'/images/toolbar';
    }
    
    protected function getDefaultToolbarImagesMap() {
        return array(
            'new' => array(
                'image' => 'icon-32-new.png', 
            ), 
            'edit' => array(
                'image' => 'icon-32-edit.png', 
            ), 
            'delete' => array(
                'image' => 'icon-32-delete.png', 
            ), 
            'apply' => array(
                'image' => 'icon-32-apply.png', 
            ), 
            'save' => array(
                'image' => 'icon-32-save.png', 
            ), 
            'saveAndAdd' => array(
                'image' => 'icon-32-save.png', 
            ), 
            'cancel' => array(
                'image' => 'icon-32-cancel.png', 
            ), 
        );
    }
    
}
