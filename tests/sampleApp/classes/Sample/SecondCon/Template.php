<?php

class Sample_SecondCon_Template extends Ac_Template_Html {
    
    function showDefaultMethod() {
        echo "Sample.SecondCon.DefaultMethod ";
        echo $this->controller->getUrl();
    }
    
    function showOtherMethod() {
        echo 'Sample.SecondCon.OtherMethod.'.$this->controller->getContext()->getData('argument')." ";
        echo $this->controller->getUrl();
    }
    
}