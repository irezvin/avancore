<?php

class Ac_Response_Updater_Debug implements Ac_I_RegistryUpdater {
    
    function update(Ac_I_Registry $registry, $data) {
        $registry->mergeRegistry(array('debug' => $data));
    }
    
}