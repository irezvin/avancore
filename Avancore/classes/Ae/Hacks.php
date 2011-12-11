<?php

define('AE_HACKS_EXPOSE', '_hck_expose');

/*
  
  	// this function sould be inserted into every hacked class that has private members and/or methods
  
    function & _hck_expose($action, $name, $arg) {
    	$res = null;
    	switch ($action) {
    		case 'get': $res = & $this->$name; break;
    		case 'set': $this->$name = $arg; break;
    		case 'call': $res = call_user_func_array(array(& $this, $name), $arg); break;
    		case 'setByRef': $this->$name = & $arg; break;
    		case 'isset': $res = isset($this->$name); break;
    		case 'unset': $res = unset($this->$name); break;
    	}
    	return $res;
    }
  
 */

class Ae_Hacks {

	var $_hck_enabled = true;
	
	var $_hck_stack = array();
	
	var $_hck_contextStack = array();
	
	var $_hck_isCallParent = false;
	
	/**
	 * Current $this object (if call isn't static)
	 * @var object
	 */
	var $_hck_context = null;
	
	var $_hck_contextCaps = array();
	
	var $_hck_class = false;
	
	/**
	 * @var array(hackMethodName => array('file' => fileName, 'class' => className, 'method' => methodName)
	 */
	var $_hck_methodTable = array();

	function _hck_registerHack($hackMethodName, $methodName, $className = false, $fileName = false) {
		$src = array('method' => $methodName);
		if ($className !== false) $src['class'] = $className;
		if ($fileName !== false) $src['file'] = $fileName;
		if (isset($this->_hck_methodTable[$hackMethodName])) trigger_error("Overwriting already resistered method '\$hackMethodName'", E_USER_NOTICE);
		$this->_hck_methodTable[$hackMethodName] = $src;
	}
	
	function _hck_findHack($file, $method, $class = false, $searchAutoHacks = true) {
		$resMatch = 0;
		$res = false;
		$m = array('file', 'class', 'method');
		foreach ($this->_hck_methodTable as $methodName => $src) {
			$match = 0; // No. of file path parts matched 
			$src = array_merge(array('file' => false, 'class' => false, 'method' => false), $src);
			if (!!strlen($src['class']) !== !!strlen($class)) continue; // Class methods definitely don't match function calls
			if (strlen($src['class']) && strcasecmp($src['class'], $class)) continue; // Classes must match if they are specified
			if ($src['method'] !== $method) continue; // Methods must also match
			if (strlen($src['file'])) {
				$k = $file;
				$b = $src['file'];
				while (strlen($k) && strlen($b) && !strcasecmp(basename($k), basename($b))) {
					$k = dirname($k);
					$b = dirname($b);
					$match++;
				}
				// We didn't fully match $src['file'] so we can't use it
				// Example: $src['file'] = includes/js/classes.php; $file = /src/foobar/classes.php
				// At the end $b will contain includes/js
				// If $src['file'] == classes.php, that would be ok.
				
				if (strlen($b)) continue;
				if ($b > $resMatch) {
					$res = $methodName;
					$resMatch = $b;
				}
			}
		}
		if (!strlen($res) && $searchAutoHacks) $res = $this->_hck_findAutoHack($method, $class);
		
		return $res;
	}
	
	/**
	 * @access private
	 */
	function _hck_findAutoHack($method, $class = false) {
		$hackName = '_my__';		
		if (strlen($class)) $hackName .= $class.'__';
		$hackName .= $method;
		
		if (method_exists($this, $hackName) && !isset($this->_hck_methodTable[$method])) 
			$res = $hackName;
		else
			$res = false;
			
		return $res;		 	
	}
	
