<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ac_Test_CsvResume extends UnitTestCase {

	var $silent = true;
	
	var $moduleSettings = array(
		'objects' => array(
			'hierarchy' => array(
				'emptyIsRepeater' => false,
				'fieldPrefix' => 'subject.',
				'children' => array(array(
					'emptyIsRepeater' => false,
					'fieldPrefix' => array('c.', 'v.'),
				)),
			),
		),
	);
	
	function testCsvResume() {
            $moduleSettings = $this->moduleSettings;
            
            $serFile = dirname(__FILE__).'/assets/serData';
            $filename = dirname(__FILE__).'/assets/testCsv.csv';
            
            if (!is_file($serFile)) {
	            	
		        $storageOptions = array(
		            'class' => 'Ac_Imex_Storage_Csv',
		            'filename' => $filename,
		            'delimiter' => ';',
		            'enclosure' => '"',
		        	'inputEncoding' => 'windows-1251',
		        	'outputEncoding' => 'utf-8',
		        );
		        
		        foreach ($this->moduleSettings as $n => $m) {
		            if (isset($m['hierarchy'])) {
		                $storageOptions['hierarchy'][$n] = $m['hierarchy'];
		            }
		        }
	            
	            $imexOptions = array(
	                'defaultModuleClass' => 'Ac_Imex_Module_Basic',
	                'modules' => $moduleSettings,
	            );
	            $processor = new Ac_Imex_Processor($imexOptions);
	            $storage = & Ac_Imex_Storage::factory($storageOptions, $this->processor);
	            $processor->setImporter($storage);
	
	            
				$sec = $storage->getNextBlock();
				$recLevel1 = $sec->getNextBlock();
				$recLevel2 = $recLevel1->getNextBlock();
				
            	if (!$this->silent) var_dump($storage->_lineNo);
				file_put_contents($serFile, serialize(array($processor, $storage, $sec, $recLevel1, $recLevel2)));
				
            } else {
            	
            	if (!$this->silent) var_dump("Loading");
            	
            	$cnt = file_get_contents($serFile); 
            	unlink($serFile);
            	list($processor, $storage, $sec, $recLevel1, $recLevel2) = unserialize($cnt);

            	if (!$this->silent) var_dump($recLevel1->getNextBlock()->data); else $recLevel1->getNextBlock(); 
            	if (!$this->silent) var_dump($storage->_lineNo);
            	
            }
			
			if (!$this->silent) var_dump("End");
			
	}
	
}

?>