<?php

/**
 * Important note: form template show... methods should accept reference to the rendered control as the first parameter
 */
class Ae_Form_Control_Template extends Ae_Template_Html {
    
    var $control = false;

    var $showDescriptions = true;

    function setVars($vars) {
        parent::setVars($vars);
        if (!$this->htmlResponse) {
            $this->htmlResponse = Ae_Legacy_Controller_Response_Global::r();
        }
    }

    /**
     * @param Ae_Form_Control $control
     */
    function _showCaption(& $control, $dontShowCaptionIfShownByControl = true, $substituteWhenNoCaption = "&nbsp;") {
        if ($dontShowCaptionIfShownByControl && $control->showsOwnCaption) echo $substituteWhenNoCaption;
        else {
            if ($this->showDescriptions && strlen($control->getDescription()) ) $this->_showCaptionWithDescription($control);
            else {
                echo $control->getCaption();
            }
        }
    }
    
    /**
     * @param Ae_Form_Control $control
     */
    function _showCaptionWithDescription(& $control) {
        $this->addJsLib('{AE}overlib_mini.js');
?><span class='captionWithDesc' onmouseover='overlib("<?php echo addcslashes(htmlspecialchars($control->getDescription()), "\n\r\'\""); ?>");' onmouseout="nd();"><?php ?>
<?php     echo $control->getCaption(); ?></span><?php ?>
<?php            
    }
    
    /**
     * @param Ae_Form_Control $control
     */
    function _getAttribs(& $control, $excludeAttribs = false) {
        $res = $control->getHtmlAttribs();
        if (is_array($excludeAttribs)) 
            foreach ($excludeAttribs as $k) {
                if (isset($res[$k])) unset($res[$k]);
            }
        return $res;    
    }

    /**
     * @param Ae_Form_Control $control
     */
    function _mapNames(& $control, $names, $asArrays = false) {
        $ctx = & $control->getContext();
        if (!is_array($names)) $res = $ctx->mapParam($names, $asArrays);
        else {
            $res = array();
            foreach ($names as $name) $res[] = $ctx->mapParam($names, $asArrays);
        }
        return $res;
    }
    
    function _showErrors($errors) {
        if ($errors) {
            if (is_array($errors)) $html = nl2br(htmlspecialchars(Ae_Util::implode_r("\n", $errors)));
            else $html = htmlspecialchars($errors);
?>
    <div class='errors'>
        <?php echo $html; ?>
    </div>
<?php 
        }
    }

    function _showCoolRequiredAsterisk(& $control) {
?>
    <span class='requiredAsterisk'>*</span>
<?php
    }
    
}

?>