<?php

class Ac_Result_Writer_WithCharset extends Ac_Result_Writer_Plain {

    protected $charset = false;

    function setCharset($charset) {
        $this->charset = $charset;
    }

    function getDefaultCharset() {
        if ($this->stage && ($app = $this->stage->getApplication())) $res = $app->getAdapter()->getCharset();
        else $res = false;
        return $res;
    }
    
    function getCharset($returnDefault = false) {
        if ($this->charset === false) {
            $res = false;
            if ($this->target && $this->target instanceof Ac_I_Result_WithCharset) {
                $res = $this->target->getCharset();
                if (($res === false) && $returnDefault) {
                    $res = $this->getDefaultCharset();
                }
            }
        } else { 
            $res = $this->charset;
        }
        return $res;
    }
    
    protected function requiresTarget() {
        return false;
    }
    
    protected function implWrite(Ac_Result $r, Ac_Result $t = null, Ac_Result_Stage $s = null) {
        $echo = true;
        if ($r instanceof Ac_I_Result_WithCharset && ($rChar = $r->getCharset()) !== ($tChar = $this->getCharset())) {
            $us = $r->getCharsetUsage();
            if ($us == Ac_I_Result_WithCharset::CHARSET_IGNORE) $echo = true;
            elseif ($us == Ac_I_Result_WithCharset::CHARSET_PROPAGATE && $tChar === false) {
                if ($t instanceof Ac_I_Result_WithCharset) $t->setCharset($rChar);
            } else {
                if ($rChar === false) $rChar = $this->getDefaultCharset();
                if ($rChar !== false) {
                    $this->writeRecode($r, $rChar, $tChar, $t, $s);
                    $echo = false;
                }
            }
        }
        if ($echo) $this->implWriteNoCharset($r, $t, $s);
    }
    
    protected $in = false;
    protected $out = false;
    
    protected function iconv($buf, $phase) {
        if (strlen($buf)) return iconv($this->in, $this->out, $buf);
    }

    protected function writeRecode(Ac_Result $r, $rCharset, $tCharset, Ac_Result $t = null, Ac_Result_Stage $s = null) {
        // TODO: figure out how to implement conversion on final stage during string objects' merge and lazy evaluation... is it possible?
        //echo "<!--###BEGIN '{$rCharset}' to '{$tCharset}'-->";
        $all = iconv_get_encoding();
        $this->in = $rCharset;
        $this->out = $tCharset;
        ob_start(array($this, 'iconv'));
        $this->implWriteNoCharset($r, $t, $s);
        ob_end_flush();
        //var_dump(iconv_get_encoding());
        //  echo "<-- '{$rCharset}' to '{$tCharset}' END###-->";
    }
    
    protected function implWriteNoCharset(Ac_Result $r, Ac_Result $t = null, Ac_Result_Stage $s = null) {
        $r->echoContent();
    }
    
}