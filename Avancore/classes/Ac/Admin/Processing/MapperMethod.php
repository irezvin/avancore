<?php

class Ac_Admin_Processing_MapperMethod extends Ac_Admin_Processing {
    
    var $method = false;
    
    var $provideRecordKeys = false;
    
    function executeProcess() {
        if ($this->provideRecordKeys) {
            $args = array($this->_getIdentifiersFromRequest());
            if (!$args[0] && $this->defaultToAllRecords) {
                $coll = $this->_doGetRecordsCollection();
                $kk = $coll->getPkName();
                $db = $this->getApplication()->getDb();
                if (count($kk) == 1) {
                    $k = $kk[0];
                    $keys = $db->fetchColumn("SELECT DISTINCT {$k} ".$coll->getStatementTail(true));
                } else {
                    $k = $db->n($kk, true);
                    $keys = array();
                    foreach ($db->fetchColumn("SELECT DISTINCT {$k} ".$coll->getStatementTail(true)) as $row) {
                        $keys[] = array_keys($row);
                    }
                }
                $args = array($keys);
            }
        } else {
            $args = array();
        }
        if (is_array($this->method)) $m = $this->method;
            else $m = array($this->_getMapper(), $this->method);
        call_user_func_array($m, $args);
    }
    
}

