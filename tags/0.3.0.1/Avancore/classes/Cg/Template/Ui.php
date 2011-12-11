<?php

Ae_Dispatcher::loadClass('Cg_Template');

class Cg_Template_Ui extends Cg_Template {
    
    var $modelName = false;
    var $modelClass = false;
    var $genFormClass = false;
    var $formClass = false;
    var $genListClass = false;
    var $listClass = false;
    var $pagemapFile = false;
    var $formTemplateFile = false;
    var $tableName = false;
    
    var $singlePkColumn = false;
    var $nameColumn = false;
    var $publishedColumn = false;
    var $orderingColumn = false;
    var $orderGroupColumn = false;

    function doInit() {
        $this->modelName = $this->model->getModelBaseName();
        $this->modelClass = $this->model->className;
        $this->tableName = $this->model->tableObject->name;
        $this->genFormClass = $this->modelName.'_Base_Form';
        $this->formClass = $this->modelName.'_Form';
        $this->genListClass = $this->modelName.'_Base_List';
        $this->listClass = $this->modelName.'_List';
        $this->pagemapFile = $this->strategy->getPagemapFileName($this->model);
        $this->formTemplateFile = 'forms/'.$this->modelName;
        
        if (count($pkf = $this->model->tableObject->listPkFields()) === 1) $this->singlePkColumn = $pkf[0];
        
        
        $this->nameColumn = $this->model->titleProp;
        $this->publishedColumn = $this->model->publishedProp;
        $this->orderingColumn = $this->model->orderingProp;
        $this->orderGroupColumn = $this->model->orderGroupProp;
        
    }
    
    function _generateFilesList() {
        $res = array(
            'form' => array(
                'relPath' => Cg_Util::className2fileName($this->formClass), 
                'isEditable' => true, 
                'templatePart' => 'form',
            ),
            'genForm' => array(
                'relPath' => 'gen/'.Cg_Util::className2fileName($this->genFormClass), 
                'isEditable' => false, 
                'templatePart' => 'genForm',
            ),
            'list' => array(
                'relPath' => Cg_Util::className2fileName($this->listClass), 
                'isEditable' => true, 
                'templatePart' => 'list',
            ),
            'genList' => array(
                'relPath' => 'gen/'.Cg_Util::className2fileName($this->genListClass), 
                'isEditable' => false, 
                'templatePart' => 'genList',
            ), 
            'pagemap' => array(
                'relPath' => $this->pagemapFile, 
                'isEditable' => true, 
                'templatePart' => 'pagemap',
            ),
            'formTemplate' => array(
                'relPath' => 'templates/'.$this->formTemplateFile.'.tpl.php', 
                'isEditable' => true, 
                'templatePart' => 'formTemplate',
            ),
        );
//      var_dump($res);
        return $res;
    }
    
    // --------------------------- genForm -------------------------
    
    function showGenForm() {
?>
<?php $this->phpOpen(); ?>

Ae_Dispatcher::loadClass (<?php $this->str($this->model->uiFormBaseClass) ?>);

class <?php $this->d($this->genFormClass); ?> extends <?php $this->d($this->model->uiFormBaseClass) ?> {

    function <?php $this->d($this->genFormClass); ?> ($extraSettings = array(), $taskName = false) {
        parent::<?php $this->d($this->model->uiFormBaseClass) ?>($extraSettings, $taskName);
    }
}

<?php $this->phpClose(); ?><?php        
    }
    
    // --------------------------- form -------------------------
    
    function showForm() {
?>
<?php $this->phpOpen(); ?>

Ae_Dispatcher::loadClass (<?php $this->str($this->genFormClass); ?>);

class <?php $this->d($this->formClass); ?> extends <?php $this->d($this->genFormClass); ?> {

    function <?php $this->d($this->formClass); ?> ($extraSettings = array(), $taskName = false) {
        parent::<?php $this->d($this->genFormClass); ?>($extraSettings, $taskName);
    }
}

<?php $this->phpClose(); ?><?php        
    }
    
    // --------------------------- genList -------------------------

