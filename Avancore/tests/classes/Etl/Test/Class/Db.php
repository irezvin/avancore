<?php

class Test_Db extends Ac_Sql_Db_Ae {
    
    var $queries = array();
    
    var $executeQueries = true;
    
    function query($query) {
        $this->queries[] = $query;
        if ($this->executeQueries) {
            $res = parent::query($query);
        } else {
            $res = true;
        }
        return $res;
    }
    
    function getQueries($clear = false) {
        $res = $this->queries;
        if ($clear) $this->queries = array();
        return $res;
    }
    
}