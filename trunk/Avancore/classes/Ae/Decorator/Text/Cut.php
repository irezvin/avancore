<?php

class Ae_Decorator_Text_Cut extends Ae_Decorator {
    
    var $cutLength = 100;
    var $cutEllipsis = '...';
    var $preserveWords = true;
    var $encoding = 'utf-8';
    var $fullTextToHtmlTitle = false;
    
    function apply($value) {
        $wasCut = false;
        $orig = $value;
        if ($this->encoding) {
            if ($this->cutLength && (($len = mb_strlen($value, $this->encoding)) > $this->cutLength)) {
                if ($this->preserveWords) {
                    if ($this->encoding != 'utf-8') $value = iconv($this->encoding, 'utf-8', $value);
                    $value = $this->utfCut($value, $this->cutLength);
                    if ($this->encoding != 'utf-8') $value = iconv('utf-8', $this->encoding, $value);
                } else {
                    $value = mb_substr($value, 0, $this->cutLength, $this->encoding);
                }
                $wasCut = true;
            }
        } else {
            if ($this->cutLength && (($len = strlen($value, $this->encoding)) > $this->cutLength)) {
                if ($this->preserveWords) {
                    $str = wordwrap($res, ($cutLength - $len), $foo = md5(microtime().rand()), 1);
                    $xp = explode($foo, $str);
                    $value = $xp[0];
                } else {
                    $value = substr($value, 0, $this->cutLength);
                }
                $wasCut = true;
            }
        }
        if ($wasCut) {
            if (strlen($this->cutEllipsis)) $value .= $this->cutEllipsis;
            if ($this->fullTextToHtmlTitle) $value = Ae_Util::mkElement('span', $value, array('title' => $orig));
        }
        
        return $value;
    }
    
    protected function utfCut($str, $maxLen) {
         $words = preg_split('#([^\p{L}]+)#u', $str, 0, PREG_SPLIT_DELIM_CAPTURE);
         $res = $words[0];
         $c = count($words);
         if (mb_strlen($res, 'utf-8') > $maxLen) {
             $res = mb_substr($res, 0, $maxLen, 'utf-8');
         } else {
             $maxLen -= mb_strlen($res, 'utf-8'); 
             for ($i = 1; $i < $c; $i++) {
                 $l = mb_strlen($words[$i], 'utf-8');
                 if ($l < $maxLen) {
                     $res .= $words[$i];
                     $maxLen -= $l;
                 } else break;
            }
         }
         return $res;
    }
    
}