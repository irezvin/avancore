<?php

class Ac_Cg_Dictionary extends Ac_Cg_Base {
    
    var $translations = array();
    
    var $pluralForms = array();
    
    var $singularForms = array();

    var $fallbackToConstants = false;
    
    var $constants = array();
    
    var $alwaysUseConstants = true;
    
    var $constantPrefix = false;
    
    var $defaultLanguage = 'en';
    
    var $useInflector = true;
    
    protected $configVars = array(
        'alwaysUseConstants', 
        'fallbackToConstants', 
        'constantPrefix', 
        'defaultLanguage', 
        'useInflector'
    );
    
    function Ac_Cg_Dictionary ($config = array()) {
        foreach ($this->configVars as $p) 
            if (isset($config[$p])) 
                $this->$p = $config[$p];
        if (isset($config['data'])) $this->addData($config['data']);
    }
    
    function getConfig() {
        $res = array_intersect_key(get_object_vars($this), array_flip($this->configVars));
        return $res;
    }
    
    function addData($data) {
        if (!is_array($data)) $res = false;
        else {
            foreach ($data as $word => $details) if(is_array($details)) {
                if (isset($details['plural'])) {
                    $this->pluralForms[$si = Ac_Cg_Inflector::humanize($word)] = $plu = Ac_Cg_Inflector::humanize($details['plural']);
                    $this->singularForms[$plu] = $si;
                    unset($details['plural']);
                }
                if (isset($details['singular'])) {
                    $this->singularForms[$plu = Ac_Cg_Inflector::humanize($word)] = $si = Ac_Cg_Inflector::humanize($details['singular']);
                    $this->pluralForms[$si] = $plu;
                    unset($details['singular']);
                }
                foreach ($details as $lang => $translation) {
                    $this->translations[strtolower($lang)] = Ac_Cg_Inflector::humanize($translation);
                }
            }
            $res = true;
        }
        return $res;
    }
    
    /**
     * Translates string to a specified language.
     * 
     * This function can return following values:
     * - FALSE if translation is not found and $returnFalseIfNotFound is true; 
     * - constant name if $this->alwaysUseConstants is TRUE
     * - constant name if translation is not found and $this->fallbackToConstants is TRUE; 
     * - translated string if translation is found and $this->alwaysUseConstants is FALSE; 
     * - $string if translation is not found and both $this->fallbackToConstants and $this->alwaysUseConstants are FALSE.
     *
     * @param string $string String to translate
     * @param string $langName Language identifier
     * @param bool $returnFalseIfNotFound If true, FALSE will be returned if proper translation is not found. Otherwise $string or constant identifier will be returned (depending on $this->fallbackToConstants setting)
     * 
     * @return false|string 
     */
    function translate($string, $langName, $returnFalseIfNotFound = false) {
        $string = Ac_Cg_Inflector::humanize($string);
        $langName = strtolower($langName);
        if (isset($translations[$string]) && is_array($translations[$string]) && isset($translations[$string][$langName])) {
            $translation = $translations[$string][$langName];
            if ($this->alwaysUseConstants) {
                $res = $this->_makeConstant($string, $langName, $translation);
            } else {
                $res = $translation;
            }
        } else {
            if ($returnFalseIfNotFound) $res = false;
            else {
                if ($this->fallbackToConstants || $this->alwaysUseConstants) {
                    $res = $this->_makeConstant($string, $langName, $string);
                } else {
                    $res = $string;
                }
            }
        }
        return $res;
    }
    
    function _makeConstant($string, $langName, $value) {
        $constantName = Ac_Cg_Inflector::definize($string);
        if (strlen($this->constantPrefix)) $constantName = strtoupper($this->constantPrefix).'_'.$constantName;
        $this->constants[$constantName][$langName] = $value;
        return $constantName; 
    }
    
    /**
     * Returns constant values for specified language
     *
     * @param string $langName
     * @param bool $returnMissingConstants Whether to return default values
     * @return unknown
     */
    function getConstants($langName, $returnMissingConstants = true) {
        $constantNames = array_keys($this->constants);
        $langName = strtolower($langName);
        $res = array();
        foreach ($constantNames as $c) {
            if (isset($this->constants[$c][$langName])) {
                $res[$c] = $this->constants[$c][$langName];
            } else {
                if ($returnMissingConstants) {
                    if (($l = strlen($this->constantPrefix)) && !strncmp($c, strtoupper($this->constantPrefix.'_'), $l + 1)) $id = substr($c, $l + 1);
                        else $id = $c;
                    $res[$c] = Ac_Cg_Inflector::humanize($id);
                }
            }
        }
        return $res;
    }
    
