<?php

/**
 * TODO 1: if we pass lines with ending line-breaks, first line will be repeated twice
 * and if multiline is disabled, parser won't work at all
 * TODO 2: without $multiline = true, class currently DOES NOT work
 */
class Ac_Util_Csv extends Ac_Prototyped {
    
    protected $enclose = '"';

    protected $delimiter = ";";
    
    protected $qEnclose = '"';
    
    protected $encloseLen = 1;
    
    protected $qDelimiter = ";";
    
    protected $delimiterLen = 1;
    
    protected $header = false;
    
    protected $result = false;
    
    protected $useHeader = false;
    
    protected $multiline = false;
    
    protected $prevLine = array();

    function setEnclose($enclose) {
        if ($enclose !== ($oldEnclose = $this->enclose)) {
            $this->enclose = $enclose;
            $this->qEnclose = preg_quote($this->enclose, "/");
            $this->encloseLen = strlen($this->enclose);
        }
    }

    function getEnclose() {
        return $this->enclose;
    }
    
    function setMultiline($multiline) {
        $this->multiline = (bool) $multiline;
    }

    function getMultiline() {
        return $this->multiline;
    }
    
    function setDelimiter($delimiter) {
        if ($delimiter !== ($oldDelimiter = $this->delimiter)) {
            $this->delimiter = $delimiter;
            $this->qDelimiter = preg_quote($this->delimiter, "/");
            $this->delimiterLen = strlen($this->delimiter);
        }
    }

    function getDelimiter() {
        return $this->delimiter;
    }    

    function setUseHeader($useHeader) {
        $this->useHeader = $useHeader;
    }

    function getUseHeader() {
        return $this->useHeader;
    }    
    
    /**
     * @param array|FALSE $header
     * @throws type
     */
    function setHeader($header) {
        if (!is_array($header) || $header === false) 
            throw Ac_E_InvalidCall::wrongType ('header', $header, array('boolean (FALSE)', 'array'));
        $this->header = $header;
    }
    
    function getHeader() {
        return $this->header;
    }
    
    function pushLine($line) {
        $data = $this->decodeLine($line, $this->prevLine);
        if ($data === false && $this->multiline) return;
        $skip = false;
        if ($this->header === false) {
            if ($this->useHeader) {
                $this->header = $data;
                $skip = true;
            } else {
                $this->header = array();
            }
            $this->result = array();
        }
        if (!$skip) {
            $r = array();
            foreach ($data as $k => $v) {
                if (isset($this->header[$k])) $k = $this->header[$k];
                $r[$k] = $v;
            }
            $this->result[] = $r;
        }
    }
    
    function reset() {
        $this->result = false;
        if ($this->useHeader) $this->header = false;
        $this->prevLine = array();
    }
    
    function getResult($clean = false) {
        $res = $this->result;
        if ($clean) $this->cleanResult();
        return $res;
    }
    
    function cleanResult() {
        $res = $this->result;
        if (is_array($this->result)) $this->result = array();
        return $res;
    }
    
    function decodeLine($line, array & $prevLine = array()) {
        return self::myDecodeLine($line, $this->delimiter, $this->enclose, $this->qDelimiter, $this->delimiterLen, $this->qEnclose, $this->encloseLen, $this->multiline, $prevLine);
    }
    
    static protected function myDecodeLine($line, $s=";", $l='"', $ss=";", $s_=1, $ll = '"', $l_ = 1, $ml = false, & $prevLine = array()) {
        if ($ml) {
            $line = str_replace("\r", "", $line);
            $line = str_replace("\n", "", $line);
        } else {
            $line = rtrim($line, "\n\r");
        }
        $tok = array();
        $tk = preg_split($rx = "/($ss)|($ll)/", $line, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        foreach ($tk as $tk_) {
            if (!strncmp($tk_, $ss, $s_)) {$tok[] = $ss; if(strlen($tk_ = substr($tk_, $s_))) $tok[] = $tk_; }
            elseif (!strncmp($tk_, $ll, $l_)) {$tok[] = $ll; if(strlen($tk_ = substr($tk_, $l_))) $tok[] = $tk_; }
            else $tok[] = $tk_;
        }
        $res = array(); $curr = ""; $isL = false; $n = count($tok); $i = 0;
        $d = 0;
        while ($i < $n) {
            if ($ml && $prevLine) {
                $isL = true;
                $curr = array_pop($prevLine)."\n";
                $res = $prevLine;
                $prevLine = array();
                $d = 1;
            }
            switch($t = $tok[$i]) {
                case $l: 
                    if ($isL && ($i < ($n - 1)) && ($tok[$i + 1] == $l)) { $curr .= $t; $i++; }
                    else {
                        $d += $isL? -1 : 1;
                        $isL = !$isL; 
                    }
                    break;
                case $s:
                    if ($isL) $curr .= $t;
                    else { $res[] = $curr; $curr = ""; }
                    break;
                default:
                    $curr .= $t;
            }
            $i++;
        }
        $res[] = $curr;
        if ($ml && $d) { // we have unmatched quote
            $prevLine = $res;
            $res = false;
        }
        return $res;
    }
    
    function readFile($file) {
        $this->reset();
        if (is_string($file)) {
            foreach (file($file) as $line) $this->pushLine ($line);
        } else { 
            while(false !== $line = fread($file)) $this->pushLine($line);
        }
        return $this->getResult(true);
    }
    
}
