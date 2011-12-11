<?php

Ae_Dispatcher::loadClass('Ae_User');

class Ae_Joomla_User extends Ae_User {

	var $josUser = null;
	
	function Ae_Joomla_User(& $josUser) {
		$this->josUser = & $josUser;
	}
	
  	function getId() {
		if (is_object($this->josUser)) return $this->josUser->id;
			else return 0;  
  	}
  
	function isManager() {
		if (is_object($this->josUser)) $res = $this->josUser->id && in_array($this->josUser->usertype, array('Administrator', 'Super Administrator', 'Manager'));
			else $res = false;
		return $res;
	}
  	
	function isSpecial() {
		if (is_object($this->josUser)) $res = $this->josUser->id && in_array($this->josUser->usertype, array('Administrator', 'Super Administrator', 'Manager', 'Editor', 'Publisher', 'Author'));
			else $res = false;
		return $res;
	}
  
}

?>