	/**
	 * @static
	 * @access protected
	 * @param string $className Name of Ae_Hacks descendant
	 * @return object
	 * 
	 * This method is intended to be called by Ae_Hacks descendants
	 * <code>
	 * 		class Foo_Hacks extends Ae_Hacks {
	 * 			// ...
	 * 			function getInstance() {
	 * 				return Ae_Hacks::getInstance('Foo_Hacks');
	 * 			} 
	 * 			// ..
	 * 		}
	 * </code>
	 */
	function & _hck_getInstance($className) {
		$varName = '_aeHacks_'.$className;
		if (!isset($GLOBALS[$varName]) || !is_a($GLOBALS[$varName], $className)) $GLOBALS[$varName] = new $className();
		$res = & $GLOBALS[$varName];
		return $res;
	}

	function & _hck_getCurrentCall() {
		if (!($c = count($this->_hck_stack))) $res = null;
			else $res = & $this->_hck_stack[$c-1];
		return $res;
	}
	
	function _hck_isStatic($class, $method, & $context) {
		if (strlen($class) && $context) {
			$res = !is_a($context, $class);
		} else {
			$res = true;
		}
		return $res;
	}
	
	function _hck_getCallback($class, $method, & $context) {
		if ($this->_hck_isStatic($class, $method, $context)) {
			$res = strlen($class)? array($class, $method) : $method;
		} else {
			$res = array(& $context, $method);
		}
		return $res;
	}
	
	function _hck_pushContext(& $newContext) {
		$tmp = & $this->_hck_context;
		$this->_hck_contextStack[] = & $tmp;
		$this->_hck_context = & $newContext;
	}
	
	function _hck_popContext() {
		if ($c = count($this->_hck_contextStack)) {
			$this->_hck_context = null;
			$this->_hck_context = & $this->_hck_contextStack[$c-1];
			unset($this->_hck_contextStack[$c-1]);
		}
	}
	
	function _hck_invoke(& $context, $method, $arguments, &$result, $file, $class = false, $hackMethodName = false, $searchAutoHacks = true) {
		
		if (!$this->_hck_enabled) return false;
		
		if ($this->_hck_isCallParent) {
			$this->_hck_isCallParent = false;
			$res = false;
		} else {
			if (!strlen($hackMethodName)) $hackMethodName = $this->_hck_findHack($file, $method, $class, $searchAutoHacks);
			if (strlen($hackMethodName)) {
				$this->_hck_stack[count($this->_hck_stack)] = $call = array(
					'context' => & $context,
					'method' => $method,
					'arguments' => $arguments,
					'file' => $file,
					'class' => $class,
					'hackMethodName' => $hackMethodName,
					'caps' => $this->_hck_getContextCaps($context, $class),
				);
				$this->_hck_pushContext($context);
				$tmpCaps = $this->_hck_contextCaps;
				$tmpClass = $this->_hck_class;
				$this->_hck_contextCaps = $call['caps'];
				$this->_hck_class = $class;
				$result = call_user_func_array(array(& $this, $hackMethodName), $arguments);
				unset($this->_hck_stack[count($this->_hck_stack) - 1]);
				$this->_hck_popContext();
				$this->_hck_contextCaps = $tmpCaps;
				$this->_hck_class = $tmpClass;
				$res = true;
			} else {
				trigger_error('Hack method not found', E_USER_WARNING);
				$res = false;	
			}
		}
		return $res;
	}
	
	function _hck_auto(& $context, & $result) {
		// get context info of immediate caller
		$b = debug_backtrace();
		
//		foreach ($b as $i => $t) {
//			$file = isset($t['file'])? $t['file'] : '';
//			$line = isset($t['line'])? $t['line'] : '';
//			$func = isset($t['function'])? $t['function'] : '';
//			$class = isset($t['class'])? $t['class'] : '';
//			var_dump("$i) '$file' '$line' '$func' '$class'");
//		}
		
		if (!isset($b[1]['file'])) { 
			//if ($this->_hck_isCallParent) {
			if (isset($b[3]) && isset($b[3]['class']) && (!strcasecmp($b[3]['class'], 'Ae_Hacks'))) {
				$c = $this->_hck_getCurrentCall();
				$b[1]['file'] = $c['file'];
				
//				if (!$c['file']) {
//					
//					var_dump($c);
//					
//					foreach ($b as $i => $t) {
//						$file = isset($t['file'])? $t['file'] : '';
//						$line = isset($t['line'])? $t['line'] : '';
//						$func = isset($t['function'])? $t['function'] : '';
//						$class = isset($t['class'])? $t['class'] : '';
//						var_dump("$i) '$file' '$line' '$func' '$class'");
//					}
//					
//				}
				
			} else {
				trigger_error("Cannot properly determine execution context", E_USER_ERROR);
			}
		}
		return $this->_hck_invoke($context, $b[1]['function'], $b[1]['args'], $result, $b[1]['file'], isset($b[1]['class'])? $b[1]['class'] : false);
	}

