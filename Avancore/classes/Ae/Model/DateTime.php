<?php

class Ae_Model_DateTime {
    
    var $arrayFields = false;

    var $validFields = array ('day', 'month', 'strMonth', 'year', 'hour', 'minute', 'second');
    
    var $mandatoryFields = array ('day', 'month', 'year');

    var $detectZeroDate = true;

    var $debug = false;
    
    var $months = 'Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec';
    
    static function getDateTranslations() {
        static $englishMonths = false;
        static $englishMonthShorts = false;
        static $localeMonths = false;
        static $localeMonthShorts = false;
        static $res = false;
        
        if (!class_exists('Ae_Lang_String', false)) {
            return array();
        }
        
        if ($englishMonths === false) {
            
            $englishMonthShorts = explode('|', 'Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec');
            $englishMonths = array("January", "February", "March", "April", "May", 
            		"June","July", "August", "September", "October", "November", "December");
            $localeMonths = explode('|', new Ae_Lang_String('locale_months_long', implode('|', $englishMonths)));
            $localeMonthShorts = explode('|', new Ae_Lang_String('locale_months_short', implode('|', $englishMonthShorts)));
            
            $res = array();
            
            for ($i = 0; $i < 12; $i++) {
                $res[$englishMonths[$i]] = $localeMonths[$i];
            }
            
            for ($i = 0; $i < 12; $i++) {
                $res[$englishMonthShorts[$i]] = $localeMonthShorts[$i];
            }
            
        }
        
        return $res;
        
    }

    // 'r' = regex part, 'l' = letter (will be %l or %?l in template), 't' => convert_to, 'f' => convert_from, 'v' => validate function, 'i' => input ignore (if true)
    var $chunks = array (
        'day2' => array ('r' => '[0-9]{2}', 'l' => 'd2', 't' => 'mday', 'f' => 'mday', 'fn' => 'c2', 'tn' => 'iv'),
        'month2' => array ('r' => '([0-9]{1,2})|([\p{L}]+)', 'l' => 'm2', 't' => 'month', 'f' => 'month', 'fn' => 'c2', 'tn' => 'iv'),
        'year2' => array ('r' => '[0-9]{2}', 'l' => 'y2', 't' => 'year', 'f' => 'year', 'fn' => 'c2', 'tn' => 'iv'),
        'year4' => array ('r' => '[0-9]{4}', 'l' => 'y4', 't' => 'year', 'f' => 'year', 'fn' => 'c4', 'tn' => 'iv'),
        'year34' => array ('r' => '[0-9]{3,4}', 'l' => 'y34', 't' => 'year', 'f' => 'year', 'fn' => 'c4', 'tn' => 'iv'),
        'hour2' => array ('r' => '[0-9]{2}', 'l' => 'h2', 't' => 'hours', 'f' => 'hours', 'fn' => 'c2', 'tn' => 'iv'),
        'minute2' => array ('r' => '[0-9]{2}', 'l' => 'i2', 't' => 'minutes', 'f' => 'minutes', 'fn' => 'c2', 'tn' => 'iv'),
        'second2' => array ('r' => '[0-9]{2}', 'l' => 's2', 't' => 'seconds', 'f' => 'seconds', 'fn' => 'c2', 'tn' => 'iv'),

        'mday' => array ('r' => '[0-9]{1,2}', 'l' => 'd'),
        'month' => array ('r' => '([0-9]{1,2})|([\p{L}]+)', 'l' => 'm'),
        'year' => array ('r' => '[0-9]{1,4}', 'l' => 'y'),
        'hours' => array ('r' => '[0-9]{1,2}', 'l' => 'h'),
        'minutes' => array ('r' => '[0-9]{1,2}', 'l' => 'i'),
        'seconds' => array ('r' => '[0-9]{1,2}', 'l' => 's'),
        
        
        'dateSeparator' => array ('r' => '[\-.]+', 'l' => '-', 'i' => true, 'o' => '.'),
        'timeSeparator' => array ('r' => '[\:]+', 'l' => '|', 'i' => true, 'o' => ':'),
        'whitespace' => array ('r' => '\\s+', 'l' => '_', 'i' => true, 'o' => ' '),
    );
    
    var $inputMasks = array (
        '_mssql' => "\s*(?P<mssql>\w{3}\s+[0-9]{1,2}\s+[0-9]{1,4}\s+[0-9]{2}:[0-9]{2}:[0-9]{2}:[0-9]{3}\s*((AM)|(PM)))\s*",
    
        'hms' => '%h%|%i(%|%s)?',
        'ymd' => '%y4%-%m%-%d',
    	'mdy' => '%m\/%d\/%y',
        'dmy' => '%d%-%m%-%y',
        'my' => '%m%-%y34',
        'dm' => '%d%-%m',
        
        '_mysqlts' => '%y2%m2%d2%h2%i2%s2',
        'iso' => '%y4%m2%d2T#hms',
        'germanFcukingDate' => array('%d\.\s*%m\s+%y4'), 
    	'anydate' => array ('#my', '#dm', '#dmy', '#ymd', '#mdy', '#germanFcukingDate'),
        'datetime' => array ('#anydate%_#hms', '#hms%_#anydate'),
    );
    
