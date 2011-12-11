<?php

Ae_Dispatcher::loadClass('Ae_User');

class Ae_Native_User extends Ae_User {
    
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