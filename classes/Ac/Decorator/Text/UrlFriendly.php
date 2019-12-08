<?php

/**
 * Transforms text to url-friendly segments like 'Foo bar - Baz' to 'foo-bar-baz'
 * Transliterates non-ascii characters
 */
class Ac_Decorator_Text_UrlFriendly extends Ac_Decorator {
    
    var $delimiter = "-";
    
    function apply($value) {
        if (class_exists('Transliterator', false) && ($t = Transliterator::create("latin"))) {
            $value = $t->transliterate($value);
        } else {
            $t = new Translit;
            $value = $t->Transliterate($value);
        }
        $value = strtolower($value);
        $value = preg_replace("/[^a-z0-9-\\/_ ]+/", "", $value);
        $value = preg_replace("/[- \\/_]+/", $this->delimiter, trim($value));
        return $value;
    }
   
}