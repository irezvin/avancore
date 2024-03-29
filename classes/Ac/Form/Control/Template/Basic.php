<?php

class Ac_Form_Control_Template_Basic extends Ac_Form_Control_Template {
    
    var $textAreaRows = 4;
    
    var $textAreaCols = 40;

    /**
     * @param Ac_Form_Control $control
     */
    function showSimpleList($control) {
?>

        <div <?php echo Ac_Util::mkAttribs($control->getHtmlAttribs()); ?> >
<?php       $this->utlShowSimpleControlsList($control->getOrderedDisplayChildren());     
?>
        </div>
<?php
    }

    /**
     * @param Ac_Form_Control $control
     */
    function showPlainList($control) {
        $this->utlShowSimpleControlsList($control->getOrderedDisplayChildren());
    }
    
    /**
     * @param Ac_Form_Control $control
     */
    function showDivsList($control, $wrap = null) {
?>

        <div <?php echo Ac_Util::mkAttribs($control->getHtmlAttribs()); ?> >
<?php       $this->utlShowDivsList($control->getOrderedDisplayChildren(), $wrap);     
?>
        </div>
<?php
    }
    
    function showTable($control, $extraParams = false) {
        $this->utlShowControlsTable($control->getOrderedDisplayChildren(), true, $control->getHtmlAttribs(), $extraParams);
    }
    
    function showHTable($control) {
        $this->utlShowControlsTableHorizontal($control->getOrderedDisplayChildren(), true, $control->getHtmlAttribs());
    }

    function showTableRow($control) {
        $trAttribs = $control->getHtmlAttribs();
?>
        <tr <?php echo Ac_Util::mkAttribs($control->getHtmlAttribs()); ?>>
<?php       $this->utlShowControlsTableHorizontal($control->getOrderedDisplayChildren(), false); ?>
        </tr>
<?php 
    }
    
    function showTableWithoutTags($control) {
        $tableAttribs = $control->getHtmlAttribs();
?>
        <table <?php echo Ac_Util::mkAttribs($control->getHtmlAttribs()); ?>>
<?php       $this->utlShowSimpleControlsList($control->getOrderedDisplayChildren()); ?>
        </table>
<?php
    }
    
    
    function showTableWithoutOuterTag($control, $extraParams = false) {
        $this->utlShowControlsTable($control->getOrderedDisplayChildren(), false, $control->getHtmlAttribs(), $extraParams);
    }
    
    /**
     * @param Ac_Form $form
     */
    function showForm ($form, $html) {
        if (is_array($form->jsLibs)) foreach ($form->jsLibs as $jsLib) $this->addJsLib($jsLib);
        if (is_array($form->cssLibs)) foreach ($form->cssLibs as $cssLib) $this->addCssLib($cssLib);
        if (is_array($form->inlineStyles)) foreach ($form->inlineStyles as $inlineStyle) {
?>

        <style type="text/css">
<?php echo $inlineStyle; ?>

        </style> 

<?php
        }
        
        $ctx = $form->getContext();
        if (!$ctx->isInForm) { 
            $attribs = $form->getFormTagAttribs(false);
            $hasOwnAction = isset($attribs['action']);
            if (!$hasOwnAction) {
                $actionUrl = $form->getActionUrl();
                $attribs['action'] = ''.$actionUrl;
            }
            else $actionUrl = null;
        ?>
        
        <form <?php echo Ac_Util::mkAttribs($attribs); ?> >
<?php       if (!$form->baseUrlToAction && $actionUrl) echo $actionUrl->getHidden(); ?>
<?php   } elseif ($form->alwaysPutBaseUrlParams) {
            $bu = $ctx->getBaseUrl();
            if ($bu) echo $bu->getHidden ();
        }
?>

            <?php echo $html; ?>
<?php   if ($form->performOwnSubmissionCheck) {
            $attribs = array('type' => 'hidden', 'name' => $ctx->mapParam($form->getOwnSubmissionParamName()), 'value' => $form->getOwnSubmissionParamValue());
?>
    
            <input <?php echo Ac_Util::mkAttribs($attribs); ?> />
<?php } ?>
<?php if (!$ctx->isInForm) { ?>
        </form>
<?php }
    }
    
    function utlShowSimpleControlsList ($controls, $wrap = null) {
        foreach (array_keys($controls) as $k) { $cl = $controls[$k];
?>
            <?php echo $cl->fetchPresentation(true, $wrap); ?>
<?php
        }
    }
    
