<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ac_Test_Base extends UnitTestCase {

    // Ugly but allows us to run test without legacy adapter
    static $config = array();
    
	protected $aeDb = false;
    
    protected $legacyDb = false;
	
	/**
	 * @return Ac_Sql_Db_Ae
	 */
	function & getAeDb() {
		if ($this->aeDb === false) $this->aeDb = new Ac_Sql_Db_Ae($this->getLegacyDb());
		return $this->aeDb;
	}
    
    /**
     * @return Ac_Legacy_Database 
     */
    function getLegacyDb() {
        if ($this->legacyDb === false) $this->legacyDb = new Ac_Legacy_Database_Native(self::$config);
        return $this->legacyDb;
    }
	
	function normalizeStatement($sql, $replacePrefix = false) {
		$res = preg_replace('/\s+/', ' ', trim($sql));
		if ($replacePrefix) {
			$res = $this->getLegacyDb()->replacePrefix($res);			
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
			} elseif (is_array($r[$key]) && is_array($left[$key])) $r[$key] = $this->stripRightArrayToLeft($left[$key], $r[$key], $pathsToProtectRx, $path);
		}
		return $r;
	}
	
	function assertArraysMatch($left, $right, $message = '%s', $exactItems = false) {
		if ($exactItems !== true) $right = $this->stripRightArrayToLeft($left, $right, $exactItems); 
		return $this->assertEqual($left, $right, $message);
	}
	
	
}