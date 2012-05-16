<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ac_Test_Adapter extends Ac_Test_Base {
    
    function testAdapter() {
        
        $appClassFile = dirname(dirname(dirname(dirname(__FILE__)))).'/sampleApp/classes/Sample/App.php';
        $deployDir = dirname(dirname(dirname(dirname(__FILE__)))).'/sampleApp/deploy/';
        $configFilesToLoad = array(
            realpath($deployDir.'/app.config.php'),
            realpath($deployDir.'/test.env.config.php'),
            realpath($deployDir.'/Ac_Application_Adapter.adapter.config.php'),
        );
        
        $adapter = new Ac_Application_Adapter(array(
            'appClassFile' => $appClassFile,
            'keyOverwrittenInConstructorOptions' => ($pv = 'properValue'),
            'keyOverwrittenInConstructorOptions2' => $pv,
            'envName' => 'test',
        ));
        
        // Test auto-detection of config files
        
        $adapterFiles = array();
        foreach ($adapter->getConfigFiles() as $f) {
            if (is_file($f)) $adapterFiles[] = realpath($f);
        }
        $this->assertEqual($configFilesToLoad, $adapterFiles);
        

        // Test config overwrite priority
        
        $this->assertEqual($adapter->getConfigValue('keyOverwrittenInEnvOptions'), 'Value of test.env.config.php');
        $this->assertEqual($adapter->getConfigValue('keyOverwrittenInConstructorOptions'), $pv);
        $this->assertEqual($adapter->getConfigValue('keyOverwrittenInConstructorOptions2'), $pv);
        $this->assertEqual($adapter->getConfigValue('keyOverwrittenInAdapterOptions'), '*AO*');
        
        // Test directories auto-detection
        
        $this->assertEqual(realpath($adapter->getConfigPath()), realpath($deployDir));
        $this->assertTrue(is_dir($adapter->getInitPath()));
        $this->assertTrue(is_dir($adapter->getVarPath()));
        $this->assertTrue(is_dir($adapter->getVarFlagsPath()));
        $this->assertTrue(is_dir($adapter->getVarLogsPath()));
        $this->assertTrue(is_dir($adapter->getVarCachePath()));
        $this->assertTrue(is_dir($adapter->getGenPath()));
        $this->assertTrue(is_dir($adapter->getVendorPath()));
        
        // There are several language directories...
        
        $ld = array(realpath(dirname($deployDir).'/languages'), realpath(dirname($deployDir).'/gen/languages'));
        asort($ld);
        
        $aLd = explode(PATH_SEPARATOR, $adapter->getLanguagesPaths());
        asort($aLd);
        
        // Test developer-specified directory validation
        
        $badPath = 'SomeNonExistentDir';
        $fooAdapter = new Ac_Application_Adapter(array('appClassFile' => $appClassFile, 'varFlagsPath' => $badPath));
        
        $ex = false;
        try {
            $fooAdapter->getVarFlagsPath();
        } catch (Exception $e) {
            $ex = $e;
        }
        $this->assertTrue($e instanceof Exception && preg_match('/not found/', $e->getMessage()));
        
        // Test disabled directory detection
        
        $fooAdapter2 = new Ac_Application_Adapter(array('appClassFile' => $appClassFile, 'varFlagsPath' => $badPath, 'checkDirs' => false));
        $this->assertEqual($fooAdapter2->getVarFlagsPath(), $badPath);
        
        // Test proper directory validation
        
        $fooAdapter3 = new Ac_Application_Adapter(array('appClassFile' => $appClassFile, 'varFlagsPath' => $deployDir, 'checkDirs' => true));
        $this->assertEqual($fooAdapter3->getVarFlagsPath(), $deployDir);
        
        $this->assertEqual($ld, $aLd);
        
        // Test URLs guessing
        
        $this->assertTrue(strlen($wu = $adapter->getWebUrl()) > 0);
        $this->assertTrue(strpos($adapter->getWebAssetsUrl(), '/assets') >= 0);
        $this->assertTrue(strpos($adapter->getAdminImagesUrl(), '/images') >= 0);
        
    }
    
}