<?php

class Ae_Legacy_User_Native extends Ae_Legacy_User {
    
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