<?php

class Ae_Application_Adapter_Joomla extends Ae_Application_Adapter {
    
    protected function guessOutput() {
        if (!isset($this->config[$k = 'output'])) {
            $this->config[$k] = array('class' => 'Ae_Joomla_15_Output');
        }
    }
    
}