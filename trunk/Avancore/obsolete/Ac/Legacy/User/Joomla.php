<?php

class Ac_Legacy_User_Joomla extends Ac_Legacy_User {

	var $josUser = null;
	
	function Ac_Legacy_User_Joomla(& $josUser) {
		$this->josUser = $josUser;
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