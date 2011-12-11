<?php

Ae_Dispatcher::loadClass('Ae_Form_Control_Template');

class Ae_Form_Control_Template_Autocomplete extends Ae_Form_Control_Template {
    
    var $textAreaRows = 4;
    
    var $textAreaCols = 40;
    
    /**
     * @param Ae_Form_Control_Text $control
     */
    function showTextInput(& $control) {
        if (!$control->isReadOnly()) {
            switch ($control->getType()) {
                case 'textArea': $this->_showTextArea($control); break;
                default: $this->_showTextInput($control); break;
            }
            $this->_showAutocompleterScript($control);
        } else { ?>
            <span class='readOnly'>
                <?php if (strlen($control->getValue())) $this->d ($control->getOutputText(), $control->allowHtml); else $this->d($control->getEmptyCaption()); ?>
                
            </span>       
<?php   }
    }
    
    /**
     * @param Ae_Form_Control_Text_Autocomplete $control
     */
    function _showTextArea(& $control) {
            $name = $this->_mapNames($control, 'value');
            
            $attribs = array('cols' => $this->textAreaCols, 'rows' => $this->textAreaRows);
            if (strlen($control->defaultSize)) $attribs['cols'] = $control->defaultSize;
            if (strlen($control->textAreaRows)) $attribs['rows'] = $control->textAreaRows;
            
            Ae_Util::ms($attribs, $this->_getAttribs($control));
            Ae_Util::ms($attribs, array(
                'name' => $name,
                'id' => $control->getInputId(),
                'autocomplete' => 'off',
            ));
            
?>
            <textarea <?php echo Ae_Util::mkAttribs($attribs); ?>><?php $this->d ($control->getOutputText()); ?></textarea>
<?php
   }
    
    /**
     * @param Ae_Form_Control_Text_Autocomplete $control
     */
    function _showTextInput(& $control) {
            $name = $this->_mapNames($control, 'value');
            $attribs = Ae_Util::m($this->_getAttribs($control), array(
                'type' => 'text',
                'name' => $name,
                'maxlength' => $control->getMaxLength(), 
                'value' => $control->getOutputText(),
                'id' => $control->getInputId(),
                'autocomplete' => 'off',
            ));
?>
            <input <?php echo Ae_Util::mkAttribs($attribs); ?> />
<?php
   }
   
    /**
     * @param Ae_Form_Control_Text_Autocomplete $control
     */
    function _showAutocompleterScript(& $control) {
        if ($control->loadScripts) {
            $this->addJsLib('{AE}prototype.js', false);
            $this->addJsLib('{AE}scriptaculous/scriptaculous.js', false);
        }
        
        $hh = & $this->getHtmlHelper();
        $inputId = $hh->jsQuote($control->getInputId());
        $listId = $hh->jsQuote($control->getAutocompleteListId());
        $acParams = $hh->toJson($control->getAutocompleteListJson(), 0, 4, false, false);
        $listAttribs = $control->listElementAttribs;
        $listAttribs['id'] = $control->getAutocompleteListId();
        if (strlen($u = $control->getUrl())) {
            $constructor = 'Ajax.Autocompleter';
            $dataSource = $hh->jsQuote($u);  
        } else {
            $constructor = 'Autocompleter.Local';
            $dataSource = $hh->toJson(Ae_Util::array_values($control->getValueList()), 0, 4, false, false);
        }
?>
            
            <<?php echo $control->listElementTagName.' '; $this->attribs($listAttribs); ?>></<?php echo $control->listElementTagName; ?>>
            <script type="text/javascript" language="javascript">
            // <![CDATA[
              new <?php echo $constructor; ?> (<?php echo $inputId ?>, <?php echo $listId ?>,
              <?php echo $dataSource; ?>, <?php echo $acParams; ?> );
            // ]]>
            </script>
            
<?php       
    }
    
}

?>