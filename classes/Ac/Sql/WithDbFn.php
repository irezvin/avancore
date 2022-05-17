<?php

trait Ac_Sql_WithDbFn {
    
    /**
     * Returns instance of database associated with component or is' application.
     * Accepts arbitrary arguments, passes them to $db->args() before returning the instance.
     * 
     * @return Ac_Sql_Db
     */
    function db() {
        if (isset($this->db) && $this->db instanceof Ac_Sql_Db) {
            $db = $this->db;
        } else if (isset($this->app) && $this->app instanceof Ac_Application) {
            $db = $this->app->getDb();
        } else if (Ac_Accessor::methodExists($this, 'getDb')) {
            $db = $this->getDb();
        }
        if (!($db && $db instanceof Ac_Sql_Db)) return null;
        $args = func_get_args();
        $n = count($args);
        if (!$n) return $db;
        if ($n == 1) return $db->args($args[0]);
        if ($n == 2) return $db->args($args[0], $args[1]);
        if ($n == 3) return $db->args($args[0], $args[1], $args[2]);
        return call_user_func_array([$db, 'args'], $args);
    }
    
}