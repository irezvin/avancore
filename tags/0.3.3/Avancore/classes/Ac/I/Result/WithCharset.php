<?php

interface Ac_I_Result_WithCharset {
    
    const CHARSET_CONVERT = 0;
    const CHARSET_PROPAGATE = 1;
    const CHARSET_IGNORE = 2;
    
    function getCharset();
    
    function setCharset($charset);
    
    function getCharsetUsage();
    
}