	function _hck_getContextCaps(& $context, $class) {
		$allCaps = array(AE_HACKS_EXPOSE);
		if (is_object($context) && is_a($context, $class)) {
			foreach($allCaps as $c) if (is_callable($c = array($context, $c))) $allCaps[$c] = true;
		} elseif (strlen($class)) {
			foreach($allCaps as $c) if (is_callable($c = array($class, $c))) $allCaps[$c] = true;
		}
		return $allCaps;
	}
	
	function _hck_callParent($argumentsOverride = false) {
		$top = $this->_hck_getCurrentCall();
		if (!$top) trigger_error("Cannot callParent() outside of call context", E_USER_ERROR);
		$callback = $this->_hck_getCallback($top['class'], $top['method'], $top['context']);
		if (is_array($argumentsOverride)) $args = $argumentsOverride;
			else $args = $top['arguments'];
		$this->_hck_isCallParent = true;
		return call_user_func_array($callback, $args);
	}
	
	function & __get($name) {
		$res = null;
		if ($this->_hck_context) {
			if (isset($this->_hck_contextCaps[$v = AE_HACKS_EXPOSE])) {
				$res = & $this->_hck_context->$v('get', $name);
			} elseif (isset($this->_hck_context->$name)) {
				$res = & $this->_hck_context->$name;
			}
		}
		return $res;
	}
	
	function __set($name, $value) {
		if ($this->_hck_context) {
			if (isset($this->_hck_contextCaps[$v = AE_HACKS_EXPOSE])) {
				$this->_hck_context->$v('set', $name, $value);
			} else {
				$this->_hck_context->$name = $value;
			}
		}
	}
	
	function _hck_setByRef($name, & $value) {
		if ($this->_hck_context) {
			if (isset($this->_hck_contextCaps[$v = AE_HACKS_EXPOSE])) {
				$this->_hck_context->$v('setByRef', $name, $value);
			} else {
				$this->_hck_context->$name = & $value;
			}
		}
	}
	
	function __isset($name) {
		if ($this->_hck_context) {
			if (isset($this->_hck_contextCaps[$v = AE_HACKS_EXPOSE])) {
				$res = $this->_hck_context->$v('isset', $name);
			} else {
				$res = isset($this->_hck_context->$name);
			}
		}
	}
	
	function __unset($name) {
		if ($this->_hck_context) {
			if (isset($this->_hck_contextCaps[$v = AE_HACKS_EXPOSE])) {
				$res = $this->_hck_context->$v('unset', $name);
			} else {
				unset($this->_hck_context->$name);
			}
		}
	}
	
	function & __call($method, $args) {
		$res = null;
		$called = false;
		if ($this->_hck_context) {
			if (isset($this->_hck_contextCaps[$v = AE_HACKS_EXPOSE])) {
				$res = & $this->_hck_context->$v('call', $method, $args);
				$called = true;
			} elseif (is_callable($cl = array(& $this->_hck_context, $method))) {
				$res = & call_user_func_array($cl, $args);
				$called = true;
			}
		} elseif (strlen($this->_hck_class) && is_callable($cl = array($this->_hck_class, $method))) {
			$res = & call_user_func_array($cl, $args);
			$called = true;
		}
		if (!$called) trigger_error("Cannot call '$method'", E_USER_ERROR);
		return $res;
	}
	
}
