<?php

class Ac_Registry_Updater_Key implements Ac_I_RegistryUpdater {

    protected $key;
    
    function __construct($key) {
        $this->key = $key;
    }
    
    function getKey() {
        return $this->key;
    }
    
    function update(Ac_I_Registry $registry, $data) {
        $registry->setRegistry($this->key, $data);
    }
    
}