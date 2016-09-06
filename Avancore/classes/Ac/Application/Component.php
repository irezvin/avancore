<?php

class Ac_Application_Component extends Ac_Prototyped {
    
    /**
     * @var Ac_Sql_Db
     */
    protected $db = null;
    
    /**
     * @var Ac_Application
     */
    protected $application = false;
    
    function setApplication(Ac_Application $application) {
        $this->application = $application;
        $this->db = $this->application->getDb();
    }

    /**
     * @return Ac_Application
     */
    function getApplication() {
        return $this->application;
    }
    
    function setDb(Ac_Sql_Db $db) {
        $this->db = $db;
    }

    /**
     * @return Ac_Sql_Db
     */
    function getDb() {
        if ($this->db === null) {
            if ($this->application) $this->db = $this->application->getDb();
        }
        return $this->db;
    }
    
}