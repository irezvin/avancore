<?php

Ae_Dispatcher::loadClass('Ae_Table_Column');

/**
 * This class shows record's published status icon along with the link to change record's publishing 
 *  
 * @package Avancore Lite
 * @copyright Copyright &copy; 2007, Ilya Rezvin, Avansite (I.Rezvin@avansite.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

class Ae_Table_Column_Published extends Ae_Table_Column {
    
    function getPublishedImg() {
        if (isset($this->settings['publishedImg'])) $res = $this->settings['publishedImg'];
            else $res = 'publish_g.png';
        return $res;
    }
    
    function getUnpublishedImg() {
        if (isset($this->settings['unpublishedImg'])) $res = $this->settings['unpublishedImg'];
            else $res = 'publish_x.png';
        return $res;
    }
    
    function getPublishedAlt() {
        if (isset($this->settings['publishAlt'])) $res = $this->settings['publishAlt'];
            else $res = 'published';
        return $res;
    }
    
    function getUnpublishedAlt() {
        if (isset($this->settings['unpublishAlt'])) $res = $this->settings['unpublishAlt'];
            else $res = 'unpublished';
        return $res;
    }
    
    function getPublishTask() {
        if (isset($this->settings['publishTask'])) $res = $this->settings['publishTask'];
            else $res = 'publish';
        return $res;
    }
    
    function getUnpublishTask() {
        if (isset($this->settings['unpublishTask'])) $res = $this->settings['unpublishTask'];
            else $res = 'unpublish';
        return $res;
    }
    
    function getTitle() {
        if (isset($this->settings['title'])) $res = $this->settings['title'];
            else $res = "Published";
        return $res;
    }
    
    function getHeaderAttribs($rowCount, $rowNo = 1) {
        if (isset($this->settings['headerAttribs'])) $res = $this->settings['headerAttribs'];
            else $res = array('align' => 'left', 'width' => '20');
        $res['rowspan'] = $this->getHeaderRowspan($rowCount, $rowNo);
        return $res;
    }
    
    function getCellAttribs() {
        if (isset($this->settings['cellAttribs'])) $res = $this->settings['cellAttribs'];
            else $res = array('align' => 'center');
        return $res;
    }
    
    function getData(& $record, $rowNo) {
        $data = intval(parent::getData($record, $rowNo, $this->fieldName));
        $img = $data? $this->getPublishedImg() : $this->getUnpublishedImg();
        $alt = $data? $this->getPublishedAlt() : $this->getUnpublishedAlt();
        $task = $data? $this->getUnpublishTask() : $this->getPublishTask();
        
        $res =  "<a ".
                    Ae_Util::mkAttribs(array(
                        'href' => "javascript: void(0);", 
                        'onclick' => "return listItemTask('cb{$rowNo}', '{$task}');"
                    )).
                "> ".
                    "<img ".
                        Ae_Util::mkAttribs(array(
                            'src' => "images/$img", 
                            'width' => 12,
                            'height' => 12,
                            'border' => 0,
                            'alt' => $alt,
                        )).
                    "/> ".
                "</a>";
        return $res; 
    }
    
}

?>
    