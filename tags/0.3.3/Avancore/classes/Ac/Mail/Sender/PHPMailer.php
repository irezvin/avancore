<?php

abstract class Ac_Mail_Sender_PHPMailer extends Ac_Prototyped implements Ac_I_Mail_Sender_WithDump {

    protected $lastDumpFilePrefix = false;

    protected static $tmp = false;
    
    /**
     * @var bool
     */
    protected $noSend = false;

    /**
     * @var int
     */
    protected $dumpMode = Ac_I_Mail_Sender_WithDump::DUMP_ALL_IF_NO_SEND;

    /**
     * @var string
     */
    protected $dumpDir = false;
    
    protected $dumpFilename = false;

    /**
     * @var array
     */
    protected $phpMailerExtras = array();
    
    /**
     * @var bool
     */
    protected $phpMailerExceptions = true;

    /**
     * @var string
     */
    protected $defaultCharset = false;

    /**
     * @param bool $phpMailerExceptions
     */
    function setPhpMailerExceptions($phpMailerExceptions) {
        $this->phpMailerExceptions = (bool) $phpMailerExceptions;
    }

    /**
     * @return bool
     */
    function getPhpMailerExceptions() {
        return $this->phpMailerExceptions;
    }    

    function setPhpMailerExtras(array $phpMailerExtras) {
        $this->phpMailerExtras = $phpMailerExtras;
    }

    /**
     * @return array
     */
    function getPhpMailerExtras() {
        return $this->phpMailerExtras;
    }    
    
    function applyMail(Ac_I_Mail_Message $mail, PHPMailer $mailer, array $clear = array('AllRecipients', 'ReplyTos', 'Attachments', 'CustomHeaders')) {
        
        foreach ($clear as $suffix) {
            $fn = 'clear'.$suffix;
            $mailer->$fn();
        }
        
        if (self::has($a = $mail->getMailFrom()))
            self::phpMailerAddr($mailer, 'setFrom', $a);

        if (self::has($a = $mail->getMailRecipients())) {
            self::phpMailerAddr($mailer, 'addAddress', $a);
        }

        if (strlen($a = $mail->getMailSubject())) $mailer->Subject = $a;
        if (strlen($a = $mail->getMailHtmlBody())) {
            $mailer->isHTML(true);
            $mailer->Body = $a;
            if (strlen($a = $mail->getMailTextBody())) {
                $mailer->AltBody = $a;
            }
        } else {
            if (strlen($a = $mail->getMailTextBody())) {
                $mailer->Body = $a;
            }
        }
        
        if ($mail instanceof Ac_I_Mail_ExtendedMessage) {
        
            if (self::has($a = $mail->getMailCcRecipients())) {
                self::phpMailerAddr($mailer, 'addCC', $a);
            }
        
            if (self::has($a = $mail->getMailBccRecipients())) {
                self::phpMailerAddr($mailer, 'addBCC', $a);
            }
            
            if (self::has($a = $mail->getMailReplyTo())) {
                self::phpMailerAddr($mailer, 'addReplyTo', $a);
            }
            
            foreach (Ac_Util::toArray($mail->getMailAttachments()) as $i => $item) {
                if ($item instanceof Ac_I_Mail_Attachment) {
                    if (!is_array(self::$tmp)) {
                        register_shutdown_function(array(__CLASS__, 'cleanTmp'));
                        self::$tmp = array();
                    }
                    $tmp = tmpfile($item);
                    self::$tmp[] = $tmp;
                    file_put_contents($tmp, $item->getAttachmentContent());
                    $mailer->addAttachment($tmp, $item->getAttachmentFilename(), 
                        'base64', $item->getAttachmentContentType(), 
                        $item->getAttachmentContentDisposition());
                } else {
                    throw new Ac_E_InvalidImplementation('Ac_I_Mail_ExtendedMessage::getMailAttachments() '
                        . 'must return only Ac_I_Mail_Attachment instances; instead of that, '
                        . get_class($mail).'::getMailAttachments() has returned '
                        . Ac_Util::typeClass($item).' at position \''.$i.'\'');
                }
            }
            
            foreach (Ac_Util::toArray($mail->getMailHeaders()) as $k => $v) {
                if (is_numeric($k)) {
                    $mailer->addCustomHeader($v);
                } else {
                    $mailer->addCustomHeader($k, $v);
                }
            }
            
            if (strlen($cs = $mail->getMailCharset())) $mailer->CharSet = $cs;
            elseif (strlen($dcs = $this->getDefaultCharset())) $mailer->CharSet = $dcs;
        }
    }
    
    /**
     * @param Ac_I_Mail_ExtendedMessage $mail
     * @return PHPMailer
     */
    function createPHPMailer(Ac_I_Mail_Message $mail = null) {
        if (!class_exists('PHPMailer', false)) 
            require_once(Ac_Avancore::getInstance()->getAdapter()->getVendorPath().'/PHPMailer/class.phpmailer.php');
        
        $mailer = new PHPMailer($this->phpMailerExceptions);
        
        $this->doConfigureSender($mailer);
        
        if ($this->phpMailerExtras)
            Ac_Accessor::setObjectProperty($mailer, $this->phpMailerExtras);
        
        if ($mail) $this->applyMail ($mail, $mailer);
        
        return $mailer;
    }
    
