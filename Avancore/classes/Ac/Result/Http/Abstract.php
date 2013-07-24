<?php

/**
 * @property Ac_Result_Placeholder $headers HTTP headers
 */
abstract class Ac_Result_Http_Abstract extends Ac_Result {
    
    /**
     * @return Ac_Result_Placeholder
     */
    function getHeaders() {
        return $this->getPlaceholder('headers');
    }
    
    protected function doGetDefaultPlaceholders() {
        return array(
            'headers' => array('class' => 'Ac_Result_Placeholder')
        );
    }
    
}