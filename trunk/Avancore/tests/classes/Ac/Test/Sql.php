<?php

class Ac_Test_Sql extends Ac_Test_Base {
    
    function testDbQuote() {
        $db = $this->getAeDb();
        $repl = $db->replacePrefix("SELECT '#__foo' FROM #__foo WHERE '#__a \\'b\\'c' = `#__foo`.d");
        $px = $db->getDbPrefix();
        if (!$this->assertEqual($repl, "SELECT '#__foo' FROM {$px}foo WHERE '#__a \\'b\\'c' = `{$px}foo`.d"))
            var_dump($repl);
    }
    
    function testArgQuote() {
        $db = $this->getAeDb();
        $prep = $db->preProcessQuery(array(
            "SELECT :first, ? + :second AS `firstPosPlusSecondNamed`, "
            . "'''? can be used for positional arg and :something for named\'' AS `noArgs`, "
            . ":third + ? AS `thirdNamedPlusSecondPos`", 
            
            'first' => 'FirstNamed', 
            'FirstPos', 
            'second' => 'SecondNamed', 
            'SecondPos',
            'third' => 'ThirdNamed'
        ));
        if (!$this->assertEqual($prep, 
            "SELECT 'FirstNamed', 'FirstPos' + 'SecondNamed' AS `firstPosPlusSecondNamed`, "
            . "'''? can be used for positional arg and :something for named\'' AS `noArgs`, "
            . "'ThirdNamed' + 'SecondPos' AS `thirdNamedPlusSecondPos`")) var_dump($prep);
    }
    
}