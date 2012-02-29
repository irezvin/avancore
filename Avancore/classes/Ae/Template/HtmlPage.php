<?php

Ae_Dispatcher::loadClass('Ae_Template_Html');

class Ae_Template_HtmlPage extends Ae_Template_Html {

    const doctypeTransitional = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
    const doctypeStrict = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
    const doctypeNone = "";
    
    var $addXmlTag = true;
    
    var $htmlAttribs = array();
    
    var $bodyAttribs = array();
    
    var $doctypeTag = self::doctypeStrict;
    
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

    function getCharset() {
        if (strlen($this->charset)) $res = $this->charset;
        elseif (preg_match('/charset=(.*)$/i', $this->htmlResponse->contentType, $matches)) {
            $res = $matches[1];
        } else {
            $res = 'utf-8';
        }
        return $res;
    }
    
    function getXmlTag() {
        return ''; // Major sites don't put XML tag in front of HTML page, why should I?
        //return '<'.'?xml version="1.0" encoding="'.$this->getCharset().'"?'.'>';
    }
    
    function getTitle() {
        return implode($this->titleConcatenator, $this->htmlResponse->pageTitle);
    }
    
    function showJsLibs() {
?>
<?php       if ($this->htmlResponse->jsLibs) { ?>
<?php           foreach ($this->htmlResponse->jsLibs as $l) { ?>
    <?php           echo $this->htmlResponse->getJsScriptTag($l[0], $l[1]); ?>

<?php           } ?>
<?php       } ?>
<?php       
    }
    
    function showCssLibs() {
?>
<?php       if ($this->htmlResponse->cssLibs) { ?>
<?php           foreach ($this->htmlResponse->cssLibs as $l) { ?>
    <?php           echo $this->htmlResponse->getCssLibTag($l[0], $l[1]); ?>

<?php           } ?>
<?php       } ?>
<?php       
    }
    
    function showMetas() {
?>
<?php       if ($this->htmlResponse->metas) { ?>
<?php           foreach ($this->htmlResponse->metas as $n => $m) { ?>
    <?php           if (is_array($m)) echo $this->htmlResponse->getMetaTag($n, $m[0], $m[1]); else echo $this->htmlResponse->getMetaTag($n, $m); ?>

<?php           } ?>
<?php       } ?>
<?php       
    }

    function showHeadTags() {
?>
<?php       if ($this->htmlResponse->headTags) { ?>
<?php           foreach ($this->htmlResponse->headTags as $t) { ?>
    <?php           echo $t; ?>

<?php           } ?>
<?php       } ?>
<?php       
    }
    
    function showHead() {
?><head>
    <!-- powered by Avancore 0.3 -->
<?php   if ($this->getTitle()) { ?>

    <title><?php echo $this->getTitle(); ?></title>
<?php } ?>
<?php   $this->showCssLibs(); ?>
<?php   $this->showJsLibs(); ?>
<?php   $this->showMetas(); ?>
<?php   $this->showHeadTags(); ?>
</head> 
<?php
    }
    
    function showBody() {
        
        $ba = Ae_Util::m($this->bodyAttribs, $this->htmlResponse->getData('bodyAttribs'));
        $ba = Ae_Util::m($ba, $this->htmlResponse->bodyAttribs);
                
?><body<?php if ($ba) echo " ".Ae_Util::mkAttribs($ba); ?>>
<?php echo $this->htmlResponse->replacePlaceholders(false, true); ?>

<?php 
    if($this->showTimeIndex && function_exists('xdebug_time_index')) var_dump(xdebug_time_index()); 
    if($this->showTimeIndex && function_exists('memory_get_usage')) {
        var_dump(memory_get_usage(), memory_get_peak_usage());
    }
    if($this->showQueries) $this->_showQueries();
?></body>
<?php        
    }
    
    function showDefault() {
        $nl = "";
        
        $htmlAttribs = Ae_Util::m($this->htmlAttribs, $this->htmlResponse->getData('htmlAttribs', array()));

        if ($this->addXmlTag) {
            if (!isset($htmlAttribs['xmlns'])) $htmlAttribs['xmlns'] = 'http://www.w3.org/1999/xhtml';
            echo $this->getXmlTag();
            $nl = "\n"; 
        }
        if (strlen($this->doctypeTag)) {
            echo $this->doctypeTag;
            $nl = "\n"; 
        }
        echo $nl;
?><html<?php if ($htmlAttribs) echo " ".Ae_Util::mkAttribs($htmlAttribs); ?>>
<?php $this->showHead(); ?>
<?php $this->showBody(); ?>

</html><?php        
    }
    
    function _showQueries() {
        $disp = & Ae_Dispatcher::getInstance();
        $db = & $disp->database;
        if (is_a($db, 'Ae_Legacy_Database_Native') && $db->trackQueries) {
            foreach ($db->queries as $q) echo '<hr /><pre>'.htmlspecialchars($q).'</pre>';
        }
    }
    
}

?>