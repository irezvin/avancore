<?php

interface Ac_I_Mail_Sender {
    
    /**
     * @return bool
     */
    function sendMail(Ac_I_Mail_Message $mail, array & $errors = array());
    
}