    var $subRegs = false;               // subexpressions cache
    var $rxs = false;                   // regular expressions cache
    var $userMaskCache = array();       // user masks cache
    
    function c2 ($v) {return str_pad($v, 2, '0', STR_PAD_LEFT);}
    function c4 ($v) {return str_pad($v, 4, '0', STR_PAD_LEFT);}
    function iv ($v) {return intval($v);}
        
    function Ae_Model_DateTime() {
        $this->debug = isset($_REQUEST['_debugDate']);
        $this->rxs = array();
        $this->subRegs = array();
        foreach ($this->chunks as $chunkName => $chunk) {
            if (isset($chunk['i']) && $chunk['i']) $subReg = '('.$chunk['r'].')';
                else $subReg = "(?P<$chunkName>{$chunk['r']})";
            $this->subRegs[$chunk['l']] = $subReg;
        }

        foreach ($this->inputMasks as $maskName => $maskData) {
            $this->rxs[$maskName] = $this->rxMask($maskData, $this->rxs);
        }
    }
    
    function getInstance() {
        if (!isset($GLOBALS['_dateTimeParserSingleton']) || !is_a($GLOBALS['_dateTimeParserSingleton'], 'Ae_Model_DateTime')) {
            $GLOBALS['_dateTimeParserSingleton'] = new Ae_Model_DateTime();
        }
        return $GLOBALS['_dateTimeParserSingleton'];
    }
    
    
    function tsFromArray($array, $set = true, $useGmt = true) {
        if (isset($array['mssql'])) {
            $array['mssql'] = preg_replace('/:[0-9]{3}/', '', $array['mssql']);
            $timestamp = strtotime($array['mssql']);
            if ($useGmt && class_exists('DateTimeZone') && function_exists('date_default_timezone_get')) {
                $z = new DateTimeZone(date_default_timezone_get());
                $gmt = new DateTimeZone('GMT');
                $gmtime = new DateTime("now", $gmt);
                $timestamp = $timestamp - $z->getOffset($gmtime);
            }
        } else {
            
            $isZeroDate = $this->detectZeroDate? $this->isZeroDate($array) : false;
            
            if (!$isZeroDate) {
                static $defaults = array ('mday' => 1, 'month' => 1, 'year' => 1970, 'hours' => 0, 'minutes' => 0, 'seconds' => 0);
                $v = array_merge($defaults, $array);
                $f = $useGmt? 'gmmktime' : 'mktime';
                $timestamp = $f($v['hours'] + 12, $v['minutes'], $v['seconds'], $v['month'], $v['mday'], $v['year']) - 12*3600;                
            } else {
                $timestamp = $this->getZeroDateValue();
            }
            
        }
        return $timestamp;
    }
    
    function getZeroDateValue() {
        return 0;
    }
    
    function tsToArray($timestamp = null) {
        $dateInfo = getdate($timestamp);
        return $dateInfo; 
    }
    
    function mask2mask($mask, $exMaskName, $exMask) {
        if (is_array($mask)) {
            $res = array();
            foreach ($mask as $maskEl) $res[] = $this->mask2mask($maskEl, $exMaskName, $exMask);
            return $res;
        }
        if (is_array($exMask)) {
            $res = array();
            foreach ($exMask as $exMaskEl) $res[] = $this->mask2mask($mask, $exMaskName, $exMaskEl);
            return $res;
        }
        return str_replace('#'.$exMaskName, $exMask, $mask);
    }
    
    function rxMask($maskData, $existingMasks) {
        if (is_array($maskData)) {
            $res = array();
            foreach ($maskData as $md) $res[] = $this->rxMask($md, $existingMasks);
            return $res;
        } else {
            foreach ($this->subRegs as $smLetter => $smValue) $maskData = str_replace ('%'.$smLetter, $smValue, $maskData);
            foreach ($existingMasks as $maskName => $maskValue) 
                $maskData = $this->mask2mask($maskData, $maskName, $maskValue);
        }
        return $maskData;
    }
    
    function _intMatch($inputString, $rx) {
        if (is_array($rx)) foreach (array_reverse($rx) as $rk=>$subRx) {
            if ($res = $this->_intMatch($inputString, $subRx)) {
                return $res;
            }
        }
        $res = array();
        if (preg_match ("/^{$rx}\$/u", $inputString, $res)) {
            //lplp::p($res, 1);
            return $res;
        }
        return false;
    }
    
