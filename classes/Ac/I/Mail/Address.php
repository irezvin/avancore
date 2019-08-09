<?php

interface Ac_I_Mail_Address {

    /**
     * @return mail title (i.e. John Doe) or an empty string
     */
    function getMailTitle();
    
    /**
     * @return mail address (i.e. jdoe@example.com)
     */
    function getMailAddress();
    
}