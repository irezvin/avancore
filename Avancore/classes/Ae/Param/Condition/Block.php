<?php

if (!class_exists('Ae_Param_Condition')) {
    if (class_exists('Ae_Dispatcher')) Ae_Dispatcher::loadClass('Ae_Param_Condition');
        else require('Ae/Param/Condition.php');
}

/**
 * Condition that can contain many conditions.
 * @package Avancore
 * @subpackage Params
 */
class Ae_Param_Condition_Block extends Ae_Param_Condition {
    
}

?>