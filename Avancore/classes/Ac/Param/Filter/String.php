<?php

class Ac_Param_Filter_String extends Ac_Param_Filter implements Ac_I_Decorator {
    
    var $stripTags = true;
    
    /**
     * @var bool|string TRUE - default trim , string - use charlist from $trim
     */
    var $trim = true;
    
    var $maxLength = false;
    
    var $fromEncoding = 'utf-8';
    
    var $toEncoding = 'utf-8';
    
    var $addCSlashes = false;
    
    const caseLower = 'caseLower';
    
    const caseUpper = 'caseUpper';
    
    const casePreserve = 'casePreserve';
    
    var $changeCase = self::casePreserve;
    
    /**
     * @var array searchString => replacement
     */
    var $replace = false;
    
    /**
     * @var bool if TRUE, assumes keys in $searchAndReplace are regular expressions
     */
    var $usePregReplace = false;
    
    /**
     * @var bool|string FALSE, TRUE or charlist; TRUE = "\n\r\t\'\""
     */
    var $stripCSlashes = false;
    
    /**
     * @var bool|string FALSE, TRUE or charlist/preg class
     */
    var $removeDoubleSpaces = false;
    
    /**
     * Order of filter execution is very important:
     * 
     * - stripcslashes
     * - addcslashes
     * - stripTags
     * - removeDoubleSpaces
     * - replace
     * - trim
     * 
     * (non-PHPdoc)
     * @see Ac_I_Param_Filter::filter()
     */
    function filter($value, Ac_Param $param = null) {
        
        if ($this->fromEncoding != $this->toEncoding) $value = iconv($this->fromEncoding, $this->toEncoding);
        
        if ($this->stripCSlashes !== false) {
            $value = stripcslashes($value);
        }
        
        if ($this->addCSlashes !== false) {
            $cs = $this->addCSlashes == true? "\n\r\t\'\"" : $this->addCSlashes;
            $value = addcslashes($value, $cs);
        }
        
        if ($this->stripTags) $value = strip_tags($value);
        
        if ($this->removeDoubleSpaces) {
            $ds = $this->removeDoubleSpaces === true? '\s' : (string) $this->removeDoubleSpaces;
            $value = preg_replace("/([{$ds}])[{$ds}]+/", "\\1", $value);
        }
        
        if ($this->changeCase !== self::casePreserve) {
            $fn = false;
            if ($this->changeCase == self::caseLower) {
                $fn = 'strtolower';
            } elseif ($this->changeCase == self::caseUpper) {
                $fn = 'strtoupper';
            }
            if (strlen($fn)) {
                if ($this->toEncoding) {
                    $fn = 'mb_'.$fn;
                    $value = $fn ($value, $this->toEncoding);
                } else {
                    $value = $fn($value);
                }
            }
        }
        
        if (is_array($this->replace)) {
            if (!$this->usePregReplace) {
                $value = strtr($value, $this->replace);
            }
            else foreach ($this->replace as $k => $v) {
                $value = preg_replace($k, $v, $value);
            }
        }
        
        if ($this->trim) {
            if ($this->trim === true) $value = trim($value);
                else $value = trim($value, $this->trim);
        }
        
        if (is_numeric($this->maxLength)) {
            if ($this->toEncoding) $value = mb_substr($value, 0, $this->maxLength, $this->toEncoding);
                else $value = substr($value, 0, $this->maxLength);
        }
        
        return $value;
    }
    
}
