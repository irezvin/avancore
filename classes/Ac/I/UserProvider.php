<?php

interface Ac_I_UserProvider {
    
    /**
     * @param array $credentials
     * @param array $errors
     * @return ID of logged-in user
     */
    function authenticate(array $credentials, array & $errors = []);
    
    function getUser($id);

}