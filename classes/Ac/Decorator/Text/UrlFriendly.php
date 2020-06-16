<?php

/**
 * Transforms text to url-friendly segments like 'Foo bar - Baz' to 'foo-bar-baz'
 * Transliterates non-ascii characters
 */
class Ac_Decorator_Text_UrlFriendly extends Ac_Decorator {
    
    var $delimiter = "-";
    
    static $defaultTransliterator = false;
    
    /**
     * @var Transliterator
     */
    protected $transliterator = false;

    function setTransliterator(Transliterator $transliterator) {
        $this->transliterator = $transliterator;
    }

    /**
     * @return Transliterator
     */
    function getTransliterator($asIs = false) {
        if (!$asIs && $this->transliterator === false) {
            if (!self::$defaultTransliterator) self::$defaultTransliterator = Transliterator::create("latin");
            return self::$defaultTransliterator;
        }
        return $this->transliterator;
    }    
    
    function apply($value) {
        $t = $this->getTransliterator();
        if ($t) $value = $t->transliterate($value);
        $value = strtolower($value);
        $value = preg_replace("/[^a-z0-9-\\/_ ]+/", "", $value);
        $value = preg_replace("/[- \\/_]+/", $this->delimiter, trim($value));
        return $value;
    }
   
}