    /**
     * Tries to create full list of constants using all identifiers found in $singular, $plural and $translation members.
     * Constants which don't have translations will receive identifiers as their values.
     * Modifies $this->constants array.
     * 
     * @return array $this->constants (in its resulting state)
     */
    function populateAllConstants() {
        $allConstants = array();
        $allIdentifiers = array_unique(array_merge(array_keys($this->translations), array_values($this->singularForms), array_values($this->pluralForms)));
        $langs = $this->getAllLanguages();
        foreach ($allIdentifiers as $id) {
            $const = Ac_Cg_Inflector::definize($id);
            foreach ($langs as $lang) {
                if ($lang == $this->defaultLanguage) $tr = $id; 
                else {
                    if (isset($translations[$id]) && is_array($translations[$id]) && isset($translations[$id][$lang])) {
                       $tr = $translations[$id][$lang];
                    } else {
                        $tr = $id;
                    }
                }
                $allConstants[$const] = $tr;
            }
        }
        $this->constants = Ac_Util::m($allConstants, $this->constants);
        return $this->constants;
    }
    
    /**
     * Returns array with all language identifiers found if $this->translations table
     *
     * @return array
     */
    function getAllLanguages() {
        $res = array($this->defaultLanguage);
        foreach($this->translations as $t) $res = array_unique(array_merge($res, array_keys($t)));
        return $res;
    }
    
    /**
     * Convert string to plural form and, optionally, translate the plural form into the specified language
     *
     * @param string $string String to convert to plural form
     * @param string|false $langName Language to translate plural form to or FALSE if plural form doesn't have to be translated  
     */
    function getPlural($string, $langName = false) {
        if (is_array($string)) {
            $res = array();
            foreach ($string as $k => $v) $res[$k] = $this->getPlural($v, $langName);
            return $res;
        }
        $s = Ac_Cg_Inflector::humanize($string);
        if (isset($this->pluralForms[$s])) $plural = $this->pluralForms[$s];
        elseif ($this->useInflector) $plural = Ac_Cg_Inflector::singularToPlural($s);
        else $plural = $string;
        if ($langName !== false) $res = $this->translate($plural, $langName);
            else $res = $plural;
        return $res;
    }
    
    /**
     * Convert string to singular form and, optionally, translate the singular form into the specified language
     *
     * @param string $string String to convert to singular form
     * @param string|false $langName Language to translate singular form to or FALSE if singular form doesn't have to be translated  
     */
    function getSingular($string, $langName = false) {
        if (is_array($string)) {
            $res = array();
            foreach ($string as $k => $v) $res[$k] = $this->getSingular($v, $langName);
            return $res;
        }
        $s = Ac_Cg_Inflector::humanize($string);
        if (isset($this->singularForms[$s])) $singular = $this->singularForms[$s];
        elseif ($this->useInflector) $singular = Ac_Cg_Inflector::pluralToSingular($s);
        else $singular = $string;
        if ($langName !== false) $res = $this->translate($singular, $langName);
            else $res = $singular;
        return $res;
    }
    
    /**
     * Checks whether specified string is constant and, optionally, remembers it in $this->constants. 
     * To be recorgnized as constant, test string must be IN_DEFINIZE_FORM and, if $this->constantPrefix is set, start with it and an underscore.
     *
     * @param string $string String to check
     * @param bool $onlyKnownConstants Return TRUE only for strings that are already in $this->constants 
     * @param bool $addIfDontKnow Whether to add previously unknown constants to $this->constants
     * @return bool
     */
    function isConstant($string, $onlyKnownConstants = false, $addIfDontKnow = true) {
        if ($onlyKnownConstants) $res = isset($this->constants[$string]); 
        else { 
            if (strlen($string) > 0 && $string == Ac_Cg_Inflector::definize($string)) $res = true;
            if ($l = strlen($this->constantPrefix)) $res = $res && !strncmp($string, strtoupper($this->constantPrefix).'_', $l+1);
            if ($res && $addIfDontKnow) $this->constants[$res] = array();
        }
        return $res; 
    }

}

