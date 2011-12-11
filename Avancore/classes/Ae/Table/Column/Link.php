<?php

Ae_Dispatcher::loadClass('Ae_Table_Column');

/**
 * This class shows cell with the link to edit the record
 * 
 * @package Avancore Lite
 * @copyright Copyright &copy; 2007, Ilya Rezvin, Avansite (I.Rezvin@avansite.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

class Ae_Table_Column_Link extends Ae_Table_Column {

    var $isRawUrl = false;
    var $isEmail = false;
    var $linkAttribs = array();
    var $_urlStaticPart = false;
    var $fastUrl = true;
    var $_aAttribs = false;
    var $idColumnName = false;
    var $idUrlName = false;
    var $taskName = 'edit';
    var $linkTitle = 'Edit';
    var $staticLinkText = false;
    var $hideMainMenu = true;
    var $urlExtraParams = array();

    function doOnCreate() {
        parent::doOnCreate();
        if (!$this->idColumnName) $this->idColumnName = 'id';
        if (!$this->idUrlName) $this->idUrlName = $this->idColumnName;
    }
    
    function getLinkText($record, $rowNo) {
        if ($this->staticLinkText) return $this->staticLinkText;
            else return parent::getData($record, $rowNo, $this->fieldName);
    }
    
    function updateAttribs() {
        parent::updateAttribs();
        $this->_aAttribs = Ae_Util::mkAttribs(Ae_Util::m($this->linkAttribs, array('title' => $this->linkTitle)));
    }
    
    function getData($record, $rowNo) {
        if ($strUrl = $this->getStringUrl($record)) {
            $res =  '<a '.$this->_aAttribs.' href="'.$strUrl.'">'.$this->getLinkText($record, $rowNo).'</a>';
        } else $res = $this->nullText;
        return $res;
    }
    
    function getStringUrl($record) {
        if ($this->isRawUrl) {
            
            $res = $this->getRecordProperty($record, $this->fieldName);
            
        } elseif ($this->isEmail) {
            
            $prop = $this->getRecordProperty($record, $this->fieldName);
            if ($prop) $res = 'mailto:'.$prop;
                else $res = ''; 
            
        } else {
            
            if ($this->fastUrl) {
                if ($this->_urlStaticPart === false) {
                    $url = & $this->getUrl();
                    Ae_Util::unsetArrayByPath($url->query, Ae_Util::pathToArray($this->idUrlName));
                    $this->_urlStaticPart = $url->toString();
                    if (!$url->query) $this->_urlStaticPart .= "?"; else $this->_urlStaticPart .= "&";
                    $this->_urlStaticPart .= $this->idUrlName."="; 
                    $this->_urlStaticPart = htmlspecialchars($this->_urlStaticPart);
                }
                $res = $this->_urlStaticPart.htmlspecialchars($this->getRecordProperty($record, $this->idColumnName));
            } else {
                $url = & $this->getUrl();
                Ae_Util::setArrayByPath($url->query, Ae_Util::pathToArray($this->idColumnName), $this->getRecordProperty($record, $this->idColumnName));
                $res = $url->toString();
            }
        }
        
        return $res;
    }
    
    function getUrl() {
        $disp = & Ae_Dispatcher::getInstance();
        $url = & $disp->getUrl();
        if (isset($GLOBALS['Itemid']) && $GLOBALS['Itemid']) $url->query['Itemid'] = $GLOBALS['Itemid'];
        $url->query['task'] = $this->taskName;
        if ($this->hideMainMenu) $url->query['hidemainmenu'] = 1;
        if (is_array($this->urlExtraParams)) Ae_Util::ms($url->query, $this->urlExtraParams);
        return $url;
    }
    
}
?>