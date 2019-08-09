<?php

class Ac_Cg_Template_Languages extends Ac_Cg_Template {
    
    var $genLangFile = false;
    var $langFile = false;

    var $langStrings = array();

    function doInit() {
        $this->genLangFile = $this->generator->genDir.'/languages/en.php';
        $this->langFile = 'languages/en.php';
        
        foreach ($this->domain->listModels() as $m) {
            $mod = $this->domain->getModel($m);
            if ($mod->getUseLangStrings()) {
                Ac_Util::ms($this->langStrings, $mod->getAllLangStrings());
            }
        }
        
    }
    
    function _generateFilesList() {
        $res = array();
        $res['genLangFile'] = array(
            'relPath' => $this->genLangFile,
            'isEditable' => false,
            'templatePart' => 'genLangFile',
        );
        $res['langFile'] = array(
            'relPath' => $this->langFile,
            'isEditable' => true,
            'templatePart' => 'langFile',
        );
        return $res;
    }
    
    function showGenLangFile() {
        ksort($this->langStrings);
        $this->phpOpen();
?>

    $lang = <?php $this->exportArray($this->langStrings, 4); ?>;
    
<?php         
    } 
    
    
    function showLangFile() {
        $this->phpOpen();
?>
    
    require(dirname(__FILE__).'/../../languages/en.php');
    Ac_Util::ms($lang, array(
    
//  'lang_string_1' => 'caption_1',
//  'lang_string_2' => 'caption_2', 
    
    ));

<?php        
    }
    
}