<?php

class Sample_FirstCon_Template extends Ac_Template_Html {
    
    function showDefaultMethod() {
        echo "Sample.FirstCon.DefaultMethod ";
        echo $this->controller->getUrl();
    }
    
    function showOtherMethod() {
        echo 'Sample.FirstCon.OtherMethod.'.$this->controller->getContext()->getData('argument')." ";
        echo $this->controller->getUrl();
    }
    
}