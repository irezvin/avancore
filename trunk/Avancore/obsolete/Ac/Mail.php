<?php

class Ac_Mail {

    var $useNewMailer = true;
    
    /**
     * Recipients
     * @var array 
     */
    var $_to = false;
    
    /**
     * Senders
     * @var array
     */
    var $_from = false;
    
    var $_bcc = false;

    var $replyTo = false;
    
    /**
     * Template name for html body (it is required)
     * @var string
     */
    var $_htmlTemplate = false;
    
    /**
     * Template name for text
     * @var string
     */
    var $_textTemplate = false;

    /**
     * @var Ac_Legacy_Template
     */
    var $templateObject = false;
    
    var $htmlBody = false;
    
    var $textBody = false;
    
    protected $preparedHtmlBody = false;
    
    protected $preparedTextBody = false;
    
    /**
     * Automatically create text version from HTML
     */
    var $autoTextBody = true;
    
    var $defaultSubject = 'Notification';
    
    var $charset = false;
    
    var $_subject = false;

    var $_error = false;
    
    /**
     * @var array
     */
    var $_data = array();
    
    /**
     * @var bool
     * Whether store sent mail in '_email' dir or not
     */
    var $_storeSentMail = true;
    
    var $_noSend = '?';

    /**
     * How mail shall be sent. true = auto, 'smtp', 'mail', 'sendmail' also works
     * 
     * @var mixed
     */
    
    var $method = true;
    //var $method = 'mail';

    var $smtpAuth = 0;
    
    /**
     * @var false | '' | 'ssl' | 'tls'
     */
    var $smtpSecure = false;
    
    var $smtpHost = false;
    
    var $smtpPort = false;
    
    var $smtpUser = false;
    
    var $smtpPassword = false;
    
    var $debugFilename = false;
    
    var $dontSaveErrors = false;
    
    var $_emailFileContent = false;

    /**
     * @return Ac_Mail
     */
    
    function factory($templateName, $to, $defaultSubject = false, $from = false, $data = array(), $textTemplate = false) {
        $res = new Ac_Mail($templateName, $to, $defaultSubject, $from, $data, $textTemplate);
        return $res;
    }
    
    function Ac_Mail ($templateName, $to = false, $defaultSubject = false, $from = false, $data = array(), $textTemplate = false) {
        $this->_htmlTemplate = $templateName;
        $this->_textTemplate = $textTemplate;
        $this->_to = $to;
        $this->_from = $from;
        $this->_data = $data;
        $disp = Ac_Dispatcher::getInstance();
        if (is_object($disp) && $disp->config && $this->_from === false) {
            $this->_from = array($disp->config->mailFrom, $disp->config->fromName);
        }
        if ($defaultSubject !== false) $this->defaultSubject = $defaultSubject;
        if ($this->_noSend === '?' && $disp && $disp->config) {
            $this->_noSend = !$disp->config->sendEmails;
        }
    }
    
    /**
     * @param PhpMailer $mailer
     */
    function configureMailer($mailer) {
        if ($this->method === true) {
            $disp = Ac_Dispatcher::getInstance();
            $method = $disp->config->mailer;
            $smtpHost = $disp->config->smtpHost;
            $smtpSecure = $disp->config->smtpSecure;
            $smtpPort = $mailer->Port;
            $smtpPassword = $disp->config->smtpPass;
            $smtpUser = $disp->config->smtpUser;
            $smtpAuth = $disp->config->smtpAuth;
        } else {
            $method = $this->method;
            $smtpHost = $this->smtpHost;
            $smtpPort = $this->smtpPort;
            $smtpAuth = $this->smtpAuth;
            $smtpUser = $this->smtpUser;
            $smtpSecure = $this->smtpSecure;
            $smtpPassword = $this->smtpPassword;
        }
        
        $mailer->Mailer = $method;
        $mailer->Host = $smtpHost;
        $mailer->Username = $smtpUser;
        $mailer->Password = $smtpPassword;
        $mailer->Port = $smtpPort;
        $mailer->SMTPAuth = $smtpAuth;
        $mailer->SMTPSecure = $smtpSecure;
        
    }
    
    function getError() {
        $res = $this->_error;
        return $res;
    }
    
    function setTo($to) {
        $this->_to = $to;
    }
    
    function setFrom($from) {
        $this->_from = $from;
    }
    
    function setBcc($bcc) {
        $this->_bcc = $bcc;
    }
    
    function getBcc() {
        return $this->_bcc;
    }
    
    function getFrom() {
        return $this->_from;
    }
    
    function setData($data, $key = false) {
        if ($key !== false) $this->_data[$key] = $data;
            else $this->_data = $data;
    }
    
    function setTextTemplate($templateName) {
        $this->_textTemplate = $templateName;
    }
    
