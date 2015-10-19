<?php

class Ac_Lang_String {
	
	protected $i = '';
	
	function __construct($id, $default = false) {
		$this->i = $id;
		if (is_array($default)) {
			if (isset($default['default'])) $this->d = $default['default'];
			if (isset($default['prefix'])) $this->p = $default['prefix'];
			if (isset($default['suffix'])) $this->s = $default['suffix'];
			if (isset($default['transform'])) $this->t = $default['transform'];
			if (isset($default['sprintf'])) $this->f = $default['sprintf'];
			if (isset($default['strtr'])) $this->r = $default['strtr'];
		} else {
			if ($default !== false) $this->d = $default;
		}
	}
	
	function __toString() {
		$res = Ac_Lang_Resource::getInstance()->getString($this->i, isset($this->d)? $this->d : false);
		if (isset($this->f) && ($this->f !== false)) {
		    $a = is_array($this->f)? $this->f : array($this->f);
		    $res = call_user_func_array('sprintf', $args = array_merge(array($res), $a));
		}
		
		if (isset($this->r) && is_array($this->r) && $this->r) $res = strtr($res, $this->r);
		
		if (isset($this->t) && ($this->t !== false)) {
			switch ($this->t) {
				case 'ucfirst': $sfx = mb_strtoupper(mb_substr($res, 0, 1, 'utf-8'), 'utf-8'); $res = $sfx.mb_substr($res, 1, null, 'utf-8'); break;
				case 'lcfirst': $sfx = mb_strtolower(mb_substr($res, 0, 1, 'utf-8'), 'utf-8'); $res = $sfx.mb_substr($res, 1, null, 'utf-8'); break;
			}
		}
		if (isset($this->p) && ($this->p !== false)) $res = $this->p.$res;
		if (isset($this->s) && ($this->s !== false)) $res = $res.$this->s;
		return $res;
	}
	
	function toString() {
	    return $this->__toString();
	}
	
	function toJson() {
		return "'". addslashes ((string) $this). "'";
	}
	
}

