<?php

class Ac_Admin_Processing_MapperMethod extends Ac_Admin_Processing {
    
    var $method = false;
    
    var $provideRecordKeys = false;
    
    function executeProcess() {
        if ($this->provideRecordKeys) {
            $args = array($this->_getIdentifiersFromRequest());
            if (!$args[0] && $this->defaultToAllRecords) {
                $coll = $this->_doGetRecordsCollection();
                if (!$coll instanceof Ac_Model_Collection_SqlMapper) {
                    throw new Exception("provideRecordKeys() doesn't work currently for collections other than Ac_Model_Collection_SqlMapper");
                }
                    
                // new version of framework
                /** @TODO: Ac_Model_Collection_Abstract::getRecordKeys() */
                /** @TODO: Won't work with composite PKs */
                $k = $coll->getMapper()->pk;
                $s = $coll->createSqlSelect();
                $s->columns = [$k];
                $s->distinct = true;
                $keys = $s->getDb()->fetchColumn($s);
                $args = array($keys);
            }
        } else {
            $args = array();
        }
        $method = $this->method;
        if (!$method) $method = $this->id;
        if (!is_array($method)) $method = array($this->_getMapper(), $method);
        call_user_func_array($method, $args);
    }
    
}

