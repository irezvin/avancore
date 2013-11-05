<?php

class Ac_Result_Stage_Load extends Ac_Result_Stage {
    
    function processResultDuringWakeup() {
        $this->traverse();
    }
    
}