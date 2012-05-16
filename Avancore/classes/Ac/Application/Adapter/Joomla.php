<?php

class Ac_Application_Adapter_Joomla extends Ac_Application_Adapter {
    
    protected function guessOutput() {
        if (!isset($this->config[$k = 'output'])) {
            $this->config[$k] = array('class' => 'Ac_Legacy_Output_Joomla15');
        }
    }
    
}