    function utlShowDivsList ($controls, $wrap = null) {
        foreach (array_keys($controls) as $k) { $cl = $controls[$k];
?>
            <?php echo "<div>". $cl->fetchPresentation(true, $wrap)."</div>"; ?>
<?php
        }
    }
    
    /**
     * @param Ac_Form_Control $control
     */
    function utlShowControlsTable ($controls, $withTableTag = true, $tableAttribs = array(), $extraParams = false) {
        if (is_array($extraParams) && isset($extraParams['defaultTrWrapper'])) {
            $trWrapperMethod = 'show'.$extraParams['defaultTrWrapper'];
        } else {
            $trWrapperMethod = 'showTrWrapper';
        }
        if ($withTableTag) { 
?>

    <table <?php echo Ac_Util::mkAttribs($tableAttribs); ?>>
<?php
        }
        foreach (array_keys($controls) as $k) { 
            $cl = $controls[$k];
            if ($cl->showWrapper) {
                if (strlen($cl->wrapperTemplatePart)) {
                    echo $cl->fetchWithWrapper();
                } else {
                    if (!is_array($trWrapperMethod)) {
                        $this->$trWrapperMethod($cl, $cl->fetchPresentation(true, false), $cl->wrapperTemplateParam);
                    } else {
                        call_user_func($trWrapperMethod, $cl, $cl->fetchPresentation(true, false), $cl->wrapperTemplateParam);
                    }
                }
            } else {
                echo $cl->fetchPresentation(true, false);
            }
        }
?>        
<?php if ($withTableTag) { ?>
    </table>
<?php }
    }
    
    /**
     * @param Ac_Form_Control $control
     */
    function utlShowControlsTableHorizontal ($controls, $withTableTag = true, $tableAttribs = array()) {
        if ($withTableTag) { 
?>

    <table <?php echo Ac_Util::mkAttribs($tableAttribs); ?>>
    <tr>
<?php
        }
        foreach (array_keys($controls) as $k) { $cl = $controls[$k];
?>
            <?php echo $this->showTdWrapper($cl, $cl->fetchPresentation(true, false)); ?>
<?php } ?>
<?php if ($withTableTag) { ?>
	</tr>
    </table>
<?php }
    }
    
