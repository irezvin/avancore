<?php

class Ae_Form_Control_Template_Table extends Ae_Form_Control_Template {
    
    /**
     * @param Ae_Form_Control_Table $table
     */
    function showTable (& $table) {
        $this->addCssLib('{AECSS}aeTableInput.css', false);
        $this->addJsLib('{AE}aeTableData.js', false);
        $this->addJsLib('{AE}aeTableInput.js', false);
        $hh = $this->getHtmlHelper();
        $jsnData = $hh->toJson($table->getDataJson(), 8, 4, true, false);
        //Ae_Util::d($table->getDataJson());
        $jsnOptions = $hh->toJson($table->getTableJson(), 8, 4, true, false); 
        $ctx = & $table->getContext();
        $tcId = $ctx->mapIdentifier('ctr');
        $jsDataId = $ctx->mapIdentifier('data');
        $jsTableId = $ctx->mapIdentifier('table');
?>

        <div id="<?php $this->d($tcId); ?>">&nbsp;</div>

        <script type="text/javascript">
            <?php echo $jsDataId; ?> = new AeTableData(
                <?php echo $jsnData; ?>
                
            );
            var _table_options = <?php echo $jsnOptions; ?>;
            _table_options.data =  <?php echo $jsDataId; ?>;
            _table_options.element = $('<?php echo $tcId ;?>');
            <?php echo $jsTableId; ?> = new AeTableInput(_table_options);
            delete _table_options;
        </script>
    
<?php        
    }
    
}

?>