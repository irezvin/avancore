<?php

/**
 * @property Ac_Application $application
 * @property Ac_Sql_Db $db
 * @method Ac_Application getApplication()
 * @method void setApplication(Ac_Application $application)
 */
class Ac_Application_Component extends Ac_Prototyped implements Ac_I_ApplicationComponent {
    
    use Ac_Compat_Overloader;
    
    protected static $_compat_application = 'app';
    protected static $_compat_setApplication = 'setApp';
    protected static $_compat_getApplication = 'getApp';
    
    protected static $_compat_db = true;
    
    /**
     * @var Ac_Sql_Db
     */
    protected $dbInstance = null;
    
    /**
     * @var Ac_Application
     */
    protected $app = false;
    
    function setApp(Ac_Application $app) {
        $this->app = $app;
        $this->db = $this->app->getDb();
    }

    /**
     * @return Ac_Application
     */
    function getApp() {
        return $this->app;
    }
    
    function setDb(Ac_Sql_Db $db) {
        $this->dbInstance = $db;
    }

    /**
     * @return Ac_Sql_Db
     */
    function getDb() {
        if ($this->dbInstance === null) {
            if ($this->app) $this->dbInstance = $this->app->getDb();
        }
        return $this->dbInstance;
    }
    
}