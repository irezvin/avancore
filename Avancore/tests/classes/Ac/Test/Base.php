<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ac_Test_Base extends UnitTestCase {

    // Ugly but allows us to run test without legacy adapter
    static $config = array();
    
    protected $aeDb = false;
    
    protected $legacyDb = false;
    
    /**
     * @var Sample
     */
    protected $sampleApp = false;
    
    protected $bootSampleApp = false;

//    function setUp() {
//        parent::setUp();
//        restore_error_handler();
//    }
    
    function __construct($label = false) {
        parent::__construct($label);
        if ($this->bootSampleApp) $this->getSampleApp();
    }
    
	/**
	 * @return Ac_Sql_Db_Ae
	 */
	function getAeDb() {
		if ($this->aeDb === false) $this->aeDb = Ac_Prototyped::factory (self::$config['dbPrototype'], 'Ac_Sql_Db');
		return $this->aeDb;
	}
    
    /**
     * @return Sample
     */
    function getSampleApp() {
        if ($this->sampleApp === false) {
            $appClassFile = dirname(dirname(dirname(dirname(__FILE__)))).'/sampleApp/gen/classes/Sample/DomainBase.php';
            require_once($appClassFile);
            $appClassFile = dirname(dirname(dirname(dirname(__FILE__)))).'/sampleApp/classes/Sample.php';
            require_once($appClassFile);
            $this->sampleApp = Sample::getInstance();
        }
        return $this->sampleApp;
    }
    
    /**
     * @return Ac_Legacy_Database 
     */
    function getLegacyDb() {
        if ($this->legacyDb === false) $this->legacyDb = new Ac_Legacy_Database_Native(self::$config);
        return $this->legacyDb;
    }
	
	function normalizeStatement($sql, $replacePrefix = true) {
		$res = preg_replace('/\s+/', ' ', trim($sql));
		if ($replacePrefix) {
			$res = $this->getAeDb()->replacePrefix($res);			
		}
		return $res;
	}
	
	function getDbName() {
		return self::$config['db'];
	}
	
	function getTablePrefix() {
		return self::$config['prefix'];
	}
	
    function _replaceIndent($match) {
        return "\n".str_repeat(" ", strlen($match[0])/2*4);
    }
    
    function export($foo, $return = false, $indent = 0) {
        if (is_array($foo)) $res = $this->exportArray($foo, $indent, true, true, true);
        elseif ($foo === 0) $res = '0';
        else $res = var_export($foo, true);
        
        if ($return) return $res; 
            else echo $res;
    }
    
    /**
     * Returns code for initializing given PHP array
     */
    function exportArray($foo, $indent = 0, $withNumericKeys = false, $oneLine = false, $return = false) {
        $vx = var_export($foo, 1);
        $vx = preg_replace("/=> \n([ ]+)array \\(/", "=> array (\\1", $vx);
        $vx = preg_replace_callback("/\n[ ]+/", array(& $this, '_replaceIndent'), $vx);
        if ($indent) {
            $ind = str_repeat(" ", $indent);
            $vx = preg_replace("/\n/", "\n".$ind, $vx);
        }
        if (!$withNumericKeys) $vx = preg_replace ("/(\n[ ]+) \\d+ =>/", "\\1", $vx);
        if ($oneLine) {
            $vx = preg_replace("/\n[ ]*/", " ", $vx);
        }
        if (!$return) echo $vx; 
            else return $vx;
    }
    
	function stripRightArrayToLeft($left, $right, $pathsToProtectRx = false, $basePath = '') {
		$r = $right;
		foreach (array_keys($r) as $key) {
			$path = $basePath.'/'.$key;
			//if ($pathsToProtectRx && preg_match($pathsToProtectRx, $path)) var_dump($path);
			if (!array_key_exists($key, $left) && !($pathsToProtectRx && preg_match($pathsToProtectRx, $path))) {
				unset($r[$key]);
			} elseif (is_array($r[$key]) && is_array($left[$key])) {
                $r[$key] = $this->stripRightArrayToLeft($left[$key], $r[$key], $pathsToProtectRx, $path);
            } elseif (is_array($left[$key]) && is_object($right[$key])) {
                $r[$key] = Ac_Accessor::getObjectProperty($right[$key], array_keys($left[$key]));
                $r[$key]['__class'] = get_class($right[$key]);
            }
            
		}
		return $r;
	}
	
	function assertArraysMatch($left, $right, $message = '%s', $exactItems = false) {
		if ($exactItems !== true) {
            $right = $this->stripRightArrayToLeft($left, $right, $exactItems); 
        }
		return $this->assertEqual($left, $right, $message);
	}
    
    function normalizeHtml($html, $stripBreaks = true) {
        $html = str_replace("'", '"', trim($html));
        $html = preg_replace("/\s*\n\r?\s*/", "\n", $html);
        $html = preg_replace("/ +/", " ", $html);
        $html = preg_replace("/\n+/", $stripBreaks? "" : "\n", $html);
        return $html;
    }
    
    // ugly hack to allow us to run only specific method
    function getTests() {
        $res = parent::getTests();
        if (isset($_REQUEST['method']) && strlen($_REQUEST['method'])) {
            $res = array_intersect(explode(",", $_REQUEST['method']), $res);
        }
        return $res;
    }
    
    function resetAi($tableName, $ai = false) {
        // MySQL only!!!
        $db = $this->getAeDb();
        $cols = $db->fetchArray("SHOW COLUMNS FROM ".$db->n($tableName), 'Field');
        $res = false;
        foreach ($cols as $col => $data) {
            if ($data['Extra'] == 'auto_increment') {
                $max = $db->fetchValue("SELECT MAX(".$db->n($col).") FROM ".$db->n($tableName));
                $res = intval($max) + 1;
                if (is_numeric($ai)) {
                    if ($ai < $res) throw new Exception("Cannot set AI ($ai) to value < max ({$max})");
                    $res = $ai;
                }
                $db->query('ALTER TABLE '.$db->n($tableName)." AUTO_INCREMENT=".($res));
                break;
            }
        }
        return $res;
    }
    
    function deleteProducts($where) {
        if (is_array($where)) {
            foreach ($where as $w) $this->deleteProducts($w);
        }
        $db = $this->getAeDb();
        if (is_numeric($where)) {
            $where = 'p.id '.$db->q($where);
        }
        $db->query("
            DELETE p.*, m.*, pub.* 
            FROM #__shop_products p 
                LEFT JOIN #__shop_meta m ON m.id = p.metaId
                LEFT JOIN #__publish pub ON p.pubId = pub.id
                WHERE $where
        ");
    }
	
}