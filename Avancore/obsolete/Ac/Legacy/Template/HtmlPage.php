<?php

class Ac_Legacy_Template_HtmlPage extends Ac_Legacy_Template_Html {

    const doctypeTransitional = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
    const doctypeStrict = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
    const doctypeHtml5 = '<!DOCTYPE html>';
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
<?php           echo "    ".$this->htmlResponse->getJsScriptTag($l[0], $l[1]); ?>

<?php           } ?>
<?php       } ?>
<?php       
    }
    
    function showCssLibs() {
?>
<?php       if ($this->htmlResponse->cssLibs) { ?>
<?php           foreach ($this->htmlResponse->cssLibs as $l) { ?>
<?php           echo "    ".$this->htmlResponse->getCssLibTag($l[0], $l[1]); ?>

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
    
    function showInsideHead($withMetas = true) {
?>
<?php   $this->showCssLibs(); ?>
<?php   $this->showJsLibs(); ?>
<?php   if ($withMetas) $this->showMetas(); ?>
<?php   $this->showHeadTags(); ?>
<?php
    }
    
    function showHead() {
?><head>
    <!-- powered by Avancore <?php echo Ac_Avancore::version ?> -->
<?php   if ($this->getTitle()) { ?>

    <title><?php echo $this->getTitle(); ?></title>
<?php   } ?>
<?php   $this->showInsideHead(); ?> 
</head> 
<?php
    }
    
    function showBody() {
        
        $ba = Ac_Util::m($this->bodyAttribs, $this->htmlResponse->getData('bodyAttribs'));
        $ba = Ac_Util::m($ba, $this->htmlResponse->bodyAttribs);
                
?><body<?php if ($ba) echo " ".Ac_Util::mkAttribs($ba); ?>>
<?php echo $this->htmlResponse->replacePlaceholders(false, true); ?>

<?php if ($this->htmlResponse->initScripts) $this->showInitScripts(); ?>
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
        
        $htmlAttribs = Ac_Util::m($this->htmlAttribs, $this->htmlResponse->getData('htmlAttribs', array()));

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
?><html<?php if ($htmlAttribs) echo Ac_Util::mkAttribs($htmlAttribs); ?>>
<?php $this->showHead(); ?>
<?php $this->showBody(); ?>
</html><?php        
    }
    
    function showInitScripts() {
        
        $scripts = array();
        
        foreach (Ac_Util::toArray($this->htmlResponse->initScripts) as $s) {
            if ($s instanceof Ac_Js_Script) $s = $s->toRawCode ();
            $scripts[] = rtrim($s, "; ");
        }
        
?>

<script type="text/javascript">
<?php   echo "    ".implode(";\n\n", $scripts).";\n"; ?>
</script>
<?php
    }
    
}

