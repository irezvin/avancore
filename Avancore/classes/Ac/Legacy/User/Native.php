<?php

class Ac_Legacy_User_Native extends Ac_Legacy_User {
    
	function getId() {
		return 0;  
	}
  
	function isAdmin() {
		return false;
	}
	
	function isSpecial() {
		return false;
	}
    
}

?>