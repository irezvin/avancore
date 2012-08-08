<?php

/**
 * @TODO Where will we get default charset??? Inject by the factory? Should it be computed in the getter?
 */
abstract class Ac_Content_WithCharset extends Ac_Content {
    
    protected $charset = false;
    
    protected $mimeType = 'text/plain';

    function setCharset($charset) {
        $this->charset = $charset;
    }

    function getCharset() {
        return $this->charset;
    }

}