    function showGenList() {
?> 
<?php $this->phpOpen(); ?>

Ae_Dispatcher::loadClass(<?php $this->str($this->model->uiListBaseClass) ?>);

class <?php $this->d($this->genListClass); ?> extends <?php $this->d($this->model->uiListBaseClass) ?> {

    var $tableName = <?php $this->str($this->tableName); ?>;
    var $recordClass = <?php $this->str($this->modelClass); ?>;
    var $defaultTemplateName = "recordslist";

<?php /* if ($this->orderingColumn) { ?>
    function getOrdering() {
<?php if ($this->orderGroupColumn) { ?>
        $res = " t.<?php $this->d($this->orderGroupColumn); ?>, t.ordering ";
<?php } else { ?>
        $res = "t.ordering";
<?php } ?>
        return $res;
    }

<?php } */ ?>
}
<?php $this->phpClose(); ?><?php        
    }
    
    // --------------------------- list -------------------------

    function showList() {
?> 
<?php $this->phpOpen(); ?>

Ae_Dispatcher::loadClass(<?php $this->str($this->genListClass); ?>);

class <?php $this->d($this->listClass); ?> extends <?php $this->d($this->genListClass); ?> {

}
<?php $this->phpClose(); ?><?php        
    }
    
    // --------------------------- pagemap -------------------------