    /**
     * @param Ac_Form_Control $control
     */
    function showDivWrapper ($control, $html) {
?>
    <div class='wrapper control<?php if($errors = $control->getErrors()) echo ' withErrors'; ?>'>
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
     * @param Ac_Form_Control $control
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
     * @param Ac_Form_Control $control
     */
    function showErrorSpanWrapper ($control, $html) {
?>
    <span class='control<?php if($errors = $control->getErrors()) echo ' withErrors'; ?>'>
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
     * @param Ac_Form_Control $control
     */
    function showTrWrapper ($control, $html, $extraParams = array()) {
        if (is_array($extraParams) && isset($extraParams['class'])) {
            $extraClasses = ' '.$extraParams['class'];
        } else {
            $extraClasses = '';
        }
?>

    <tr class='control<?php if($errors = $control->getErrors()) echo ' withErrors'; ?><?php echo $extraClasses; ?>'>
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
    
    function showTrWrapperColspan ($control, $html, $extraParams = array()) {
        if (is_array($extraParams) && isset($extraParams['class'])) {
            $extraClasses = ' '.$extraParams['class'];
        } else {
            $extraClasses = '';
        }
?>

    <tr class='control<?php if($errors = $control->getErrors()) echo ' withErrors'; ?><?php echo $extraClasses; ?>'>
        <td class='control spanned' colspan = "3">
<?php       if (strlen($control->getCaption())) { ?>
                <div class="caption"><?php $this->_showCaption($control, true, "&nbsp;"); ?></div>
<?php       } ?>
<?php       if ($control->isRequired($control)) { ?>
                <?php $this->_showCoolRequiredAsterisk($control); ?>
<?php       } ?>
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
     * @param Ac_Form_Control $control
     */
    function showTrWrapperSeparateErrors ($control, $html, $extraParams) {
        if (is_array($extraParams) && isset($extraParams['class'])) {
            $extraClasses = ' '.$extraParams['class'];
        } else {
            $extraClasses = '';
        }
?>

    <tr class='control<?php if($errors = $control->getErrors()) echo ' withErrors separateErrors'; ?><?php echo $extraClasses; ?>'>
        <td class='caption'>
            <?php $this->_showCaption($control, true, "&nbsp;"); ?>
        </td>
        <td><?php if ($control->isRequired($control)) $this->_showCoolRequiredAsterisk($control); else echo "&nbsp;"; ?></td>
        <td class='control'>
<?php echo $html; ?>
        </td>
    </tr>
<?php if ($errors) { ?>

    <tr class='errors separateErrors'>
        <td class='dummy'>&nbsp;</td>
        <td></td>
        <td class='control errors'>
            <?php $this->_showErrors($errors); ?>
        </td>
    </tr>
<?php } ?>
    
    
<?php        
    }
       
    /**
     * @param Ac_Form_Control $control
     */
    function showTrColspanWrapper ($control, $html, $extraParams = false) {
        if (is_array($extraParams) && isset($extraParams['class'])) {
            $extraClasses = ' '.$extraParams['class'];
        } else {
            $extraClasses = '';
        }
?>

    <tr class='control<?php if($errors = $control->getErrors()) echo ' withErrors'; ?><?php echo $extraClasses; ?>'>
        <td class='spanned' colspan='3'>
            <?php $this->showSpanWrapper($control, $html); ?>
        </td>
    </tr>
<?php        
    }
       
    /**
     * @param Ac_Form_Control $control
     */
    function showTrRightCaptionWrapper ($control, $html, $extraParams = false) {
        
        if (is_array($extraParams) && isset($extraParams['class'])) {
            $extraClasses = ' '.$extraParams['class'];
        } else {
            $extraClasses = '';
        }
        
        $leftText = '&nbsp;';
        $separateErrors = false;
        
        if (is_array($extraParams)) {
            if (isset($extraParams['separateErrors'])) {
                $separateErrors = true;
            }
            if (isset($extraParams['class'])) {
                $extraClasses = ' '.$extraParams['class'];
            }
            if (isset($extraParams['leftText'])) {
                $leftText = $extraParams['leftText'];
            }
        } else {
            $extraClasses = '';
        }
?>

    <tr class='control<?php if($errors = $control->getErrors()) echo ' withErrors'; if ($separateErrors) echo ' separateErrors' ?><?php echo $extraClasses; ?>'>
        <td class='dummy'><?php echo $leftText; ?></td>
        <td><?php if ($control->isRequired($control)) $this->_showCoolRequiredAsterisk($control); else echo "&nbsp;"; ?></td>
        <td class='control'>
<?php echo $html; ?>
<?php   if (strlen($control->getCaption() && !$control->showsOwnCaption)) { ?>
            <span class='caption'>
<?php       $this->_showCaption($control, true, ""); ?>
            </span>
<?php   } ?>
    <?php if (!$separateErrors && $errors) { ?>

            <div class='errors'>
                <?php $this->_showErrors($errors); ?>
            </div>
<?php } ?>
            
        </td>
    </tr>
<?php if ($separateErrors && $errors) { ?>

    <tr class='errors separateErrors'>
        <td class='dummy'>&nbsp;</td>
        <td></td>
        <td class='control errors'>
            <?php $this->_showErrors($errors); ?>
        </td>
    </tr>
<?php } ?>    
<?php        
    }
    
    /**
     * @param Ac_Form_Control $control
     */
    function showTdWrapper ($control, $html) {
        $class = '';
        if ($errors = $control->getErrors()) $class .= ' withErrors';
?>

    <th class='caption <?php echo $class; ?>'>
        <?php $this->_showCaption($control, true, "&nbsp;"); ?>
        <?php if ($control->isRequired($control)) $this->_showCoolRequiredAsterisk($control); else echo "&nbsp;"; ?>
    </th>
    <td class='control <?php echo $class; ?>'>
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
     * @param Ac_Form_Control_Text $control
     */
    function showTextInput($control) {
        if (!$control->isReadOnly()) {
            switch ($control->getType()) {
                case 'textArea': $this->_showTextArea($control); break;
                case 'rte':
                    if (class_exists('JFactory', false)) {
                        $editor = JFactory::getEditor();
                        $ha = array_merge(array(
                            'size' => 75,
                            'rows' => 20,
                            'width' => '100%',
                            'height' => 300,
                        ), $control->getHtmlAttribs());
                        echo $editor->display( 
                            $this->_mapNames($control, 'value'),  
                            $control->getDisplayValue(), 
                            $ha['width'], $ha['height'], 
                            $ha['size'], 
                            $ha['rows'],
                            true
                        );                    
                    } else {
                        $a = $control->getRteAdapter();
                        echo $a->getHtmlForEditor($control, $id, null);
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
            <span class='readOnly' <?php if ($control->htmlAttribs) echo Ac_Util::mkAttribs($control->htmlAttribs); ?>>
                <?php if (strlen(''.$control->getDisplayValue())) $this->d($control->getOutputText(), $control->allowHtml); else $this->d($control->getEmptyCaption()); ?>
                
            </span>       
<?php   }
    }
    
    /**
     * @param Ac_Form_Control_Text $control
     */
    function _showTextArea($control, & $id = null) {
            $name = $this->_mapNames($control, 'value');
            
            $attribs = array('cols' => $this->textAreaCols, 'rows' => $this->textAreaRows);
            if (strlen($control->defaultSize)) $attribs['cols'] = $control->defaultSize;
            if (strlen($control->textAreaRows)) $attribs['rows'] = $control->textAreaRows;
            
            Ac_Util::ms($attribs, $this->_getAttribs($control));
            Ac_Util::ms($attribs, array(
                'name' => $name,
            ));
            
            if (!isset($attribs['id']) || $attribs['id'] === false) {
                $id = $control->getContext()->mapIdentifier('value');
                $attribs['id'] = $id;
            } else {
                $id = $attribs['id'];
            }
?>
            <textarea <?php echo Ac_Util::mkAttribs($attribs); ?>><?php echo Ac_Util::htmlspecialchars($control->getOutputText(), ENT_QUOTES, $this->charset, $control->doubleEncodeInInput); ?></textarea>
<?php
   }
    
    /**
     * @param Ac_Form_Control_Text $control
     */
    function _showTextInput($control) {
            $name = $this->_mapNames($control, 'value');
            if ($control->getType() === 'password') $type = 'password';
            elseif ($control->getType() === 'hidden') $type = 'hidden';
            else $type = 'text';
            $attribs = Ac_Util::m(array(
                'type' => $type,
                'name' => $name,
                'maxlength' => $control->getMaxLength(), 
                'value' => $control->getOutputText(),
            ), $this->_getAttribs($control));
?>
            <input <?php echo Ac_Util::mkAttribs($attribs, '"', ENT_QUOTES, "UTF-8", $control->doubleEncodeInInput); ?> />
<?php
   }
    
    /**
     * @param Ac_Form_Control_Static $control
     */
    function showStatic($control) {
            $name = $this->_mapNames($control, 'value');
            $attribs = $control->getHtmlAttribs();
            if (!isset($attribs['class'])) $attribs['class'] = 'readOnly';
            $disp = $control->getDisplayValue();
?>            
            <<?php echo $control->tagName; ?> <?php echo Ac_Util::mkAttribs($attribs); ?>><?php if (strlen($disp)) $this->d ($disp, $control->allowHtml); else $this->d($control->getEmptyCaption()); ?></<?php echo $control->tagName; ?>>
<?php   
    }
    
    /**
     * @param Ac_Form_Control_Button $control
     */
    function showButton($control) {
        $name = $this->_mapNames($control, 'value');
        if ($control->buttonType === 'button') {
            $attribs = Ac_Util::m($this->_getAttribs($control), array(
                'name' => $name,
            ));
            echo Ac_Util::mkElement('button', $control->getButtonCaption(), $attribs);
        } elseif ($control->buttonType === 'link') {
            $attribs = Ac_Util::m($this->_getAttribs($control), array(
                'name' => $name,
            ));
            echo Ac_Util::mkElement('a', $control->getButtonCaption(), $attribs);
        } else {
            $name = $this->_mapNames($control, 'value');
            $attribs = Ac_Util::m($this->_getAttribs($control), array(
                'type' => $control->buttonType,
                'name' => $name,
                'value' => $control->getButtonCaption(),
            ));
?>
        <input <?php echo Ac_Util::mkAttribs($attribs); ?> />
<?php
        }
    }
    
    /**
     * @param Ac_Form_Control_List $control
     */
    function showSelectList($control) {
        $items = array();
        
        if ($control->type == 'chosen') {
            $control->autoId = true;
            $controlId = $control->getId();
        }
        
        if (!$control->isReadOnly() && (($cap = $control->getDummyCaption()) !== false)) {
            $items[] = array('value' => (string) $control->getDummyValue(), 'caption' => $cap, 'selected' => $control->isItemSelected(''));
        }
        
        foreach ($control->getValueList() as $val => $cap) {
            $items[] = array('value' => $val, 'caption' => $cap, 'selected' => $control->isItemSelected($val));
        } 
       
        if ($control->isReadOnly()) $this->_showReadOnlyList($control, $items);
        elseif ($control->type == 'selectList' || $control->type == 'chosen') $this->_showSelectListElement($control, $items);
            else $this->_showSelectListControls($control, $items);
            
        if ($control->type == 'chosen') {
            $r = Ac_Controller_Response_Global::r();
            if (!isset($control->tplExtras['noJquery']) || !$control->tplExtras['noJquery']) {
                $r->addAssetLibs(array(
                    '{JQUERY}',
                ));
            }
            $r->addAssetLibs(array(
                '{AC}/vendor/chosen/chosen.min.css',
                '{AC}/vendor/chosen/chosen.jquery.min.js',
            ));
            $options = new Ac_Js_Val(Ac_Util::m(array(
                'disable_search_threshold' => 10,
                'allow_single_select' => true
            ), $control->chosenOptions));
            echo new Ac_Js_Script("
                jQuery(document).ready(function() {
                    var jq =  jQuery(".new Ac_Js_Val('#'.$controlId).");
                    if (jq.chosen) jq.chosen({$options});
                    else console.warn('jQuery(...).chosen not found');
                });
            ");
        }
    }
    
    /**
     * @param Ac_Form_Control_List $control
     */
    function _showSelectListElement($control, $listItems) {
        $elementAttribs = $control->getHtmlAttribs();
        
        $elementAttribs['name'] = $this->_mapNames($control, 'value');
        if ($control->getMultiSelect()) {
            $elementAttribs['multiple'] = true;
            $elementAttribs['name'] .= '[]';
        }
        if ($control->autoSize && count($listItems)) $elementAttribs['size'] = count($listItems);
?>
        
        <select <?php $this->attribs($elementAttribs); ?>>
<?php foreach ($listItems as $item) { ?>
            <option value="<?php $this->d($item['value'], $control->isHtmlAllowed()); ?>"<?php if($item['selected']) { ?> selected="selected"<?php } ?>><?php 
                if (is_array($item['caption'])) $item['caption'] = implode(', ', $item['caption']);
                if (strlen($item['caption'])) $this->d($item['caption'], $control->isHtmlAllowed()); else echo "&nbsp;"; ?></option>
<?php } ?>
        </select>
<?php
    }
    
    /**
     * @param Ac_Form_Control_List $control
     */
    function _showReadOnlyList($control, $listItems) {
        $captions = array();
        foreach($listItems as $item) {
            if ($item['selected']) $captions[] = $control->isHtmlAllowed()? $item['caption'] : htmlspecialchars($item['caption'], ENT_QUOTES, $this->charset);
        }
?>
        
        <span class='readOnly' <?php if ($control->htmlAttribs) echo Ac_Util::mkAttribs($control->htmlAttribs); ?>>
            <?php if (count($captions)) $this->d (implode($control->listSeparator, $captions)); else $this->d($control->getEmptyCaption()); ?>
        </span>       
<?php        
    }
    
    /**
     * @param Ac_Form_Control_List $control
     */
    function _showSelectListControls($control, $listItems) {
        $elementAttribs = $control->getHtmlAttribs();
        $name = $this->_mapNames($control, 'value');
        if (!isset($elementAttribs['class']) ) $elementAttribs['class'] = '';
        $elementAttribs['class'] .= ' checkList';
        $this->htmlResponse->addAssetLibs(array(
            '{FASI}/fasiFrontend.css',
        ));
        if ($control->getMultiSelect()) {
            $type = "checkbox";
            $name .= '[]';
            $elementAttribs['class'] .= ' multiple';
        } else {
            if ($control->dummyCaption !== false) {
                array_shift($listItems);
            }
            $type = "radio";
        }
        $i = 0;
?>
        
        <div <?php $this->attribs($elementAttribs); ?>>
<?php foreach ($listItems as $item) { $i++; ?>
            <div>
                <input name="<?php $this->d($name); ?>" type="<?php echo $type; ?>" id="<?php echo $id = $control->getContext()->mapIdentifier('_item_'.$i); ?>" value="<?php $this->d($item['value'], $control->isHtmlAllowed()); ?>"<?php if($item['selected']) { ?> checked="checked"<?php } ?> />
<?php           if (is_array($item['caption'])) $item['caption'] = implode(', ', $item['caption']); ?>
                <label for="<?php echo $id; ?>"><?php if (strlen($item['caption'])) $this->d($item['caption'], $control->isHtmlAllowed()); else echo "&nbsp;"; ?></label>
            </div>
<?php } ?>
        </div>
<?php
    }
    
    /**
     * @param Ac_Form_Control_Toggle $control
     */
    function showToggle($control) {
        if ($control->type == 'checkbox') {
            $this->showCheckbox($control);
        } else {
            $this->showSelectToggle($control);
        }
    }
     
    /**
     * @param Ac_Form_Control_Toggle $control
     */
    function _showSelectToggle($control) {
        echo "<h1 style='color: red'>".className($this).'::'.__FUNCTION__.' is not implemented yet</h1>';
    }
    
    /**
     * @param Ac_Form_Control_Toggle $control
     */
    function showCheckbox($control) {
            $attribs = $control->getHtmlAttribs();
            if ($control->isReadOnly()) {
                $attribs['disabled'] = true;
            }
            $attribs['name'] = $this->_mapNames($control, 'value');
            $attribs['value'] = 1;
            $attribs['type'] = 'checkbox';
            if ($control->getDisplayValue()) $attribs['checked'] = true;
?>
            <input <?php $this->attribs($attribs); ?> /><?php
    }

    
    /**
     * @param Ac_Form_Control_Group $group
     */
    function showControlGroup ($group) {
        if (strlen($group->preHtml)) echo $group->preHtml;
        if ($group->groupTemplatePart) $this->show($group->groupTemplatePart, array($group));
        else {
            if ($group->style == 'table') $this->showTable($group);
            elseif ($group->style == 'horizontal') $this->showHTable($group);
            elseif ($group->style == 'divsList') $this->showDivsList ($group, 'divWrapper');
            else $this->showSimpleList($group);
        }
        if (strlen($group->postHtml)) echo $group->postHtml;
    }
    
    /**
     * @param Ac_Form_Control_Date $date
     */
    function showDate($date) {
        $context = $date->getContext();
        $id = $date->getId();    
        $name = $this->_mapNames($date, 'value');
            $attribs = Ac_Util::m($this->_getAttribs($date), array(
                'type' => 'text',
                'name' => $name,
                'value' => $date->getDisplayValue(),
                'id' => $id,
            ));
            
            if ($date->isReadOnly()) $attribs['readOnly'] = true;
?>
            <input <?php echo Ac_Util::mkAttribs($attribs); ?> />
<?php
        if ($date->showCalendar && !$date->isReadOnly()) {
            $this->addJsLib('{AC}/vendor/calendar/calendar_stripped.js');
            $this->addJsLib('{AC}/vendor/calendar/calendar-setup_stripped.js');
            $this->addJsLib('{AC}/vendor/calendar/lang/calendar-'.(defined('AC_LANG_ID')? AC_LANG_ID : 'en').'.js');
            if ($date->calendarSkin) {
                $this->addCssLib('{AC}/vendor/calendar/calendar-'.$date->calendarSkin.'.css');
            }
            $json = array(
                'inputField' => $id,
                'showsTime' => false,
            );
            if ($df = $date->getCalendarDateFormat()) $json['ifFormat'] = $df;
            Ac_Util::ms($json, $date->getExtraJson());
            $helper = $this->getHtmlHelper();
?>
            <script type='text/javascript'>
                Calendar.setup(<?php echo $helper->toJson($json, 16); ?>);
            </script>            
<?php
        }
    }
    
    function showUpload(Ac_Form_Control_Upload $upload) {
        
        $name = $upload->getFileParamName();
        $attribs = Ac_Util::m($this->_getAttribs($upload), array(
            'type' => 'file',
            'name' => $name,
        ));
?>        
        <input <?php echo Ac_Util::mkAttribs($attribs); ?> />
<?php
        
    }
    
    function showErrorList(Ac_Form_Control_ErrorList $control) {
        if ($control->showErrorsInMainArea && ($e = $control->getDisplayValue())) {
?>
        <li class="errors">
            <?php echo Ac_Util::implode_r("</li><li>", Ac_Util::flattenArray($e)); ?>
        </li>
<?php   
        }
    }

    
}
