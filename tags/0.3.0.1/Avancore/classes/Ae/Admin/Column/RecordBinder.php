<?php

Ae_Dispatcher::loadClass('Ae_Admin_Column');

/**
 * Binds records to the Javascript GUI
 */
class Ae_Admin_Column_RecordBinder extends Ae_Admin_Column {

     var $trIdPrefix = 'tr';
     
     /**
      * @var Ae_Controller_Context_Http
      */
     var $_context = false;
    
     var $_recordsJson = array();
     
     var $_recordKeys = array();
     
     var $canEdit = true;

     function showHeader($rowCount, $rowNo = 1) {
         $this->_table->trAttribsCallback = array(& $this, 'trAttribsCallback');
         $this->_context = & $this->manager->getContext();
     }
     
     function trAttribsCallback(& $record, & $attribs) {
         $attribs['id'] = $this->_context->mapIdentifier($this->trIdPrefix.$this->manager->getStrPk($record));
     }
     
     function showCell(& $record, $rowNo) {
         $this->_recordsJson[] = array('key' => $key = $this->manager->getStrPk($record));
         $this->_recordKeys[] = $key;
     }
     
     function showHint() {
        $tpl = & $this->manager->getTemplate();
        $jsHelper = & $tpl->getHtmlHelper();
        ?>
     
        <script type="text/javascript">
            var _c = <?php echo $this->manager->getJsListControllerRef() ?>;
            _c.addRecords(<?php echo $jsHelper->toJson($this->_recordsJson, 16); ?>);
<?php foreach ($this->_recordKeys as $i => $key) { ?>
            _c.getRecord(<?php echo $i; ?>)
                .observe([_c.ShowSelected, _c.ToggleSelected], {element: <?php echo $jsHelper->jsQuote($this->_context->mapIdentifier($this->trIdPrefix.$key)); ?>})<?php 
if ($this->canEdit) { ?>.observe([_c.EditRecord], {eventName: 'dblclick', element: <?php echo $jsHelper->jsQuote($this->_context->mapIdentifier($this->trIdPrefix.$key)); ?>}) <?php } ?>;
<?php } ?>

            delete _c;
        </script>
     <?php
     }
}
    
?>