    protected static function has($something) {
        $res = is_object($something) || is_string($something) && strlen($something) || !empty($something);
        return $res;
    }

    protected static function phpMailerAddr(PHPMailer $mailer, $method, $address) {
        if (is_array($address)) {
            $res = array();
            $args = func_get_args();
            foreach ($address as $k => $a) {
                $args[2] = $a;
                $res[$k] = call_user_func_array(array(__CLASS__, __FUNCTION__), $args);
            }
        } else {
            $a = new Ac_Mail_Address($address);
            $args = func_get_args();
            array_shift($args);
            array_shift($args);
            array_shift($args);
            array_unshift($args, $a->getMailTitle());
            array_unshift($args, $a->getMailAddress());
            $res = call_user_func_array(array($mailer, $method), $args);
        }
        return $res;
    }
    
    protected static function cleanTmp() {
        foreach (self::$tmp as $f) unlink($f);
        self::$tmp = array();
    }
    
    abstract protected function doConfigureSender(PHPMailer $mailer);
    
    public function sendMail(Ac_I_Mail_Message $mail, array &$errors = array()) {
        $mailer = $this->createPHPMailer($mail);
        $e = null;
        try {
            if (!$this->noSend) $res = (bool) $mailer->send();
                else $res = true;
        } catch (phpmailerException $e) {
        }
        if ($e) $res = false;
        if (!$res) $errors = array('mailer' => $mailer->ErrorInfo);
        if ($this->dumpMode) {
            $content = false;
            $error = false;
            if ($this->dumpMode == self::DUMP_ALL || $this->dumpMode == $this->noSend && self::DUMP_ALL_IF_NO_SEND) {
                $content = $this->getMailContent($mailer);
            }
            if ($errors) {
                $error = print_r($errors, 1);
            }
            $this->dumpToFile($content, $error);
        }
        if ($e) throw $e;
        return $res;
    }
    
    function getMailContent(PHPMailer $mailer) {
        $tmp = clone $mailer;
        $tmp->preSend();
        if(!empty($tmp->AltBody))
            $tmp->ContentType = "multipart/alternative";
        $res = ($tmp->createHeader()."\n\n".$tmp->createBody());
        return $res;
    }
    
    protected function dumpToFile($content, $error) {
        $this->lastDumpFilePrefix = false;
        if (strlen($this->dumpFilename)) {
            $emlFileName = $this->dumpFilename.'.eml';
            $errFileName = $this->dumpFilename.'-error.txt';
            $this->lastDumpFilePrefix = $this->dumpFilename;
        } elseif ($this->dumpDir !== false) {
            if (($d = is_dir($this->dumpDir)) && ($w = is_writable($this->dumpDir))) {
                $dir = $this->dumpDir;
                $fname = date("Y-m-d-h-i-s");
                $suffix = -1;
                do {
                    $suffix++;
                    $suff = $suffix? '-'.$suffix : '';
                    $this->lastDumpFilePrefix = $dir.'/'.$fname.$suff;
                    $emlFileName = $dir.'/'.$fname.$suff.'.eml';
                    $errFileName = $dir.'/'.$fname.$suff.'-error.txt';
                } while (strlen($content) && is_file($emlFileName) || strlen($error) && is_file($errFileName));
            } else {
                if ($d) 
                    throw new Ac_E_InvalidUsage("\$dumpDir points to non-existent directory: '{$this->dumpDir}'");
                else 
                    throw new Ac_E_InvalidUsage("\$dumpDir points to non-writeable directory: '{$this->dumpDir}'");
            }
        }
        if (strlen($content) && isset($emlFileName)) file_put_contents($emlFileName, $content);
        if (strlen($error) && isset($errFileName)) file_put_contents($errFileName, $error);
    }

    /**
     * @param bool $noSend
     */
    function setNoSend($noSend) {
        $this->noSend = $noSend;
    }

    /**
     * @return bool
     */
    function getNoSend() {
        return $this->noSend;
    }

    /**
     * @param int $dumpMode
     */
    function setDumpMode($dumpMode) {
        $this->dumpMode = $dumpMode;
    }

    /**
     * @return int
     */
    function getDumpMode() {
        return $this->dumpMode;
    }

    /**
     * @param string $dumpDir
     */
    function setDumpDir($dumpDir) {
        $this->dumpDir = $dumpDir;
    }

    /**
     * @return string
     */
    function getDumpDir() {
        return $this->dumpDir;
    }    
    
    function getLastDumpFilePrefix() {
        return $this->lastDumpFilePrefix;
    }

    /**
     * @param string $defaultCharset
     */
    function setDefaultCharset($defaultCharset) {
        $this->defaultCharset = $defaultCharset;
    }

    /**
     * @return string
     */
    function getDefaultCharset($guess = false) {
        $res = $this->defaultCharset;
        if ($guess && $res === false) {
            if (class_exists('Ac_Application', false)) {
                $def = Ac_Application::getDefaultInstance();
                if ($def) $res = $def->getAdapter()->getCharset();
            }
        }
        return $res;
    }
    
}