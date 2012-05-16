<?php

class Ac_Lang_ResourceProvider_Dir extends Ac_Autoparams implements Ac_I_Lang_ResourceProvider {

	/**
	 * Name of a directory that contains language files or directories.
	 * It can have such contents:
	 * 
	 * ru.php
	 * ru/01first.php
	 * ru/02second.php
	 * en.php
	 * en/01first.php
	 * en/02second.php
	 * 
	 * Each file should be valid PHP file that assigns associative array to $langData variable.
	 * <code>
	 * < ?php
	 * $langData = array(
	 * 		'stringId1' => 'value1',
	 * 		'stringId2' => 'value2',
	 * 		'stringId3' => 'value3'...
	 * );
	 * </code> 
	 * 
	 * Files are loaded in this order (and data of subsequent files overwrites data of former files):
	 * - file in the dirName/ dir
	 * - files in the dirName/lang/ dir, ordered alphabetically
	 */
    protected $dirName = false;

    protected $fallbackLang = 'en';

    protected function setDirName($dirName) {
        $this->dirName = $dirName;
    }

    function getDirName() {
        return $this->dirName;
    }

    protected function setFallbackLang($fallbackLang) {
        $this->fallbackLang = $fallbackLang;
    }

    function getFallbackLang() {
        return $this->fallbackLang;
    }	
	
	/**
	 * Returns has used for by resource content caching (it should change if content has changed)
	 * 
	 * @param string $langId 'ru', 'en', 'de' and so on...
	 * @return string 
	 */
	function getLangHash($langId) {
		$s = '';
		$f = $this->listIncludedFiles($langId);
		if (!count($f) && $this->fallbackLang && ($langId !== $fallbackLang)) $f = $this->listIncludedFiles($fallbackLang);
		foreach ($f as $file) {
			$s .= ' '.filemtime($file);
		}
		return md5($s);
	}
	
	/**
	 * Returns array with language strings.
	 * 
	 * @param string $langId 'ru', 'en', 'de' and so on...
	 * @return array
	 */
	function getLangData($langId) {
	    $f = $this->listIncludedFiles($langId);
	    if (!count($f) && $this->fallbackLang && ($langId !== $this->fallbackLang)) $f = $this->listIncludedFiles($this->fallbackLang);
		$res = array();
		foreach ($f as $file) $res = array_merge($res, $this->getLangDataFromFile($file));
		return $res;
	}
	
	function getLangDataFromFile($filename) {
		$lang = array();
		include($filename);
		if (!is_array($lang)) $lang = array();
		return $lang;
	}
	
	function listIncludedFiles($langId) {
		$ds = DIRECTORY_SEPARATOR;
		$res = array();
		if (is_file($fName = $this->dirName.$ds.$langId.'.php')) {
			$res[] = $fName;
		}
		if (is_dir($dName = $this->dirName.$ds.$langId)) {
			$files = glob($dName.$ds."*.php", $dName);
			sort($files);
			foreach ($files as $file) $res[] = $dName.$ds.$file;
		}
		return $res;
	}
	
	/**
	 * Registers directories with languages in 
	 * @param string $dirNamesString One or more directories (separated with PATH_SEPARATOR). Defaults to _DEPLOY_LANG_DIRS.
	 */
	static function autoRegister($dirNamesString = false) {
		if ($dirNamesString === false && defined('_DEPLOY_LANG_DIRS')) $dirNamesString = _DEPLOY_LANG_DIRS;
        $lrp = array();
		$lr = Ac_Lang_Resource::getInstance();
		if ($dirNamesString !== false) {
			$dirNames = explode(PATH_SEPARATOR, $dirNamesString);
			$lrp = $lr->getProviders();
			foreach ($dirNames as $dirName) $lrp[] = new Ac_Lang_ResourceProvider_Dir(array('dirName' => $dirName));
		}
		if ($lrp) {
		    $lr->setResourceProviders($lrp);
		}
	}
	
	
}