    function setHtmlTemplate($templateName) {
        $this->_htmlTemplate = $templateName;
    }
    
    function getPreparedHtmlBody() {
        if ($this->preparedHtmlBody === false) {
            if (strlen($this->htmlBody)) $htmlBody = $this->htmlBody; else {
                if ($this->_htmlTemplate) {

                    if (is_string($this->_htmlTemplate)) {
                        $htmlBody = $this->fetchTemplate($this->_htmlTemplate, $this->_data);
                    } else {
                         if (is_callable($this->_htmlTemplate)) {
                             $htmlBody = call_user_func($this->_htmlTemplate);
                         } else {
                             $htmlBody = false;
                         }
                    }

                } else {
                    $htmlBody = false;
                }
            }
            $this->preparedHtmlBody = $htmlBody;
        }
        return $this->preparedHtmlBody;
    }
    
    function getPreparedTextBody() {
        if ($this->preparedTextBody === false) {
            if (strlen($this->textBody)) $textBody = $this->textBody; 
            else {
                if ($this->_textTemplate) {

                    if (is_string($this->_textTemplate)) {
                        $textBody = $this->fetchTemplate($this->_textTemplate, $this->_data);
                    } else {
                         if (is_callable($this->_textTemplate)) {
                             $textBody = call_user_func($this->_textTemplate);
                         } else {
                             $textBody = false;
                         }
                    }

                } else {
                    if (strlen($htmlBody = $this->getPreparedHtmlBody()) && $this->autoTextBody) {
                        $h2t = $this->createHtml2Text();
                        $h2t->set_html($htmlBody);
                        $textBody = $h2t->get_text(); 
                    } else $textBody = false;
                }
            }
            $this->preparedTextBody = $textBody;
        }
        return $this->preparedTextBody;
    }
    
    function resetPreparedHtmlAndText() {
        $this->preparedHtmlBody = false;
        $this->preparedTextBody = false;
    }
    
    function send() {
        $triedToSend = false;
        
        $m = $this->createPhpMailer();
        $this->configureMailer($m);
        
        $from = $this->_getAddresses(array($this->_from), true);
        $res = true;
        if (!$m->From) {
            $this->_error = "'From' address missing";
            $res = false;
        } else {
            $m->From = $from[0];
            $m->FromName = (string) $from[1];
            $to = $this->_getAddresses($this->_to);
            if ($this->replyTo) {
                $replyTo = $this->_getAddresses($this->replyTo);
                foreach ($replyTo as $address) {
                     $m->AddReplyTo($address[0], (string) $address[1]);
                }
            }
            
            if (!$to) {
                $this->_error = "'To' address missing";
                $res = false;
            } else {
                foreach($to as $toAddress) {
                    $m->AddAddress($toAddress[0], (string) $toAddress[1]);
                }
                $bcc = $this->_getAddresses($this->_bcc);
                foreach ($bcc as $addr) {
                    $m->AddBcc($addr[0], (string) $addr[1]);
                }
                if (!$this->_htmlTemplate && !$this->_textTemplate && !strlen($this->htmlBody) && !strlen($this->textBody)) {
                    $this->_error = "No templates defined";
                    $res = false;
                } else {
                    if (!strlen($this->_subject))
                        $this->setSubject($this->defaultSubject);

                    $htmlBody = $this->getPreparedHtmlBody();
                    $textBody = $this->getPreparedTextBody();
                    
                    if (strlen($htmlBody)) $m->Body = $htmlBody; 
                    if (strlen($textBody)) $m->AltBody = $textBody;
                    
                    //var_dump($m->Body);
                    //var_dump($m->AltBody);
                    
                    if ($this->charset === false && defined('_ISO')) {
                        $iso = explode('=', _ISO, 2);
                        $charset = $iso[1];
                    } else {
                        $charset = $this->charset;
                    }
                    $m->CharSet = $charset;
                    $m->Subject = $this->_subject;
                    $triedToSend = true;
                    if (! ($this->_noSend || ($m->Send()) )) {
                        $this->_error = "Mailer error: ".$m->ErrorInfo;
                        $res = false;
                    } else {
                        // Send Ok
                    }
                }
            }
        }
        
        $this->_debugToFile($m, $triedToSend && $this->_storeSentMail);
        
        if ($this->_error) {
            $disp = Ac_Dispatcher::getInstance();
            if ($disp->config && $disp->config->debug) {
                trigger_error($this->getError(), E_USER_WARNING);
            }
        }
        
        return $res;
    }
    
    function setSubject($subject) {
        $this->_subject = $subject;
    }
    
    function showTemplate($templateName, $vars = array()) {
        $disp = Ac_Dispatcher::getInstance();
        $filename = $disp->getDir()."/templates/".$templateName.".tpl.php";
        extract($vars);
        require($filename);
    }
    
