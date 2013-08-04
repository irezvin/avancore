<?php

class Ac_Legacy_Template_JoomlaPage extends Ac_Legacy_Template_Html {
    
    var $addXmlTag = true;
    
    var $htmlAttribs = array();
    
    var $bodyAttribs = array();
    
    var $doctypeTag = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
    
    var $titleConcatenator = ' - ';
    
    var $pathwayConcatenator = ' &raquo; ';
    
    /**
     * Show xdebug_time_index() before </body> tag (for debug purposes)
     *
     * @var bool
     */
    var $showTimeIndex = false; 
    
    /**
     * Show database queries before </body> tag (for debug purposes)
     *
     * @var bool
     */
    var $showQueries = false;

    function getXmlTag() {
        return '<'.'?xml version="1.0" encoding="'.$this->charset.'"?'.'>';
    }
    
    function getTitle() {
        return implode($this->titleConcatenator, $this->htmlResponse->pageTitle);
    }
    
    function showHead() {
?>
    <!-- powered by Avancore 0.2 -->
<?php if (0 && $this->getTitle()) { ?>

    <title><?php echo $this->getTitle(); ?></title>
<?php } ?>
<?php if ($this->htmlResponse->jsLibs) { ?>
<?php foreach ($this->htmlResponse->jsLibs as $l) { ?>
    <?php echo $this->htmlResponse->getJsScriptTag($l[0], $l[1]); ?>

<?php } ?>
<?php } ?>
<?php if ($this->htmlResponse->cssLibs) { ?>
<?php foreach ($this->htmlResponse->cssLibs as $l) { ?>
    <?php echo $this->htmlResponse->getCssLibTag($l[0], $l[1]); ?>

<?php } ?>
<?php } ?>
<?php
    }
    
    function showBody() {
?><div <?php if ($this->bodyAttribs) echo " ".Ac_Util::mkAttribs($this->bodyAttribs); ?>>
<?php echo $this->htmlResponse->content; ?>

<?php 
    if($this->showTimeIndex && function_exists('xdebug_time_index')) var_dump(xdebug_time_index()); 
    if($this->showTimeIndex && function_exists('memory_get_usage') && function_exists('memory_get_peak_usage')) {
        var_dump(memory_get_usage(), memory_get_peak_usage());
    }
    if($this->showQueries) $this->_showQueries();
?></div>
<?php        
    }
    
    function showDefault() {
        $nl = "";
        if ($this->addXmlTag) {
            if (!isset($this->htmlAttribs['xmlns'])) $this->htmlAttribs['xmlns'] = 'http://www.w3.org/1999/xhtml';
            echo $this->getXmlTag();
            $nl = "\n"; 
        }
        if (strlen($this->doctypeTag)) {
            echo $this->doctypeTag;
            $nl = "\n"; 
        }
        echo $nl;
?><div<?php if ($this->htmlAttribs) echo " ".Ac_Util::mkAttribs($this->htmlAttribs); ?>>
<?php $this->showHead(); ?>
    
<?php $this->showBody(); ?>

</div><?php        
    }
    
    function _showQueries() {
        $disp = Ac_Dispatcher::getInstance();
        $db = $disp->database;
        if (is_a($db, 'Ac_Legacy_Database_Native') && $db->trackQueries) {
            foreach ($db->queries as $q) echo '<hr /><pre>'.htmlspecialchars($q).'</pre>';
        }
    }
    
}

?>