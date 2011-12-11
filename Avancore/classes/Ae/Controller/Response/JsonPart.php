<?php

/**
 * Response part which placeholder can be safely inserted into Json (Javascript) array and screened there
 * (it means they can be inserted into javascript array in the string in the javascript array)
 */
class Ae_Controller_Response_JsonPart extends Ae_Controller_Response_Part {

    protected $evaluateAllBeforeReplace = true;
    
    private $ev = array();
    
    /**
     * (non-PHPdoc)
     * @see Ae_Controller_Response_Part::createPlaceholder()
     * 
     * Screened placeholders always end with string "\." (a backslash and a dot) so we can later determine
     * it's screening level by counting the slashes.
     */

    protected function doGetRandomPlaceholder() {
        $res = '{'.md5(microtime().rand()).md5(microtime().rand()).'\\.}'; // it's even more random :)
        return $res;
    }
    
    protected function doReplacePlaceholders($content) {
        if ($this->evaluateAllBeforeReplace) $this->ev = array_merge($this->ev, $this->evaluateAllPlaceholders());
        $content = preg_replace_callback("#(\\{[a-z0-9]{64})([\\\\]+)\\.\\}#", array($this, 'replacePlaceholder'), $content);
        return $content;
    }
    
    function screen($string) {
        if (is_object($string)) {
            $v = new Pm_Js_Var($string);
            $res = $v->toJson();
        } else $res = addcslashes($string, "'\"\n\r\t\0\\");
        return $res;
    }
    
    function replacePlaceholder($matches) {
        /*
         * We have three parts in regex above
         * 1) "{<64-digit hash>" - match #1
         * 2) one or more backslashes - match #2
         * 3) - it's not in $matches - ".}"
         */
        $ph = $matches[1].'\\.}'; // full placeholder will consist of match #1, a backslash and a dot
        if (isset($this->placeholders[$ph])) { // it's our client
            $depth = log(strlen($matches[2]), 2); // count screening depth
            if (ceil($depth) != $depth) trigger_error("Cannot correctly determine screening depth in placeholder '{$matches[0]}' "
                ."- number of backslashes should be power of 2, but is ".strlen($matches[2]), E_USER_NOTICE);
            if (is_array($this->ev) && isset($this->ev[$ph])) $res = $this->ev[$ph];
                else $res = $this->evaluatePlaceholder($this->placeholders[$ph]);
            
            // screen result to given level    
            for ($i = 0; $i < $depth; $i++) {
                $res = $this->screen($res);
            }                        
            
        } else {
            $res = $matches[0]; // leave it as is
        }
        return $res;
    }
    
    function __sleep() {
        return array_diff(array_keys(get_object_vars($this)), array('ev'));
    }
    
}