<?php

class Ac_Value_Impl_DeferredString {

    static $strings = array();
    
    static function getMarkForString(Ac_I_DeferredString $string) {
        $res = $string->getDeferredStringMark();
        if (!strlen($res)) {
            $res = '###'.md5(microtime().rand()).'_'.md5(microtime().rand()).'###';
            if (!isset($this->strings[$res])) $this->strings[$res] = $string;
            $string->setDeferredStringMark($res);
        }
        return $res;
    }
    
    static function unlist(Ac_I_DeferredString $string) {
        unset($this->strings[$this->getMarkForString($string)]);
    }
    
}