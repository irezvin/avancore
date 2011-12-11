<?php

class Ae_Util_Csv extends Ae_Autoparams {
    
    protected $enclose = '"';

    protected $delimiter = ";";
    
    protected $qEnclose = '"';
    
    protected $encloseLen = 1;
    
    protected $qDelimiter = ";";
    
    protected $delimiterLen = 1;
    
    protected $header = false;
    
    protected $result = false;
    
    protected $useHeader = false;

    function setEnclose($enclose) {
        if ($enclose !== ($oldEnclose = $this->enclose)) {
            $this->enclose = $enclose;
            $this->qEnclose = preg_quote($this->encolse, "/");
            $this->encloseLen = strlen($this->enclose);
        }
    }

    function getEnclose() {
        return $this->enclose;
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
    
    function pushLine($line) {
        $data = $this->decodeLine($line);
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
        $this->header = $this->result = false;
    }
    
    function getResult($clean = false) {
        $res = $this->result;
        if ($clean) $this->cleanResult();
        return $res;
    }
    
    function cleanResult() {
        $res = $this->result;
        $this->result = array();
        return $res;
    }
    
    function decodeLine($line) {
        return self::myDecodeLine($line, $this->delimiter, $this->enclose, $this->qDelimiter, $this->delimiterLen, $this->qEnclose, $this->encloseLen);
    }
    
    static protected function myDecodeLine($line, $s=";", $l='"', $ss=";", $s_=1, $ll = '"', $l_ = 1) {
        $line =  str_replace("\r", "", $line);
        $line = str_replace("\n", "", $line);
        $tok = array();
        $tk = preg_split($rx = "/($ss)|($ll)/", $line, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        foreach ($tk as $tk_) {
            if (!strncmp($tk_, $ss, $s_)) {$tok[] = $ss; if(strlen($tk_ = substr($tk_, $s_))) $tok[] = $tk_; }
            elseif (!strncmp($tk_, $ll, $l_)) {$tok[] = $ll; if(strlen($tk_ = substr($tk_, $l_))) $tok[] = $tk_; }
            else $tok[] = $tk_;
        }
        $res = array(); $curr = ""; $isL = false; $n = count($tok); $i = 0;
        while ($i < $n) {
            switch($t = $tok[$i]) {
                case $l: 
                    if ($isL && ($i < ($n - 1)) && ($tok[$i + 1] == $l)) { $curr .= $t; $i++; }
                    else $isL = !$isL; 
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
        return $res;
    }

    
}