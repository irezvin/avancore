<?php

interface Ac_I_Mail_Message {

    function getMailRecipients();
    
    function getMailFrom();
    
    function getMailSubject();
    
    function getMailHtmlBody();
    
    function getMailTextBody();
    
}