    function fetchTemplate($templateName, $vars = array()) {
        if ($this->templateObject && is_a($this->templateObject, 'Ac_Legacy_Template')) {
            // A. Brave new templates 
            $this->templateObject->setVars($vars);
            return $this->templateObject->fetch($templateName);
        } else {
            // B. O1d-sk00l - include files...
            
            ob_start();
            $this->showTemplate($templateName, $vars);
            return ob_get_clean();
        } 
    }
        
    protected function getMailerDir() {
        $disp = Ac_Dispatcher::getInstance();
        return $this->useNewMailer? $disp->getVendorDir().'/PHPMailerNew' : $disp->getVendorDir().'/PHPMailer';
    } 
    
    /**
     * @return PHPMailer
     */
    function createPhpMailer() {
        if (!class_exists('PHPMailer', false)) {
            require($this->getMailerDir().'/class.phpmailer.php');
        }
        $res = new PHPMailer();
        $res->SetLanguage('en', $langDir = $this->getMailerDir().'/language/');
        return $res;
    }
    
    /**
     * @return html2text
     */
    function createHtml2Text() {
        if (!class_exists('html2text', false)) {
            $disp = Ac_Dispatcher::getInstance();
            require($disp->getVendorDir().'/html2text/html2text.php');
        }
        $res = new html2text();
        return $res;
    }
    
    function _getAddresses($src, $firstOne = false) {
        if (!is_array($src)) $src = array($src);
        $res = array();
        foreach (array_keys($src) as $k) {
            $entry = $src[$k];
            $address = $this->_getAddress($entry);
            if ($address[0]) 
                $res[] = $address;
        }
        
        if ($firstOne) $res = count($res)? $res[0] : false;
        
        return $res;
    }
    
    function _getAddress($entry) {
        $title = false;
        $email = false;
        if (is_string($entry)) {
            $email = $entry;    
        } elseif (is_array($entry)) {
            if (isset($entry['emailTitle'])) $title = $entry['emailTitle'];
            if (isset($entry['email'])) $email = $entry['email'];
            if (!$email && count($entry) == 2 && isset($entry[0]) && isset($entry[1])) {
                $email = $entry[0];
                $title = $entry[1];
            }
        } elseif (is_object($entry)) {
            $title = Ac_Util::getObjectProperty($entry, 'emailTitle', false);
            $email = Ac_Util::getObjectProperty($entry, 'email', false);
        }
        if ($email) $res = array($email, $title);
            else $res = false;
        return $res;
    }
    
    /**
     * @param PhpMailer $mailer
     */
    function _debugToFile($mailer, $saveEmail, $saveError = true) {
        $disp = Ac_Dispatcher::getInstance();
        
        if (!strlen($this->debugFilename)) {
        
            if (isset($disp->config->emailsSavePath) && strlen($disp->config->emailsSavePath)) $dir = $disp->config->emailsSavePath;
                else $dir = $disp->getDir().'/emails';

            if (!is_dir($dir)) mkdir($dir, 0777);
            
            $fname = date("Y-m-d-h-i-s");
            $suffix = -1;

            if($saveEmail || $saveError) {
                do {
                    $suffix++;
                    $suff = $suffix? '-'.$suffix : '';
                    $emlFileName = $dir.'/'.$fname.$suff.'.eml';
                    $errFileName = $dir.'/'.$fname.$suff.'-error.txt';
                } while (is_file($emlFileName) || is_file($errFileName));
                if ($saveEmail) touch($emlFileName);
                if ($this->_error && $saveError) touch($errFileName);
            }        
        } else {
            $emlFileName = $this->debugFilename.'.eml';
            $errFileName = $this->debugFilename.'-error.txt';
        }
        if ($this->_error && $saveError && !$this->dontSaveErrors) touch($errFileName);
            
        if(!empty($mailer->AltBody))
            $mailer->ContentType = "multipart/alternative";
        $mailer->error_count = 0; // reset errors
        $mailer->SetMessageType();
        
        if ($saveEmail) {
            $file = fopen($emlFileName, "w");
            
    
            $this->_emailFileContent = $message = ($mailer->createHeader()."\n\n".$mailer->createBody());
            fputs($file, $message, strlen($message));
            fclose($file);
        } else {
            $this->_emailFileContent = ($mailer->createHeader()."\n\n".$mailer->createBody());
        } 
        
        if ($this->_error && $saveError) {
            $file = fopen($errFileName, "w");
            fputs($file, print_r($this->_error, 1));
            fclose($file);
        }
    }
    
    function getEmailFileContent() {
        return $this->_emailFileContent;
    }
    
    
    
}
