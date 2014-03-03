<?php

class Ac_Mail_PHPMailer_SendMail extends Ac_Mail_Sender_PHPMailer {
    
    protected function doConfigureSender(PHPMailer $mailer) {
        $mailer->Mailer = 'sendmail';
    }
    
}