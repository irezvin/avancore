<?php

class Ae_Debug_FirePHP {
    
    /**
     * @return FirePHP 
     */
    static function getInstance() {
        if (!class_exists('FirePHP', false)) {
            require(Ae_Dispatcher::getInstance()->getVendorDir().'/FirePHPCore/FirePHP.class.php');
            if (!(defined('_DEPLOY_USE_FIRE_PHP') && _DEPLOY_USE_FIRE_PHP || Ae_Dispatcher::getInstance()->config->getValue('useFirePHP', 0)))
                FirePHP::getInstance(true)->setEnabled(false);
        }
        return FirePHP::getInstance(true);
    }
    
}