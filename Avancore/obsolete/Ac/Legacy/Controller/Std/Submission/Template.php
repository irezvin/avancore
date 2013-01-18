<?php

class Ac_Legacy_Controller_Std_Submission_Template extends Ac_Template_Html {
    
    /**
     * @var Ac_Legacy_Controller_Std_Submission
     */
    var $submission = false;
    
    /**
     * @var Ac_Model_Object
     */
    var $model = false;
    
    /**
     * @var Ac_Legacy_Controller_Std_Submission_Sendout
     */
    var $currentSendout = false;
    
    var $errors = false;

    function showErrors() {
        if ($this->errors) {
            if (!is_array($this->errors)) $this->errors = array($this->errors);    
?>
    <div class='errors'>
        <?php echo (nl2br(Ac_Util::implode_r("\n", $this->errors))); ?>
    </div>
<?php
        }
    }
    
    function showForm() {
        $this->appendPathway(false, $this->submission->title);
        $this->addPageTitle($this->submission->title);
        if ($this->errors) $this->showErrors();
?>
    <div class='submissionForm'>
<?php   if ($this->submission->title) { ?>
        <h1><?php $this->d($this->submission->title); ?></h1>
<?php   } ?>  
<?php           
        $form = $this->frontend->getForm();
        echo $form->fetchPresentation();
?>    
    </div>
<?php    
    }
    
    function showDone() {
        if ($this->errors) $this->showErrors();
?> 
    <div class='submissionForm'>
        <h1><?php $this->d($this->submission->submissionCompleteTitle); ?></h1>
        <p><?php $this->d($this->submission->submissionCompleteMessage); ?></p>
    </div>

<?php
    }

    function showEmail() {
?>
    <html>
         <head>
<?php        $this->showEmailHtmlHead(); ?>         
         </head>
         <body>
<?php        if ($this->currentSendout->templatePrefixPart) $this->show($this->currentSendout->templatePrefixPart); ?>
<?php        if ($this->currentSendout->templateBodyPart) $this->show($this->currentSendout->templateBodyPart); ?>         
<?php        if ($this->currentSendout->templateSuffixPart) $this->show($this->currentSendout->templateSuffixPart); ?>
         </body>
    </html> 
<?php
    }
    
    function showEmailHtmlHead() {
    }
    
    function showObject() {
        $i = 0;
?>
    <table border='1'>
<?php    foreach ($this->model->listProperties() as $p) { ?>
<?php        $propInfo = $this->model->getPropertyInfo($p, true); ?>
<?php        if (!($flag = $this->currentSendout->propertyFlag) || isset($propInfo->$flag) && $propInfo->$flag) { ?>
<?php             $val = $this->model->getField($p); $capt = strlen($propInfo->caption)? $propInfo->caption : $p; ?>
<?php             if (strlen($val)) { ?>
         <tr class='row<?php $this->d($i++ % 2); ?> field_<?php $this->d($p); ?>'>
             <th><?php $this->d($capt); ?></th>
             <td><?php $this->d($val); ?></td>
         </tr>
<?php             } ?>
<?php        } ?> 
<?php    } ?>
    </table>
<?php
    }
    
    function showInvalidRequest() {
?>
        <div class='errors invalidRequest'>Invalid Request</div>
<?php
    }
    
    
}

?>