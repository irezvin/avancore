<?php

class Ac_Mail_Address extends Ac_Prototyped implements Ac_I_Mail_Address {
    
    protected $mailAddress = null;

    protected $mailTitle = null;
    
    function __construct($emailOrAddress = null, $mailTitle = null) {
        if (is_object($emailOrAddress) && ($emailOrAddress instanceof Ac_I_Mail_Address)) {
            $this->mailTitle = $emailOrAddress->getMailTitle();
            $this->mailAddress = $emailOrAddress->getMailAddress();
        } else {
            if (is_array($emailOrAddress)) Ac_Prototyped::__construct($emailOrAddress);
            elseif (is_string($emailOrAddress)) {
                if (is_null($mailTitle)) $this->setAddress($emailOrAddress);
                else $this->setMailAddress($emailOrAddress);
            }
        }
        if (!is_null($mailTitle)) {
            $this->mailTitle = $mailTitle;
        }
    }

    function setMailAddress($mailAddress) {
        $this->mailAddress = $mailAddress;
    }

    function getMailAddress() {
        return $this->mailAddress;
    }

    function setMailTitle($mailTitle) {
        $this->mailTitle = $mailTitle;
    }

    function getMailTitle() {
        return $this->mailTitle;
    } 
    
    function setAddress($address) {
        if (preg_match("#<([^>]+)>#", $address, $matches)) {
            $this->mailAddress = trim($matches[0], "<>");
            $this->mailTitle = trim(str_replace($matches[0], "", $address));
        } else {
            $this->mailaddress = ''.$address;
            $this->mailTitle = null;
        }
    }
    
    function getAddress() {
        if (!strlen($this->mailAddress)) throw new Ac_E_InvalidUsage("Cannot ".__METHOD__." setMailAddress() or setAddress() first");
        return Ac_Mail_Util::composeAddress($this);
    }
    
}