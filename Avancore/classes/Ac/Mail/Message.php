<?php

class Ac_Mail_Message extends Ac_Prototyped implements Ac_I_Mail_ExtendedMessage {
    
    protected $mailRecipients = false;

    protected $mailFrom = false;

    protected $mailSubject = false;

    protected $mailHtmlBody = false;

    protected $mailTextBody = false;

    protected $mailCcRecipients = false;

    protected $mailBccRecipients = false;

    protected $mailReplyTo = false;

    /**
     * @var array
     */
    protected $mailHeaders = false;

    /**
     * @var string
     */
    protected $mailCharset = false;

    /**
     * @var array
     */
    protected $mailAttachments = false;

    function __construct($prototype = array()) {
        if (is_object($prototype) && $prototype instanceof Ac_I_Mail_Message) {
            $pp = array('mailRecipients', 'mailFrom', 'mailSubject', 'mailHtmlBody', 'mailTextBody');
            if ($prototype instanceof Ac_I_Mail_ExtendedMessage) {
                $pp = array_merge($pp, array('mailCcRecipients', 'mailBccRecipients', 
                    'mailReplyTo', 'mailHeaders', 'mailCharset'));
            }
            foreach ($pp as $prop) {
                $s = 'set'.$prop;
                $g = 'get'.$prop;
                $this->$s($prototype->$g());
            }
        } else {
            parent::__construct($prototype);
        }
    }
    
    protected function ensureItems($items, $paramName, $one = false, $class = 'Ac_Mail_Address', $check = 'Ac_I_Mail_Address') {
        if (!is_array($items)) $items = Ac_Util::toArray($items);
        $res = array();
        foreach ($items as $k => $item) {
            if (is_string($item)) $item = new Ac_Mail_Address($item);
            elseif (is_array($item)) $item = Ac_Prototyped::factory($item, $class);
            if (is_object($item)) {
                if ($item instanceof $check) $res[] = $item;
                    else throw Ac_E_InvalidCall::wrongType($paramName."[{$k}]", $item, array('string', $check));
            }
        }
        if ($one) {
            if (count($res) !== 1) {
                throw new Ac_E_InvalidCall("There must be one and only one {$paramName}, but ".count($res)." were given");
            } else {
                $res = array_pop($res);
            }
        }
        return $res;
    }
    
    function setMailRecipients($mailRecipients) {
        $this->mailRecipients = $this->ensureItems($mailRecipients, 'mailRecipients');
    }

    function getMailRecipients() {
        return $this->mailRecipients;
    }

    function setMailFrom($mailFrom) {
        $this->mailFrom = $this->ensureItems($mailFrom, 'mailFrom', true);
    }

    function getMailFrom() {
        return $this->mailFrom;
    }

    function setMailSubject($mailSubject) {
        $this->mailSubject = $mailSubject;
    }

    function getMailSubject() {
        return $this->mailSubject;
    }

    function setMailHtmlBody($mailHtmlBody) {
        $this->mailHtmlBody = $mailHtmlBody;
    }

    function getMailHtmlBody() {
        return $this->mailHtmlBody;
    }

    function setMailTextBody($mailTextBody) {
        $this->mailTextBody = $mailTextBody;
    }

    function getMailTextBody() {
        return $this->mailTextBody;
    }

    function setMailCcRecipients($mailCcRecipients) {
        $this->mailCcRecipients = $this->ensureItems($mailCcRecipients, 'mailCcRecipients');
    }

    function getMailCcRecipients() {
        return $this->mailCcRecipients;
    }

    function setMailBccRecipients($mailBccRecipients) {
        $this->mailBccRecipients = $this->ensureItems($mailBccRecipients, 'mailBccRecipients');
    }

    function getMailBccRecipients() {
        return $this->mailBccRecipients;
    }

    function setMailReplyTo($mailReplyTo) {
        $this->mailReplyTo = $this->ensureItems($mailReplyTo, 'mailReplyTo', true);
    }

    function getMailReplyTo() {
        return $this->mailReplyTo;
    }

    function setMailHeaders(array $mailHeaders = array()) {
        $this->mailHeaders = Ac_Util::toArray($mailHeaders);
    }

    /**
     * @return array
     */
    function getMailHeaders() {
        return $this->mailHeaders;
    }

    /**
     * @param string $mailCharset
     */
    function setMailCharset($mailCharset) {
        $this->mailCharset = (string) $mailCharset;
    }

    /**
     * @return string
     */
    function getMailCharset() {
        return $this->mailCharset;
    }

    function setMailAttachments(array $mailAttachments) {
        $this->mailAttachments = $this->ensureItems(
            $mailAttachments, 'mailAttachments', false, 'Ac_Mail_Attachment', 'Ac_I_Mail_Attachment');
    }

    /**
     * @return array
     */
    function getMailAttachments() {
        return $this->mailAttachments;
    }    
    
}
