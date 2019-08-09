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
    
    public function onCreateSqlSelect(Ac_Sql_Select $select) {
        var_dump($select.'');
        if ($this->die) die();
    }
    
}
