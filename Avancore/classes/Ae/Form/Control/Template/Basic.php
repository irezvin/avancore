<?php

Ae_Dispatcher::loadClass('Ae_Form_Control_Template');

class Ae_Form_Control_Template_Basic extends Ae_Form_Control_Template {
    
    var $textAreaRows = 4;
    
    var $textAreaCols = 40;

    /**
     * @param Ae_Form_Control $control
     */
    function showSimpleList($control) {
?>

        <div <?php echo Ae_Util::mkAttribs($control->getHtmlAttribs()); ?> >
<?php       $this->utlShowSimpleControlsList($control->getOrderedDisplayChildren());     
?>
        </div>
<?php
    }

    /**
     * @param Ae_Form_Control $control
     */
    function showPlainList($control) {
        $this->utlShowSimpleControlsList($control->getOrderedDisplayChildren());     
    }
    
    /**
     * @param Ae_Form_Control $control
     */
    function showDivsList($control) {
?>

        <div <?php echo Ae_Util::mkAttribs($control->getHtmlAttribs()); ?> >
<?php       $this->utlShowDivsList($control->getOrderedDisplayChildren());     
?>
        </div>
<?php
    }
    
    function showTable($control) {
        $this->utlShowControlsTable($control->getOrderedDisplayChildren(), true, $control->getHtmlAttribs());
    }
    function showHTable($control) {
        $this->utlShowControlsTableHorizontal($control->getOrderedDisplayChildren(), true, $control->getHtmlAttribs());
    }
    
    function showTableWithoutOuterTag($control) {
        $this->utlShowControlsTable($control->getOrderedDisplayChildren(), false, $control->getHtmlAttribs());
    }
    
    /**
     * @param Ae_Form $form
     */
    function showForm (& $form, $html) {
        
        if (is_array($form->jsLibs)) foreach ($form->jsLibs as $jsLib) $this->addJsLib($jsLib);
        if (is_array($form->cssLibs)) foreach ($form->cssLibs as $cssLib) $this->addCssLib($cssLib);
        if (is_array($form->inlineStyles)) foreach ($form->inlineStyles as $inlineStyle) {
?>

        <style type="text/css">
<?php echo $inlineStyle; ?>

        </style> 

<?php
        }
        
        $ctx = & $form->getContext();
        if (!$ctx->isInForm) { 
            $attribs = array();
            $ownAttribs = $form->getHtmlAttribs();
            // FIXME $url = & $ctx->getUrl();
            if (!isset($ownAttribs['action'])) {
                $url = & $ctx->_baseUrl;
                $attribs['action'] = $url->toString($form->baseUrlToAction);
            }
            $attribs['method'] = $ctx->requestMethod;
            if (strlen($form->name)) $attribs['name'] = $ctx->mapParam($form->name);
            Ae_Util::ms($attribs, $ownAttribs);
        ?>
        
        <form <?php echo Ae_Util::mkAttribs($attribs); ?> >
<?php       if (!$form->baseUrlToAction && !isset($ownAttribs['action'])) echo $url->getHidden(); ?>
<?php   } 
?>

            <?php echo $html; ?>
<?php   if ($form->performOwnSubmissionCheck) {
            $attribs = array('type' => 'hidden', 'name' => $ctx->mapParam($form->getOwnSubmissionParamName()), 'value' => $form->getOwnSubmissionParamValue());
?>
    
            <input <?php echo Ae_Util::mkAttribs($attribs); ?> />
<?php } ?>
<?php if (!$ctx->isInForm) { ?>
        </form>
<?php }
    }
    
    function utlShowSimpleControlsList ($controls, $wrap = null) {
        foreach (array_keys($controls) as $k) { $cl = $controls[$k];
?>
            <?php echo $cl->fetchPresentation(false, $wrap); ?>
<?php
        }
    }
    
    function utlShowDivsList ($controls, $wrap = null) {
        foreach (array_keys($controls) as $k) { $cl = $controls[$k];
?>
            <?php echo "<div>". $cl->fetchPresentation(false, $wrap)."</div>"; ?>
<?php
        }
    }
    
    /**
     * @param Ae_Form_Control $control
     */
    function utlShowControlsTable ($controls, $withTableTag = true, $tableAttribs = array()) {
        if ($withTableTag) { 
?>

    <table <?php echo Ae_Util::mkAttribs($tableAttribs); ?>>
<?php
        }
        foreach (array_keys($controls) as $k) { $cl = $controls[$k];
?>
<?php       if ($cl->showWrapper) { ?>
            <?php echo $this->showTrWrapper($cl, $cl->fetchPresentation(false, false)); ?>
<?php       } else { ?>
            <?php echo $cl->fetchPresentation(false, false); ?>
<?php       } ?>                        
<?php } ?>
<?php if ($withTableTag) { ?>

    </table>
<?php }
    }
    
