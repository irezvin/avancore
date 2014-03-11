<?php

/**
 * @property Ac_Result_Placeholder $headers HTTP headers
 */
abstract class Ac_Result_Http_Abstract extends Ac_Result implements Ac_I_Result_WithCharset {
    
    protected $charset = false;

    protected $contentType = false;
    
    protected $charsetUsage = Ac_I_Result_WithCharset::CHARSET_CONVERT;
    
    /**
     * @return Ac_Result_Placeholder_Headers
     */
    function getHeaders() {
        return $this->getPlaceholder('headers');
    }

    function setCharset($charset) {
        $this->charset = $charset;
    }

    function getCharset() {
        return $this->charset;
    }

    function setContentType($contentType) {
        if ($contentType !== $this->contentType) {
            $this->contentType = $contentType;
            if ($this->merged) $this->setDefaultHeaders();
        }
    }

    function getContentType() {
        return $this->contentType;
    }    
    
    protected function doGetDefaultPlaceholders() {
        return array(
            'headers' => array('class' => 'Ac_Result_Placeholder_Headers')
        );
    }

    function setCharsetUsage($charsetUsage) {
        if (!in_array($charsetUsage, $a = array(
            Ac_I_Result_WithCharset::CHARSET_CONVERT, 
            Ac_I_Result_WithCharset::CHARSET_PROPAGATE, 
            Ac_I_Result_WithCharset::CHARSET_IGNORE))) 
        {
            throw Ac_E_InvalidCall::outOfConst('charsetUsage', $charsetUsage, array(
                'CHARSET_CONVERT', 
                'CHARSET_PROPAGATE', 
                'CHARSET_IGNORE'
            ), 'Ac_I_Result_WithCharset');
        }
        $this->charsetUsage = $charsetUsage;
    }

    function getCharsetUsage() {
        return $this->charsetUsage;
    }    
    
    function setMerged($merged) {
        if ($merged && !$this->merged) {
            $this->setDefaultHeaders();
        }
        parent::setMerged($merged);
    }
    
    protected function setDefaultHeaders() {
        if ($this->contentType !== false) {
            $cts = $this->contentType;
            if (strlen($this->charset)) $cts .= '; charset='.$this->charset;
            $this->headers[] = 'Content-Type: '.$cts;
        }
    }    
    
    
}