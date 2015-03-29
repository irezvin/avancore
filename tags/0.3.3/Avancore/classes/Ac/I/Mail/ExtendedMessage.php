<?php

interface Ac_I_Mail_ExtendedMessage extends Ac_I_Mail_Message {
    
    function getMailCcRecipients();
    
    function getMailBccRecipients();
    
    function getMailReplyTo();
    
    function getMailHeaders();
    
    function getMailCharset();
    
    function getMailAttachments();
    
}