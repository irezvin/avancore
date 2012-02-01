<?php

class Ae_Debug_FirePHP {
    
    /**
     * @return FirePHP 
     */
    static function getInstance() {
        if (!class_exists('FirePHP', false)) {
            require(Ae_Avancore::getInstance()->getAdapter()->getVendorPath().'/FirePHPCore/FirePHP.class.php');
            if (!(defined('_DEPLOY_USE_FIRE_PHP') && _DEPLOY_USE_FIRE_PHP || Ae_Avancore::getInstance()->getAdapter()->getConfigValue('useFirePHP', 0)))
                FirePHP::getInstance(true)->setEnabled(false);
        }
        return FirePHP::getInstance(true);
    }
    
}
