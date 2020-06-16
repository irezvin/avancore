<?php

class Ac_Mail_PHPMailer_Smtp extends Ac_Mail_Sender_PHPMailer implements Ac_I_Mail_Sender_Smtp {
    
    protected $smtpHost = 'localhost';

    protected $smtpPort = 25;

    protected $smtpUser = '';

    protected $smtpAuth = false;

    protected $smtpSecure = Ac_I_Mail_Sender_Smtp::SMTP_SECURE_NONE;

    protected $smtpPassword = '';
    
    protected $smtpAuthType = '';
    
    /**
     * Properties => values of SMTP class' instance (of PHPMailer's class.smtp.php)
     * Most useful ones are 
     * 'do_debug' => 1..4
     * 'Debugoutput' => 'echo', 'html' or 'error_log'
     * @var array
     */
    protected $stmpInstanceVars = []; 

    function setSmtpHost($smtpHost) {
        $this->smtpHost = $smtpHost;
    }

    function getSmtpHost() {
        return $this->smtpHost;
    }

    function setSmtpPort($smtpPort) {
        $this->smtpPort = $smtpPort;
    }

    function getSmtpPort() {
        return $this->smtpPort;
    }

    function setSmtpUser($smtpUser) {
        $this->smtpUser = $smtpUser;
    }

    function getSmtpUser() {
        return $this->smtpUser;
    }

    function setSmtpAuth($smtpAuth) {
        $this->smtpAuth = $smtpAuth;
    }

    function getSmtpAuth() {
        return $this->smtpAuth;
    }

    function setSmtpSecure($smtpSecure) {
        $c = Ac_Util::getClassConstants('Ac_I_Mail_Sender_Smtp', 'SMTP_SECURE');
        if (!in_array($smtpSecure, $c)) 
            throw Ac_E_InvalidCall::outOfConst ('smtpSecure', $smtpSecure, 
                'SMTP_SECURE', 'Ac_I_Mail_Sender_Smtp');
        $this->smtpSecure = $smtpSecure;
    }

    function getSmtpSecure() {
        return $this->smtpSecure;
    }

    function setSmtpPassword($smtpPassword) {
        $this->smtpPassword = $smtpPassword;
    }

    function getSmtpPassword() {
        return $this->smtpPassword;
    }    
    
    function setSmtpInstanceVars(array $smtpInstanceVars) {
        $this->smtpInstanceVars = $smtpInstanceVars;
    }

    /**
     * @return array
     */
    function getSmtpInstanceVars() {
        return $this->smtpInstanceVars;
    }    
    
    protected function doConfigureSender(PHPMailer $mailer) {
        $mailer->Mailer = 'smtp';
        $mailer->SMTPAuth = $this->smtpAuth;
        $mailer->SMTPSecure = $this->smtpSecure;
        $mailer->Host = $this->smtpHost;
        $mailer->Port = $this->smtpPort;
        $mailer->Username = $this->smtpUser;
        $mailer->Password = $this->smtpPassword;
        $mailer->AuthType = $this->smtpAuthType;
        if ($this->smtpInstanceVars) Ac_Accessor::setObjectProperty($mailer->getSMTPInstance(), $this->smtpInstanceVars);
    }

    function setSmtpAuthType($smtpAuthType) {
        $this->smtpAuthType = $smtpAuthType;
    }

    function getSmtpAuthType() {
        return $this->smtpAuthType;
    }    
    
}