    function showPagemap() {
        $mn = $this->modelName;        
?> 
<?php $this->phpOpen(); ?>

$pageMap = array_merge($pageMap, array(
<?php if ($this->publishedColumn) { ?>
    '<?php echo $mn; ?>_Publish' => array( 'extends' => '<?php echo $mn; ?>_List', 'extraParams' => array( 'processing' => 'publish' ), ),    

    '<?php echo $mn; ?>_Unpublish' => array( 'extends' => '<?php echo $mn; ?>_List', 'extraParams' => array( 'processing' => 'unpublish' ), ),    

<?php } ?>
<?php if ($this->model->uiCanDelete) { ?>
    '<?php echo $mn; ?>_Delete' => array( 'extends' => '<?php echo $mn; ?>_List', 'extraParams' => array( 'processing' => 'delete', ), ),

<?php } ?>
<?php if ($this->orderingColumn) { ?>
    '<?php echo $mn; ?>_Order_Up' => array( 'extends' => '<?php echo $mn; ?>_List', 'extraParams' => array( 'processing' => 'orderUp', ), ),
    
    '<?php echo $mn; ?>_Order_Down' => array( 'extends' => '<?php echo $mn; ?>_List', 'extraParams' => array( 'processing' => 'orderDown', ), ),
    
    '<?php echo $mn; ?>_Order_Save' => array( 'extends' => '<?php echo $mn; ?>_List', 'extraParams' => array( 'processing' => 'saveOrder', ), ),
    
<?php } ?>
    '<?php echo $mn; ?>_Save' => array( 'extends' => '<?php echo $mn; ?>_Edit', 'extraParams' => array( 'processing' => 'save', ), ),
    
    '<?php echo $mn; ?>_Apply' => array( 'extends' => '<?php echo $mn; ?>_Edit', 'extraParams' => array( 'processing' => 'apply', ), ),

    '<?php echo $mn; ?>_Cancel' => array( 'extends' => '<?php echo $mn; ?>_Edit', 'extraParams' => array( 'processing' => 'cancel', ), ),

    '<?php echo $mn; ?>_Edit' => array(
        'class' => '<?php echo $mn; ?>_Form',
        'extraParams' => array(
            'title' => <?php $this->str($this->model->singleCaption); ?>,
            'subtitle' => <?php $this->str($this->model->singleCaption); ?>,
            'features' => array (),
            'recordClass' => <?php $this->str($this->model->className); ?>,
            'mapperClass' => <?php $this->str($this->model->getMapperClass()); ?>,
            'formTemplate' => <?php $this->str($this->formTemplateFile) ?>,
            'returnTaskName' => '<?php echo $mn; ?>_List',
<?php if (!$this->model->uiCanCreate) { ?>
            'canCreate' => false, 
<?php } ?>            

            'toolbar' => array(
                'save' => array(
                    'href' => "javascript: submitbutton('<?php echo $mn; ?>_Save');", 
                    'icon' => 'save.png', 'iconOver' => 'save_f2.png', 'alt' => <?php $this->lng('save'); ?>, 
                ),
                
                '<?php echo $mn; ?>_Apply' => array(
                    'href' => "javascript: submitbutton('<?php echo $mn; ?>_Apply');", 
                    'icon' => '<?php echo $mn; ?>_Apply.png', 'iconOver' => 'apply_f2.png', 'alt' => <?php $this->lng('apply'); ?>, 
                ),
                
                'cancel' => array(
                    'href' => "javascript: submitbutton('<?php echo $mn; ?>_Cancel');", 
                    'icon' => 'cancel.png', 'iconOver' => 'cancel_f2.png', 'alt' => <?php $this->lng('close'); ?>, 
                ),
            ),
        ),
    ),

    '<?php echo $mn; ?>_List' => array(
        'class' => '<?php echo $mn; ?>_List',
        
        'extraParams' => array(
            'title' => <?php $this->str($this->model->pluralCaption); ?>,
            'mapperClass' => <?php $this->str($this->model->getMapperClass()); ?>, 
<?php if ($this->orderingColumn) { ?>

            'ordering' => "t.<?php $this->d($this->orderingColumn); ?><?php if ($this->orderGroupColumn) { ?>, <?php $this->d($this->orderGroupColumn); ?><?php } ?>",
<?php } ?>            
            'tableColumnSettings' => array(
                'number' => array('class' => 'Ae_Page_List_Column_Number'),
                'checked' => array('class' => 'Ae_Page_List_Column_Checked', 'checkoutProcessing' => false),
<?php if ($this->nameColumn) { ?>
                <?php $this->str($this->nameColumn); ?> => array('class' => 'Ae_Page_List_Column_Link', 'taskName' => '<?php echo $mn; ?>_Edit', 'linkTitle' => <?php $this->lng('editRecord'); ?>, 'title' => <?php $this->lng('title'); ?>),
<?php } else { ?>
                '<?php $this->d($this->singlePkColumn); ?>' => array('class' => 'Ae_Page_List_Column_Link', 'taskName' => '<?php echo $mn; ?>_Edit', 'linkTitle' => <?php $this->lng('editRecord'); ?>, 'title' => <?php $this->lng('id'); ?>),
<?php } ?>
<?php if ($this->orderingColumn) { ?>
                'ordering' => array('class' => 'Ae_Page_List_Column_Reorder', 'orderUpTask' => '<?php echo $mn; ?>_Order_Up', 'orderDownTask' => '<?php echo $mn; ?>_Order_Down' <?php if($this->orderGroupColumn) { ?>, 'orderProperty' => '<?php  $this->d($this->orderGroupColumn); ?>' <?php } ?>),
                'saveOrder' => array('class' => 'Ae_Page_List_Column_SaveOrder', 'taskName' => '<?php echo $mn; ?>_Order_Save', 'groupProperty' => 'groupId'),
<?php } ?>
<?php if ($this->publishedColumn) { ?>
                '<?php  $this->d($this->publishedColumn); ?>' => array('class' => 'Ae_Page_List_Column_Published', 'publishTask' => '<?php echo $mn; ?>_Publish', 'unpublishTask' => '<?php echo $mn; ?>_Unpublish'),
<?php } ?>
            ),
            
            'processingSettings' => array(
<?php if ($this->model->uiCanDelete) { ?>
                'delete' => array('class' => 'Ae_Page_Processing_Delete'),
<?php } ?>                
<?php if ($this->orderingColumn) { ?>
                'orderUp' => array('class' => 'Ae_Page_Processing_Reorder', 'extraSettings' => array('direction' => -1, <?php if ($this->orderGroupColumn) { ?>'groupProperty' => '<?php $this->d($this->orderGroupColumn); ?>', <?php } ?>)),
                'orderDown' => array('class' => 'Ae_Page_Processing_Reorder', 'extraSettings' => array('direction' => 1, <?php if ($this->orderGroupColumn) { ?>'groupProperty' => '<?php $this->d($this->orderGroupColumn); ?>', <?php } ?>)),
                'saveOrder' => array('class' => 'Ae_Page_Processing_SaveOrder' <?php if ($this->orderGroupColumn) { ?>, 'extraSettings' => array('groupProperty' => '<?php $this->d($this->orderGroupColumn); ?>', ), <?php } ?> ),
<?php } ?>
<?php  if ($this->publishedColumn) { ?>
                'publish' => array('class' => 'Ae_Page_Processing_Publish', <?php if ($this->publishedColumn != 'published') { ?> 'extraSettings' => array('propName' => '<?php $this->d($this->publishedColumn); ?>'), <?php } ?>),
                'unpublish' => array('class' => 'Ae_Page_Processing_Unpublish', <?php if ($this->publishedColumn != 'published') { ?>  'extraSettings' => array('propName' => '<?php $this->d($this->publishedColumn); ?>'),  <?php } ?>),
<?php } ?>
            ),

            'toolbar' => array(
<?php  if ($this->publishedColumn) { ?>
                'publish' => array(
                    'href' => "javascript:if (document.adminForm.boxchecked.value == 0){ alert('".<?php $this->lng('selectElementsToPublish'); ?>."'); } else {submitbutton('<?php echo $mn; ?>_Publish');}", 
                    'icon' => 'publish.png', 'iconOver' => 'publish_f2.png', 'alt' => <?php $this->lng('publish'); ?>, 'confirm' => true, 'listSelect' => true
                ),
                
                'unpublish' => array(
                    'href' => "javascript:if (document.adminForm.boxchecked.value == 0){ alert('".<?php $this->lng('selectElementsToUnpublish'); ?>."'); } else {submitbutton('<?php echo $mn; ?>_Unpublish');}", 
                    'icon' => 'unpublish.png', 'iconOver' => 'unpublish_f2.png', 'alt' => <?php $this->lng('unpublish'); ?>, 'confirm' => true, 'listSelect' => true
                ),

<?php } ?>
<?php if ($this->model->uiCanDelete) { ?>
                'delete' => array(
                    'href' => "javascript:if (document.adminForm.boxchecked.value == 0){ alert('".<?php $this->lng('selectElementsToDelete'); ?>."'); } else if (confirm('".<?php $this->lng('deleteConfirm'); ?>."')){ submitbutton('<?php echo $mn; ?>_Delete');}", 
                    'icon' => 'delete.png', 'iconOver' => 'delete_f2.png', 'alt' => <?php $this->lng('delete'); ?>, 'confirm' => true, 'listSelect' => true
                ),
                
<?php } ?>
                'edit' => array(
                    'href' => "javascript:if (document.adminForm.boxchecked.value == 0){ alert('".<?php $this->lng('selectElementsToEdit'); ?>."'); } else {hideMainMenu();submitbutton('<?php echo $mn; ?>_Edit');}", 
                    'icon' => 'edit.png', 'iconOver' => 'edit_f2.png', 'alt' => <?php $this->lng('edit'); ?>, 'listSelect' => true
                ),
<?php if ($this->model->uiCanCreate) { ?>
                
                'new' => array(
                    'href' => "javascript:hideMainMenu(); submitbutton('<?php echo $mn; ?>_Edit');", 
                    'icon' => 'new.png','iconOver' => 'new_f2.png','alt' => <?php $this->lng('create'); ?>,
                ),
<?php } ?>                
            ),
        ),
    ),
));

<?php $this->phpClose(); ?><?php        
    }
    
    // --------------------------- formTemplate -------------------------

    function showFormTemplate() {
?><?php $this->phpOpen(); ?> $record = & $this->getRecord(); <?php $this->phpClose(); ?> 

<input type="hidden" name="<?php $this->d($this->singlePkColumn); ?>" value="<?php $this->phpOpen(); ?> echo $record-><?php $this->d($this->singlePkColumn); ?> <?php $this->phpClose(); ?>" />

<table cols="2">

<?php $this->phpOpen(); ?>

    foreach($record->listOwnFields(false) as $fieldName) {
        if ($fieldName !== <?php $this->str($this->singlePkColumn); ?>) $helper->showAuto($fieldName);
    }
<?php $this->phpClose(); ?>


</table><?php
    }
    
}

?>