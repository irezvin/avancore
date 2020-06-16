<?php

/**
 * This feature works with every record to populate manager with basic info 
 */
class Ac_Admin_Feature_DebugSql extends Ac_Admin_Feature {

    var $die = false;
    
    var $sqlSelectSettings = array();
    
    public function getSqlSelectSettings() {
        return $this->sqlSelectSettings;
    }
    
    function onAfterCreateCollection(Ac_Model_Collection_Abstract $collection) {
        if ($collection instanceof Ac_Model_Collection_Mapper) {
            $select = $collection->createSqlSelect();
            var_dump(''.($select));
            if ($this->die) die();
        }
    }
    
    
}
