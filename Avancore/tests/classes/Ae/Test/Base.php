<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ae_Test_Base extends UnitTestCase {

	var $_aeDb = false;
	
	/**
	 * @return Ae_Sql_Db_Ae
	 */
	function & getAeDb() {
		$d = Ae_Dispatcher::getInstance();
		if ($this->_aeDb === false) $this->_aeDb = new Ae_Sql_Db_Ae($d->database);
		return $this->_aeDb;
	}
	
	function normalizeStatement($sql, $replacePrefix = false) {
		$res = preg_replace('/\s+/', ' ', trim($sql));
		if ($replacePrefix) {
			$disp = & Ae_Dispatcher::getInstance();
			$res = $disp->database->replacePrefix($res);			
		}
		return $res;
	}
	
	function getDbName() {
		return Ae_Dispatcher::getInstance()->config->getValue('db');
	}
	
	function getTablePrefix() {
		return Ae_Dispatcher::getInstance()->config->getValue('prefix');
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