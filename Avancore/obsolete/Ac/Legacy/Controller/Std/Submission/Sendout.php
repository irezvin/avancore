<?php

class Ac_Legacy_Controller_Std_Submission_Sendout {
    
    /**
     * Sendout identifier (key in the sendouts array)
     * @var string
     */
    var $id = false;
    
    /**
     * @var Ac_Legacy_Controller_Std_Submission
     */
    var $_submission = false;
    /**
     * @var Ac_Model_Object 
     */
    var $_modelObject = false;
    /**
     * @var Ac_Legacy_Controller_Std_Submission_Template
     */
    var $_template = false;
    
    /**
     * @var Ac_Mail
     */
    var $_mail = false;
    
    /**
     * Mail subject
     * @var string
     */
    var $subject = false;
    
    /**
     * Recipients (will be supplied to $mail->to property)
     * @var array
     */
    var $recipients = false;
    
    /**
     * If there are no $recipients and this property is true, email will be sent to all Joomla admin users that want to accept emails  
     * @var bool
     */
    var $recipientsAreAdmins = false;
    
    var $from = false;
    
    var $replyTo = false;
    
    /**
     * Model meta-property that marks model properties 
     * @var string
     */
    var $propertyFlag = false;
    
    /**
     * Name of template part to render before object contents. Should be interpreted by the template. 
     * @var string
     */
    var $templatePrefixPart = false;
    
    /**
     * Name of template part that renders object contents. Should be interpreted by the template.
     * @var string
     */
    var $templateBodyPart = 'object';
    
    /**
     * Name of template part to render after object contents. Should be interpreted by the template.
     * @var string
     */
    var $templateSuffixPart = false;
    
    /**
     * Name of template part to render object html body. Is called by the Sendout object.
     * @var string
     */
    var $templatePart = 'email';

    /**
     * Extra settings of email object
     * @var array
     */
    var $mailExtraSettings = array();
    
    /**
     * Whether Ac_Mail object should be created if no recipients found (for example, if application should fill recipients later)
     * @var bool
     */
    var $createMailIfNoRecipients = false;
    
    /**
     * @param Ac_Legacy_Controller_Std_Submission $submission Controller that processes object submission from the Web
     * @param Ac_Model_Object $modelObject Object that was submitted and, maybe, stored to the database
     * @param Ac_Legacy_Template_Html $template Controller's template object (to render template parts)
     * @param array $prototype Values to initialize $this object properties
     * @return Ac_Legacy_Controller_Std_Submission_Sendout 
     */
    function Ac_Legacy_Controller_Std_Submission_Sendout ($submission, $modelObject, $template, $prototype = array()) {
        $this->_submission = $submission;
        $this->_modelObject = $modelObject;
        $this->_template = $template;
        Ac_Util::simpleBind($prototype, $this);
    }
    
    /**
     * @return array
     */
    function getRecipients() {
        if ($this->recipients) $res = $this->recipients;
        elseif ($this->recipientsAreAdmins) {
            $mapper = Ac_Model_Mapper::getMapper('Mos_User_Mapper');
            $res = $mapper->getMailAdmins();
        } else $res = array();
        return $res;
    }
    
    /**
     * Composes and returns mail object
     * @return Ac_Mail
     */
    function getMail() {
        if ($this->_mail === false) {
             $rcpt = $this->getRecipients();
             if ($rcpt) {
                 $this->_mail = new Ac_Mail(false, $rcpt, $this->subject, $this->from);
                 Ac_Util::simpleBind($this->mailExtraSettings, $this->_mail);
                 if ($this->replyTo) $this->_mail->replyTo = $this->replyTo;
                 $this->_template->currentSendout = $this;
                 $this->_mail->htmlBody = $this->_template->fetch($this->templatePart);
             } else $this->_mail = null;
        }
        return $this->_mail;
    }
    
}

?>