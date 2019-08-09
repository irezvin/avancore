<?php

class Ac_Form_Control_Template_Parameters extends Ac_Form_Control_Template {
    
    function showParameters(Ac_Form_Control_Parameters $control) {
        
        $tsId = $control->getContext()->mapIdentifier('tabs');
        
        $jf = $control->getJForm();
        $fs = $jf->getFieldsets(strlen($control->fieldsGroup)? $control->fieldsGroup : null);
        
        foreach (Ac_Util::toArray($control->langPackages) as $extension) {
            JFactory::getLanguage()->load($extension);
        }
        
        if ($fs) {
            $v = array_values($fs);
            echo JHtml::_('bootstrap.startTabSet', $tsId, array('active' => 'options-'.$v[0]->name));
            foreach ($fs as $name => $fieldset) {
                if (!isset($fieldset->repeat) || $fieldset->repeat == false)
                {
                    $label = !empty($fieldset->label) ? JText::_($fieldset->label, true) : JText::_(sprintf($control->fieldsetLabelFormat, $fieldset->name), true);
                    $optionsname = 'options-' . $fieldset->name;
                    echo JHtml::_('bootstrap.addTab', $tsId, $optionsname,  $label);
?>
                    <div class="form-vertical">
<?php        
                    
                    if (isset($fieldset->description) && trim($fieldset->description))
                    {
                        echo '<p class="tip">' . $this->escape(JText::_($fieldset->description)) . '</p>';
                    }

                    $hidden_fields = '';

                    foreach ($jf->getFieldset($name) as $field)
                    {
                        if (!$field->hidden)
                        {
                            ?>
                            <div class="control-group">
                                <div class="control-label">
                                    <?php echo $field->label; ?>
                                </div>
                                <div class="controls">
                                    <?php echo $field->input; ?>
                                </div>
                            </div>
                        <?php
                        }
                        else
                        {
                            $hidden_fields .= $field->input;
                        }
                    }
                    echo $hidden_fields;
                    
?>
                    </div>
<?php

                    echo JHtml::_('bootstrap.endTab');
                }
            }
            echo JHtml::_('bootstrap.endTabSet', $tsId);
        }
    }
    
}