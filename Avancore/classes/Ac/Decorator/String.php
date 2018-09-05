<?php

/**
 * Mostly an alias for Ac_Param_Filter_String, but with some tweaks: stripTags disabled, usePregReplace enabled
 */
class Ac_Decorator_String extends Ac_Param_Filter_String {
    
    var $stripTags = false;
    
    var $usePregReplace = true;
    
    var $trim = false;
    
}