    function match($inputString, $userMask = false) {
        if ($userMask) {
            $umd5 = md5(serialize($userMask));
            if (!isset($this->userMaskCache[$umd5])) {
                $this->userMaskCache[$umd5] = $urx = $this->rxMask ($userMask, $this->rxs);
            } else $urx = $this->userMaskCache[$umd5];
        } else $urx = $this->rxs;
        $matches = $this->_intMatch ($inputString, $urx);
        if ($matches) {
            $res = array();
            foreach (array_keys($matches) as $mk) if (!is_int($mk) && strlen($matches[$mk])) $res[$mk] = $matches[$mk];
            // Add support for character-based months
            if (isset($res['month']) && !is_numeric($res['month'])) {
                $months = $this->months;
                if (class_exists('Ae_Lang_String', false)) {
                    $months = (string) new Ae_Lang_String('locale_months_short', $months);
                } else {
                }
                $months = explode("|", $months);
                $found = false;
                foreach ($months as $i => $mon) {
                    if (preg_match("/^".preg_quote($mon, "/")."/ui", $res['month'])) {
                        $found = $i + 1;
                        break;
                    }
                }
                if ($found !== false) {
                    $res['month'] = $found;
                } else {
                    return false;
                }
            }
            return $res;
        } else return false;
    }
    
    function process($matches) {
        $res = array();
        foreach (array_keys($matches) as $k) {
            $v = $matches[$k];
            while (isset($this->chunks[$k]) && isset($this->chunks[$k]['t'])) {
                //echo ("\nReplacing $k to {$this->chunks[$k]['t']}\n");
                if (isset ($this->chunks[$k]['tn'])) 
                {
                    $convFunc = $this->chunks[$k]['tn'];
                    $v = $this->$convFunc ($v); 
                } else {
                    $convFunc = 'c_'.$k.'_'.$this->chunks[$k]['t'];
                    $v = $this->$convFunc ($v);
                }
                $k = $this->chunks[$k]['t'];
            }
            $res[$k] = $v;
        }
        return $res;
    }
    
    
    function arrayFromString($string, $userMask = false) {
    	if (is_a($string, 'DateTime')) $res = getdate(@$string->format('U')); else {
	        $parser = & Ae_Model_DateTime::getInstance();
	        if (!($res = $parser->match($string))) return false;
	        $res = $parser->process($res, $userMask);
    	}
        return $res;
    }
    
    function fromString($string, $userMask = false, $useGmt = true) {
        $parser = & Ae_Model_DateTime::getInstance();
        if (!($res = $parser->match($string))) return false;
        $res = $parser->process($res, $userMask);
        return $parser->tsFromArray($res, true, $useGmt);
    }
    
    function toString($format, $timestamp, $useGmt = true) {
        return $useGmt? gmdate($format, $timestamp) : date($format, $timestamp);
    }
    
    function isZeroDate($array) {
            $isZeroDate = false;
            if (isset($array['year'])) {
                $isZeroDate = true;
                foreach ($array as $a) {
                    if (!is_numeric($a) || intval($a)) {
                        $isZeroDate = false;
                        break;
                    }
                }
            }
            return $isZeroDate;
    }
    

    static function date ($src, $format = null, $useGmt = false) {
    	if (is_a($src, 'DateTime')) {
    		$srcTs = (int) $src->format('U');
    	} else {
	        if (!is_int($src)) { 
	            if (class_exists('Ae_Dispatcher')) Ae_Dispatcher::loadClass('Ae_Model_DateTime');
	                else {
	                    if (!class_exists('Ae_Model_DateTime')) {
	                        require(dirname(__FILE__).'/Model/DateTime.php');
	                    }
	                }
	            $dtp = & Ae_Model_DateTime::getInstance();
	            $srcTs = $dtp->fromString($src, false, $useGmt); 
	        } else $srcTs = $src;
    	}
        
        if (is_null($srcTs) || $srcTs === false) return $srcTs;
                
        // before php 5.1, negative timestamps were not allowed
        if ((intval(AE_PHP_VERSION_MAJOR) >= 5) && (intval(AE_PHP_VERSION_MINOR) >= 1)) {
            $isWrongDate = $srcTs === false;     
        } else {
            $isWrongDate = $srcTs == -1;
        }
        
        if ($isWrongDate) return false;
        
        
        if (is_null($format)) return $srcTs; 
        else {
            if (is_null($srcTs) || $srcTs === false) return false;
            
            $res = $useGmt? gmdate($format, $srcTs) : date($format, $srcTs);
            if (strpos($format, 'M') !== false || strpos($format, 'F') !== false) {
                $res = strtr($res, self::getDateTranslations());
            }
            return $res; 
        }
    }
    
    
}

?>
