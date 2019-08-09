<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ac_Test_Mail extends Ac_Test_Base {

    function testMail() {
        $a = new Ac_Mail_Address('foo <test@example.com>');
        $this->assertEqual($a->getMailAddress(), 'test@example.com');
        $this->assertEqual($a->getMailTitle(), 'foo');
        
        $mail = new Ac_Mail_Message;
        $mail->setMailRecipients('foo <test@example.com>');
        $mail->setMailFrom('sender <sender@example.com>');
        $mail->setMailTextBody('text body');
        $mail->setMailHtmlBody('<h1>HTML</h1><p>Body</p>');
        
        $mailer = new Ac_Mail_PHPMailer_Mail();
        $mailer->setNoSend(true);
        $mailer->setDumpMode(Ac_Mail_PHPMailer_Smtp::DUMP_ALL);
        $t = $this->getSampleApp()->getAdapter()->getVarTmpPath();
        $mailer->setDumpDir($this->getSampleApp()->getAdapter()->getVarTmpPath());
        $mailer->sendMail($mail);
        $this->assertTrue(strlen($p = $mailer->getLastDumpFilePrefix()));
        if ($this->assertTrue(is_file($f = $p.'.eml'))) {
            $c = file_get_contents($f);
            $this->assertTrue(strpos($c, $mail->getMailTextBody()) !== false);
            $this->assertTrue(strpos($c, $mail->getMailHtmlBody()) !== false);
            //var_dump($c);
        }
        unlink ($f);
    }
    
}