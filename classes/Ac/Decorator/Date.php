<?php

class Ac_Decorator_Date extends Ac_Decorator {
    
    var $format = null;
    
    var $useGmt = false;
    
    var $addHours = 0;
    
    var $skipEmptyValue = true;
    
    /**
     * @var array minSeconds => format
     */
    var $relativeFormats = array();
    
    var $currTimestamp = false;

    protected function detectFormat($date) {
        if (!$this->relativeFormats) return $this->format;
        $date = $this->useGmt? strtotime($date." GMT +0:00") : strtotime($date);
        //if ($this->addHours) $date += $this->addHours*3600;
        $t0 = $this->currTimestamp !== false? $this->currTimestamp : time();
        $d = $t0 - $date;
        ksort($this->relativeFormats, SORT_NUMERIC);
        $res = $this->format;
        foreach ($this->relativeFormats as $seconds => $f) {
            $samesign = ($d < 0) == ($seconds < 0);
            if ($samesign && (abs($d) < abs($seconds))) {
                $res = $f;
                break;
            }
        }
        return $res;
    }
    
    function apply($value) {
        if (strlen($value) || !$this->skipEmptyValue) {
            $origValue = $value;
            if ($this->addHours) {
                $v = Ac_Util::date($value, null, $this->useGmt, $wasZero);
                if (!$wasZero) {
                    $value = $v;
                    if ($value !== false) {
                        $value += $this->addHours*3600;
                        $value = Ac_Util::date($value, $this->detectFormat($origValue), $this->useGmt);
                    }
                }
            } else {
                $value = Ac_Util::date($value, $this->detectFormat($value), $this->useGmt);
            }
        }
        return $value;
    }
   
}