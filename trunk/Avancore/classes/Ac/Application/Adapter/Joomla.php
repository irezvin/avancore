<?php

class Ac_Application_Adapter_Joomla extends Ac_Application_Adapter {
    
    protected $liveSite = false;
    
    protected function guessOutput() {
        if (!isset($this->config[$k = 'output'])) {
            $this->config[$k] = array('class' => 'Ac_Legacy_Output_Joomla15');
        }
    }
    
    protected function calcMissingConfig() {
        $jc = new JConfig();
        $this->setDefault('checkDirs', true);
        $this->detectDir('varCachePath', JPATH_CACHE);
        $this->detectDir('varTmpPath', $jc->tmp_path);
        $this->detectDir('varLogsPath', $jc->log_path);
        return parent::calcMissingConfig();
    }
    
    protected function guessDatabase() {
        if (!isset($this->config[$k = 'database'])) {
            $this->config[$k] = array('class' => 'Ac_Sql_Db_Legacy');
        }
    }
    
    function getLegacyDatabasePrototype() {
        if (!isset($this->config[$k = 'legacyDatabasePrototype'])) {
            $this->config[$k] = array('class' => 'Ac_Legacy_Database_Joomla25');
        }
        return $this->config[$k];
    }
    
    protected function doGetDefaultServices() {
        return Ac_Util::m(parent::doGetDefaultServices(), array(
            'managerConfigService' => 'Ac_Admin_ManagerConfigService_J25',
        ));
    }
    
    function getLiveSite() {
        if ($this->liveSite === false) {
            $uri = JUri::getInstance();
            $uri = $uri->base();
            $uri = preg_replace('#(/administrator/)?(/index\.php)?$#', '', $uri);
            $this->liveSite = $uri;
        }
        return $this->liveSite;
    }

    protected function getDefaultCachePrototype() {
        $res = array('cacheDir' => $this->getVarCachePath());
        if (!$this->getConfigValue('ignoreJoomlaCacheSettings')) {
            // TODO: create specialized cache that works through Joomla
            $res['enabled'] = (bool) JFactory::getConfig()->get('caching');
            $res['lifetime'] = intval(JFactory::getConfig()->get('cachetime'))*60;
        }
        return $res;
    }
    
}