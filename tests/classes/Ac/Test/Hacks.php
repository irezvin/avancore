<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

/**
 * Test plan
 * 
 * - hack method detection using member names 
 * - hack method detection using registered member
 * 
 * - same with automated context detection
 * - correct decision based on the path
 * 
 * - function override
 * - method override with cut-and-paste
 * - static method override
 * 
 * - override of method that calls protected methods and vars (get, set, call, isset, unset) using expose function
 * - override of all-public class without expose function
 * - override static method that calls protected static method using $this-> instead of self::
 *  
 * - call original function
 * - call original method
 * - call original protected method
 * - call original protected static method
 * 
 */

if (!defined ('HACKS_SAMPLE')) define('HACKS_SAMPLE', dirname(__FILE__).DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'HacksSample.php');

if ( defined('HACKS_SAMPLE')) { if (!class_exists('HacksSample', false)) require(HACKS_SAMPLE); }

class Ac_Test_Hacks extends UnitTestCase {

	function testSimpleHacks() {
		$instance1 = HacksSample::_hck_getInstance();
		$this->assertIsA($instance1, 'HacksSample');
		$instance2 = HacksSample::_hck_getInstance();
		$this->assertReference($instance1, $instance2);
		
		
		// Let's go...
		$h = $instance1;
		
		
		// 23 and 12 are just random numbers that don't make any sense
		
		$h->_hck_enabled = false;
		$this->assertEqual(hackedFunc(23, 12), "The Output Is 23, 12");
		
		$h->_hck_enabled = true;
		$this->assertEqual(hackedFunc(23, 12), "Hacked! Args are: 23, 12, and original output was 'The Output Is 23, 12'");
		
		$apc = new AllPublicClass(23, 12);
		
		$h->_hck_enabled = false;
		$this->assertEqual($apc->decorate('1981', 'b'), '<b>1981</b>');
		
		$h->_hck_enabled = true;
		$this->assertEqual($apc->decorate('1981', 'b'), '<!--<b>1981</b>-->');
		
		
		$h->_hck_enabled = false;
		$this->assertEqual($apc->getConcat('/', 'i'), '<i>23/12</i>');
		
		$h->_hck_enabled = true;
		$this->assertEqual($apc->getConcat('/', 'i'), '<!--<i>(23|/|12)</i>-->');
		
		
	}
	
}

// -------------------- asset classes and functions ------------------

function hackedFunc($arg1, $arg2) {
	/*<aeh>*/ 
		if ( defined('HACKS_SAMPLE')) { if (!class_exists('HacksSample', false)) require(HACKS_SAMPLE); $_hck_ = HacksSample::_hck_getInstance(); 
		$_hck_ctx = isset($this)? $this : null; if($_hck_->_hck_auto($_hck_ctx, $_hck_result)) return $_hck_result; } 
	/*</aeh>*/ 
		
	return "The Output Is {$arg1}, {$arg2}";
}

class AllPublicClass {
	
	var $var1 = false;
	
	var $var2 = false;
	
	function __construct($var1, $var2) {
		$this->var1 = $var1;
		$this->var2 = $var2;
	}
	
	function decorate($string, $tagName = false) {
		/*<aeh>*/ 
			if ( defined('HACKS_SAMPLE')) { if (!class_exists('HacksSample', false)) require(HACKS_SAMPLE); $_hck_ = HacksSample::_hck_getInstance(); 
			$_hck_ctx = isset($this)? $this : null; if($_hck_->_hck_auto($_hck_ctx, $_hck_result)) return $_hck_result; } 
		/*</aeh>*/
		
		if (strlen($tagName)) $res = "<{$tagName}>{$string}</{$tagName}>";
			else $res = $string;
		return $res;
	}
	
	function getConcat($glue, $tagName = false) {
		/*<aeh>*/ 
			if ( defined('HACKS_SAMPLE')) { if (!class_exists('HacksSample', false)) require(HACKS_SAMPLE); $_hck_ = HacksSample::_hck_getInstance(); 
			$_hck_ctx = isset($this)? $this : null; if($_hck_->_hck_auto($_hck_ctx, $_hck_result)) return $_hck_result; } 
		/*</aeh>*/
		
		$res = "{$this->var1}{$glue}{$this->var2}";
		$res = $this->decorate($res, $tagName);
		return $res;
	}
	
	function staticConcat($foo, $bar) {
		/*<aeh>*/ 
			if ( defined('HACKS_SAMPLE')) { if (!class_exists('HacksSample', false)) require(HACKS_SAMPLE); $_hck_ = HacksSample::_hck_getInstance(); 
			$_hck_ctx = isset($this)? $this : null; if($_hck_->_hck_auto($_hck_ctx, $_hck_result)) return $_hck_result; } 
		/*</aeh>*/
			
		return "{$foo}{$bar}";
	}
	
}

class ClassWithProtectedMembers {

	
	
}