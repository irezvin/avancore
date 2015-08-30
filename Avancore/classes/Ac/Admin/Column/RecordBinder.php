<?php

/**
 * Binds records to the Javascript GUI
 */
class Ac_Admin_Column_RecordBinder extends Ac_Admin_Column {

     var $trIdPrefix = 'tr';
     
     /**
      * @var Ac_Legacy_Controller_Context_Http
      */
     var $_context = false;
    
     var $_recordsJson = array();
     
     var $_recordIdentifiers = array();
     
     var $canEdit = true;

     function showHeader($rowCount, $rowNo = 1) {
         $this->_table->trAttribsCallback = array(& $this, 'trAttribsCallback');
         $this->_context = $this->manager->getContext();
     }
     
     function trAttribsCallback(& $record, & $attribs) {
         $attribs['id'] = $this->_context->mapIdentifier($this->trIdPrefix.$this->manager->getIdentifierOf($record));
     }
     
     function showCell($record, $rowNo) {
         $this->_recordsJson[] = array('key' => $id = $this->manager->getIdentifierOf($record));
         $this->_recordIdentifiers[] = $id;
     }
     
     function showHint() {
        $tpl = $this->manager->getTemplate();
        $jsHelper = $tpl->getHtmlHelper();
        ?>
     
        <script type="text/javascript">
            var _c = <?php echo $this->manager->getJsListControllerRef() ?>;
            _c.addRecords(<?php echo $jsHelper->toJson($this->_recordsJson, 16); ?>);
<?php foreach ($this->_recordIdentifiers as $i => $id) { ?>
            _c.getRecord(<?php echo $i; ?>)
                .observe([AvanControllers.ListController.ShowSelected, AvanControllers.ListController.ToggleSelected], {element: <?php echo $jsHelper->jsQuote($this->_context->mapIdentifier($this->trIdPrefix.$id)); ?>})<?php 
if ($this->canEdit) { ?>.observe([AvanControllers.ListController.EditRecord], {eventName: 'dblclick', element: <?php echo $jsHelper->jsQuote($this->_context->mapIdentifier($this->trIdPrefix.$id)); ?>}) <?php } ?>;
<?php } ?>

            delete _c;
        </script>
     <?php
     }
}
    
