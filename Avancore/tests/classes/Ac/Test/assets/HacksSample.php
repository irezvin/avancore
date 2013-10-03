<?php

Ac_Dispatcher::loadClass('Ac_Hacks');

class HacksSample extends Ac_Hacks {
	
	/**
	 * @return HacksSample
	 */
	function & _hck_getInstance() {
		$res = Ac_Hacks::_hck_getInstance('hacksSample');
		return $res;
	}
	
	function _my__hackedFunc($arg1, $arg2) {
		return "Hacked! Args are: {$arg1}, {$arg2}, and original output was '".$this->_hck_callParent($arg1, $arg2)."'";
	}
	
	function _my__AllPublicClass__decorate($string, $tagName = false) {
		return "<!--".$this->_hck_callParent($string, $tagName)."-->";
	}
	
	function _my__AllPublicClass__getConcat($glue, $tagName = false) {
		
		$res = "({$this->var1}|{$glue}|{$this->var2})";
		$res = $this->decorate($res, $tagName);
		return $res;
		
	}
	
}

