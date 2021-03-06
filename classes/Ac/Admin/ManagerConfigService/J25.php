<?php

// Replaces some functions of Ac_Legacy_Adapter since it does not exist anymore

class Ac_Admin_ManagerConfigService_J25 extends Ac_Admin_ManagerConfigService {
    
    function getManagerJsAssets() {
        return array(
            '{AC}/util.js',
            '{AC}/avanManager.js',
            '{AC}/managerRendererJ25.js',
        );
    }
      
    function getDefaultImagePrefix() {
        $base = $this->application->getAdapter()->getLiveSite();
        return $base.'/administrator/templates/hathor/images/toolbar';
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
            'publish' => array(
                'image' => 'icon-32-publish.png', 
            ), 
            'unpublish' => array(
                'image' => 'icon-32-unpublish.png', 
            ), 
            
        );
    }

    
}