    /**
     * @param Ae_Form_Control $control
     */
    function utlShowControlsTableHorizontal ($controls, $withTableTag = true, $tableAttribs = array()) {
        if ($withTableTag) { 
?>

    <table <?php echo Ae_Util::mkAttribs($tableAttribs); ?>>
    <tr>
<?php
        }
        foreach (array_keys($controls) as $k) { $cl = $controls[$k];
?>
            <?php echo $this->showTdWrapper($cl, $cl->fetchPresentation(false, false)); ?>
<?php } ?>
<?php if ($withTableTag) { ?>
	</tr>
    </table>
<?php }
    }
    
    /**
     * @param Ae_Form_Control $control
     */
    function showDivWrapper ($control, $html) {
?>
    <div class='control<?php if($errors = $control->getErrors()) echo ' withErrors'; ?>'>
        <div class='caption'>
            <?php $this->_showCaption($control, true, ""); ?>
            <?php if ($control->isRequired()) $this->_showCoolRequiredAsterisk($control); ?>
        </div>
        <div class='control'>
            <?php echo $html; ?>
            <?php if ($errors) { ?>
            <div class='errors'>
                <?php $this->_showErrors($errors); ?>
            </div>
            <?php } ?>
        </div>
    </div>
<?php        
    }
    
    /**
     * @param Ae_Form_Control $control
     */
    function showSpanWrapper ($control, $html) {
?>
    <span class='control<?php if($errors = $control->getErrors()) echo ' withErrors'; ?>'>
        <span class='caption'>
            <?php $this->_showCaption($control, true, ""); ?>
            <?php if ($control->isRequired()) $this->_showCoolRequiredAsterisk($control); ?>
        </span>
        <span class='control'>
            <?php echo $html; ?>
            <?php if ($errors) { ?>
            <span class='errors'>
                <?php $this->_showErrors($errors); ?>
            </span>
            <?php } ?>
        </span>
    </span>
<?php        
    }
    
    /**
     * @param Ae_Form_Control $control
     */
    function showTrWrapper ($control, $html) {
?>

    <tr class='control<?php if($errors = $control->getErrors()) echo ' withErrors'; ?>'>
        <td class='caption'>
            <?php $this->_showCaption($control, true, "&nbsp;"); ?>
        </td>
        <td><?php if ($control->isRequired($control)) $this->_showCoolRequiredAsterisk($control); else echo "&nbsp;"; ?></td>
        <td class='control'>
<?php echo $html; ?>
<?php if ($errors) { ?>

            <div class='errors'>
                <?php $this->_showErrors($errors); ?>
            </div>
<?php } ?>
            
        </td>
    </tr>
<?php        
    }
    
    /**
     * @param Ae_Form_Control $control
     */
    function showTdWrapper ($control, $html) {
        $class = '';
        if ($errors = $control->getErrors()) $class .= ' withErrors';
?>

    <th class='caption <?php echo $class; ?>'>
        <?php $this->_showCaption($control, true, "&nbsp;"); ?>
        <?php if ($control->isRequired($control)) $this->_showCoolRequiredAsterisk($control); else echo "&nbsp;"; ?>
    </th>
    <td class='control <?echo $class; ?>'>
<?php echo $html; ?>
<?php if ($errors) { ?>

            <div class='errors'>
                <?php $this->_showErrors($errors); ?>
            </div>
<?php } ?>
            
	</td>
<?php        
    }
    
    /**
     * @param Ae_Form_Control_Text $control
     */
    function showTextInput($control) {
        if (!$control->isReadOnly()) {
            switch ($control->getType()) {
                case 'textArea': $this->_showTextArea($control); break;
                case 'rte':
                    if (class_exists('JFactory')) {
                        $editor = JFactory::getEditor();
                        $ha = array_merge(array(
                            'size' => 75,
                            'rows' => 20,
                            'width' => '100%',
                            'height' => 300,
                        ), $control->getHtmlAttribs());
                        echo $editor->display( 
                            $this->_mapNames($control, 'value'),  
                            $control->getValue(), 
                            $ha['width'], $ha['height'], 
                            $ha['size'], 
                            $ha['rows'],
                            true
                        );                    
                    }
                    break;
                case 'password':
                    $this->_showTextInput($control); 
                    break;
                case 'hidden':    
                    $this->_showTextInput($control); 
                    break;
                default: 
                    $this->_showTextInput($control); 
                    break;
            }
        } else { ?>
            <span class='readOnly' <?php if ($control->htmlAttribs) echo Ae_Util::mkAttribs($control->htmlAttribs); ?>>
                <?php if (strlen($control->getValue())) $this->d ($control->getOutputText(), $control->allowHtml); else $this->d($control->getEmptyCaption()); ?>
                
            </span>       
<?php   }
    }
    
