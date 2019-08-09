<?php

class Ac_Etl_Logger_Collector implements Ac_Etl_I_Logger {
    
    var $items = array();
    
    function acceptItem(Ac_Etl_Log_Item $item) {
        $item->acceptedBy($this);
        $this->items[] = $item;
    }
    
}