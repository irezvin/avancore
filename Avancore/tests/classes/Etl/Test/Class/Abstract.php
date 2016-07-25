<?php

class Etl_Test_Class_Abstract extends Ac_Test_Base {

    protected $bootSampleApp = true;
    
    /**
     * @return Ac_Etl_Import
     */
    function createEmptyImporter() {
        $im = new Ac_Etl_Import(array(
            'application' => $this->getSampleApp(),
            'db' => $this->db,
            'targetDbPrefix' => 'im_',
            'importerDbPrefix' => 'im_',
        ));
        $im->setImportId(1);
        $im->setLogger(new Ac_Etl_Logger_Collector);
        return $im;
    }
    
    /**
     * @var Ac_Sql_Db
     */
    var $db = false;
    
    function setUp() {

        $this->sampleApp = $this->getSampleApp();
        $this->db = clone $this->sampleApp->getDb();
        $this->db->setDbPrefix('im_');
        $this->clearData();
        
    }

    function clearData() {
        
        $prefix = $this->db->getDbPrefix();

        $testTables = preg_grep($rx = '/^'.preg_quote($prefix).'test_/', $c = $this->db->fetchColumn('SHOW TABLES'));
        
        foreach (array_merge($a = array(
            '#__test_items',
            '#__test_categories',
        ), $testTables) as $table) {
            if (!preg_match('/source_of_copy$/', $table))
                $this->db->query('DELETE FROM '.$table);
        }
        
        foreach (array('#__test_items', '#__test_types', '#__test_categories', '#__test_aipk') as $tableName) {
            $this->db->query("ALTER TABLE {$tableName} AUTO_INCREMENT=1");
        }
        
    }
    
    function writeStats(Ac_Etl_Import $importer) {
        
        $stats = new Ac_Etl_Log_Stats;
       
        $stats->setItems($importer->getLogger()->items);
       
        $html = new Ac_Etl_Log_Stats_Html_Stats($stats);
       
        $writer = new Ac_Etl_Log_Stats_Html_Writer;
        
        $writer->assetPlaceholders = Ac_Avancore::getInstance()->getAssetPlaceholders(true);
       
        $writer->writeWidget($html);
        
    }
    
    
}