    /**
     * @param Ae_Form_Control_Text $control
     */
    function _showTextArea($control) {
            $name = $this->_mapNames($control, 'value');
            
            $attribs = array('cols' => $this->textAreaCols, 'rows' => $this->textAreaRows);
            if (strlen($control->defaultSize)) $attribs['cols'] = $control->defaultSize;
            if (strlen($control->textAreaRows)) $attribs['rows'] = $control->textAreaRows;
            
            Ae_Util::ms($attribs, $this->_getAttribs($control));
            Ae_Util::ms($attribs, array(
                'name' => $name,
            ));
            
?>
            <textarea <?php echo Ae_Util::mkAttribs($attribs); ?>><?php echo Ae_Util::htmlspecialchars($control->getOutputText(), ENT_QUOTES, $this->charset, $control->doubleEncodeInInput); ?></textarea>
<?php
   }
    
    /**
     * @param Ae_Form_Control_Text $control
     */
    function _showTextInput($control) {
            $name = $this->_mapNames($control, 'value');
            if ($control->getType() === 'password') $type = 'password';
            elseif ($control->getType() === 'hidden') $type = 'hidden';
            else $type = 'text';
            $attribs = Ae_Util::m($this->_getAttribs($control), array(
                'type' => $type,
                'name' => $name,
                'maxlength' => $control->getMaxLength(), 
                'value' => $control->getOutputText(),
            ));
?>
            <input <?php echo Ae_Util::mkAttribs($attribs, '"', ENT_QUOTES, $this->charset, $control->doubleEncodeInInput); ?> />
<?php
   }
    
    /**
     * @param Ae_Form_Control_Static $control
     */
    function showStatic($control) {
            $name = $this->_mapNames($control, 'value');
            $attribs = $control->getHtmlAttribs();
            if (!isset($attribs['class'])) $attribs['class'] = 'readOnly';
            
?>            
            <<?php echo $control->tagName; ?> <?php echo Ae_Util::mkAttribs($attribs); ?>>
                <?php if (strlen($control->getValue())) $this->d ($control->getValue(), $control->allowHtml); else $this->d($control->getEmptyCaption()); ?>
            </ <?php echo $control->tagName; ?>>       
<?php   
    }
    
    /**
     * @param Ae_Form_Control_Button $control
     */
    function showButton($control) {
        $name = $this->_mapNames($control, 'value');
        $attribs = Ae_Util::m($this->_getAttribs($control), array(
            'type' => $control->buttonType,
            'name' => $name,
            'value' => $control->getButtonCaption(),
        ));
?>
        <input <?php echo Ae_Util::mkAttribs($attribs); ?> />
<?php
    }
    
    /**
     * @param Ae_Form_Control_List $control
     */
    function showSelectList($control) {
        /*
        ?>
            <div style="border: 1px solid red"><ul>
                <li><?php echo implode("</li><li>", $control->getValueList()); ?></li>
            </ul></div>
        <?php
        */
        $items = array();
        
        if (/*!$control->getMultiSelect() &&*/ !$control->isReadOnly() && (($cap = $control->getDummyCaption()) !== false)) {
            $items[] = array('value' => (string) $control->getDummyValue(), 'caption' => $cap, 'selected' => $control->isItemSelected(''));
        }
        
        foreach ($control->getValueList() as $val => $cap) {
            $items[] = array('value' => $val, 'caption' => $cap, 'selected' => $control->isItemSelected($val));
        }
        
        if ($control->isReadOnly()) $this->_showReadOnlyList($control, $items);
        elseif ($control->type == 'selectList') $this->_showSelectListElement($control, $items);
            else $this->_showSelectListControls($control, $items);
            
    }
    
    /**
     * @param Ae_Form_Control_List $control
     */
    function _showSelectListElement($control, $listItems) {
        $elementAttribs = $control->getHtmlAttribs();
        
        $elementAttribs['name'] = $this->_mapNames($control, 'value');
        if ($control->getMultiSelect()) {
            $elementAttribs['multiple'] = true;
            $elementAttribs['name'] .= '[]';
        }
?>
        
        <select <?php $this->attribs($elementAttribs); ?>>
<?php foreach ($listItems as $item) { ?>
            <option value="<?php $this->d($item['value']); ?>"<?php if($item['selected']) { ?> selected="selected"<?php } ?>><?php 
                if (is_array($item['caption'])) $item['caption'] = implode(', ', $item['caption']);
                if (strlen($item['caption'])) $this->d($item['caption']); else echo "&nbsp;"; ?></option>
<?php } ?>
        </select>
<?php
    }
    
