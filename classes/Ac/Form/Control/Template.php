<?php

/**
 * Important note: form template show... methods should accept reference to the rendered control as the first parameter
 */
class Ac_Form_Control_Template extends Ac_Template_Html {
    
    var $control = false;

    var $showDescriptions = true;

    function setVars($vars) {
        parent::setVars($vars);
        if (!$this->htmlResponse) {
            $this->htmlResponse = Ac_Controller_Response_Global::r();
        }
    }

    /**
     * @param Ac_Form_Control $control
     */
    function _showCaption($control, $dontShowCaptionIfShownByControl = true, $substituteWhenNoCaption = "&nbsp;") {
        if ($dontShowCaptionIfShownByControl && $control->showsOwnCaption) echo $substituteWhenNoCaption;
        else {
            if ($this->showDescriptions && strlen($control->getDescription()) ) $this->_showCaptionWithDescription($control);
            else {
                echo $control->getCaption();
            }
        }
    }
    
    /**
     * @param Ac_Form_Control $control
     */
    function _showCaptionWithDescription(& $control) {
        $this->addJsLib('{AC}/vendor/overlib_mini.js');
?><span class='captionWithDesc' onmouseover='overlib("<?php echo addcslashes(htmlspecialchars($control->getDescription()), "\n\r\'\""); ?>");' onmouseout="nd();"><?php ?>
<?php     echo $control->getCaption(); ?></span><?php ?>
<?php            
    }
    
    /**
     * @param Ac_Form_Control $control
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
     * @param Ac_Form_Control $control
     */
    function _mapNames(& $control, $names, $asArrays = false) {
        $ctx = $control->getContext();
        if (!is_array($names)) $res = $ctx->mapParam($names, $asArrays);
        else {
            $res = array();
            foreach ($names as $name) $res[] = $ctx->mapParam($names, $asArrays);
        }
        return $res;
    }
    
    function _showErrors($errors) {
        if ($errors) {
            if (is_array($errors)) $html = nl2br(htmlspecialchars(Ac_Util::implode_r("\n", $errors)));
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

