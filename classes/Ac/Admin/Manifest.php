<?php

class Ac_Admin_Manifest extends Ac_Prototyped {

    var $requiredPermissions = [];
    
    var $menuEntries = [];
    
    var $groupId = null;
    
    var $groupTitle = null;
    
    function setMenuEntries(array $menuEntries) {
        $this->menuEntries = Ac_Prototyped::factoryCollection($menuEntries, 'Ac_Admin_MenuEntry');
    }
    
    function hasPublicVars() {
        return true;
    }
    
    
}