    /**
     * @param Ae_Form_Control_List $control
     */
    function _showReadOnlyList($control, $listItems) {
        $captions = array();
        foreach($listItems as $item) {
            if ($item['selected']) $captions[] = htmlspecialchars($item['caption'], null, $this->charset);
        }
?>
        
        <span class='readOnly' <?php if ($control->htmlAttribs) echo Ae_Util::mkAttribs($control->htmlAttribs); ?>>
            <?php if (count($captions)) $this->d (implode($control->listSeparator, $captions)); else $this->d($control->getEmptyCaption()); ?>
        </span>       
<?php        
    }
    
    /**
     * @param Ae_Form_Control_List $control
     */
    function _showSelectListControls($control, $listItems) {
        echo "<h1 style='color: red'>".className($this).'::'.__FUNCTION__.' is not implemented yet</h1>';
    }
    
    /**
     * @param Ae_Form_Control_Toggle $control
     */
    function showToggle($control) {
        if ($control->type == 'checkbox') {
            $this->showCheckbox($control);
        } else {
            $this->showSelectToggle($control);
        }
    }
     
    /**
     * @param Ae_Form_Control_Toggle $control
     */
    function _showSelectToggle($control) {
        echo "<h1 style='color: red'>".className($this).'::'.__FUNCTION__.' is not implemented yet</h1>';
    }
    
    /**
     * @param Ae_Form_Control_Toggle $control
     */
    function showCheckbox($control) {
        if (!$control->isReadOnly()) {
            $attribs = $control->getHtmlAttribs();
            $attribs['name'] = $this->_mapNames($control, 'value');
            $attribs['value'] = 1;
            $attribs['type'] = 'checkbox';
            if ($control->getValue()) $attribs['checked'] = true;
?>
            <input <?php $this->attribs($attribs); ?> /><?php
        } else {
?>
            <span class='readOnly'>
                <?php if (strlen($control->getValue())) $this->d ($control->getValue()); else $this->d($control->getEmptyCaption()); ?>
            </span>
<?php       
        }
    }

    
    /**
     * @param Ae_Form_Control_Group $group
     */
    function showControlGroup ($group) {
        if (strlen($group->preHtml)) echo $group->preHtml;
        if ($group->groupTemplatePart) $this->show($group->groupTemplatePart, array($group));
        else {
            if ($group->style == 'table') $this->showTable($group);
            elseif ($group->style == 'horizontal') $this->showHTable($group);
                else $this->showSimpleList($group);
        }
        if (strlen($group->postHtml)) echo $group->postHtml;
    }

    /**
     * @param Ae_Form_Control_Parameters $params
     */    
    function showParameters($params) {
        $mp = & $params->getMosParameters();
        $ctx = & $params->getContext();
        echo $mp->render($ctx->mapParam('value')); 
    }
    
    /**
     * @param Ae_Form_Control_Date $date
     */
    function showDate($date) {
        $context = & $date->getContext();
        $id = $date->getId();    
        $name = $this->_mapNames($date, 'value');
            $attribs = Ae_Util::m($this->_getAttribs($date), array(
                'type' => 'text',
                'name' => $name,
                'value' => $date->getDisplayValue(),
                'id' => $id,
            ));
            
            if ($date->isReadOnly()) $attribs['readOnly'] = true;
?>
            <input <?php echo Ae_Util::mkAttribs($attribs); ?> />
<?php
        if ($date->showCalendar && !$date->isReadOnly()) {
            $this->addJsLib('{AE}calendar/calendar_stripped.js');
            $this->addJsLib('{AE}calendar/calendar-setup_stripped.js');
            $this->addJsLib('{AE}calendar/lang/calendar-'.(defined('AE_LANG_ID')? AE_LANG_ID : 'en').'.js');
            if ($date->calendarSkin) {
                $this->addCssLib('{AECSS}js/calendar/calendar-'.$date->calendarSkin.'.css');
            }
            $json = array(
                'inputField' => $id,
                'showsTime' => false,
            );
            if ($df = $date->getCalendarDateFormat()) $json['ifFormat'] = $df;
            Ae_Util::ms($json, $date->getExtraJson());
            $helper = & $this->getHtmlHelper();
?>
            <script type='text/javascript'>
                Calendar.setup(<?php echo $helper->toJson($json, 16); ?>);
            </script>            
<?php
        }
    }
    
    function showUpload(Ae_Form_Control_Upload $upload) {
        
        $name = $upload->getFileParamName();
        $attribs = Ae_Util::m($this->_getAttribs($upload), array(
            'type' => 'file',
            'name' => $name,
        ));
?>        
        <input <?php echo Ae_Util::mkAttribs($attribs); ?> />
<?php
        
    }
    
}

?>
