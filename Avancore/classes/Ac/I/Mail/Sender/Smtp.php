<?php

interface Ac_I_Mail_Sender_Smtp {

    const SMTP_SECURE_NONE = '';
    const SMTP_SECURE_SSL = 'ssl';
    const SMTP_SECURE_TLS = 'tls';
    
    function setSmtpHost($smtpHost);
    function setSmtpPort($smtpPort);
    function setSmtpUser($smtpUser);
    function setSmtpAuth($smtpAuth);
    function setSmtpSecure($smtpSecure);
    function setSmtpPassword($smtpPassword);
    function setSmtpAuthType($smtpAuthType);

}
