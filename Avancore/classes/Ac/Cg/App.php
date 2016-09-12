<?php

class Ac_Cg_App extends Ac_Prototyped {
    
    /**
     * type of application, i.e. "Joomla"
     * @var string
     */
    protected $type = false;
    
    /**
     * application name, i.e. "MyApp"
     * @var string
     */
    protected $name = false;

    /**
     * @var Ac_Cg_Layout
     */
    protected $layout = false;

    /**
     * @var Ac_Cg_Template_Skel
     */
    protected $skel = false;

    /**
     * Sets type of application, i.e. "Joomla"
     * @param string $type
     */
    function setType($type) {
        $this->type = $type;
    }

    /**
     * Returns type of application, i.e. "Joomla"
     * @return string
     */
    function getType() {
        return $this->type;
    }

    /**
     * Sets application name, i.e. "MyApp"
     * @param string $name
     */
    function setName($name) {
        $this->name = $name;
    }

    /**
     * Returns application name, i.e. "MyApp"
     * @return string
     */
    function getName() {
        return $this->name;
    }
    
    function setLayout($layout) {
        if (!$layout) $layout = null;
        $this->layout = $layout;
        if (is_object($this->layout)) {
            $this->type = $this->layout->getAppType();
            if (!strlen($this->name)) $this->name = $this->layout->appName;
        }
    }

    /**
     * @return Ac_Cg_Layout
     */
    function getLayout() {
        if (!is_object($this->layout)) {
            $def = array();
            if (strlen($this->name)) $def['appName'] = $this->name;
            $this->layout = Ac_Prototyped::factory($this->layout, 'Ac_Cg_Layout', $def);
        }
        return $this->layout;
    }

    function setSkel($skel) {
        if (!$skel) $skel = null;
        $this->skel = $skel;
    }

    /**
     * @return Ac_Cg_Template_Skel
     */
    function getSkel() {
        if (!is_object($this->skel)) {
            $def = array();
            if (!$this->skel && $this->layout) {
                $this->skel = $this->layout->getSkelPrototype();
            } else {
                if ($this->layout) $def['layout'] = $this->getLayout();
                if (strlen($this->name)) $def['appName'] = $this->name;
            }
            $this->skel = Ac_Prototyped::factory($this->skel, 'Ac_Cg_Template_Skel', $def);
        }
        return $this->skel;
    }
    
    /**
     * Checks every layout in $possibleLayout against $dir
     * Sets $this->name and $this->layout to name and configured layout in case
     * of success (final layout is clone of original one with configured options)
     * 
     * @param Ac_Cg_Layout[] $possibleLayouts
     * @param string $dir
     * @return bool Whether any layout was properly detected
     */
    function detect($dir = false, array $possibleLayouts = array(), $bubble = false, & $recommendedLayout = null) {
        $recommendedLayout = null;
        do {
            $res = false;
            if ($this->layout && !$possibleLayouts) {
                $possibleLayouts = array($this->getLayout());
            }
            if ($dir === false) {
                if ($this->layout) $dir = $this->layout->pathRoot;
                else $dir = '';
            }
            foreach ($possibleLayouts as $i => $lay) {
                $test = clone $lay;
                if (strlen($this->name)) $test->appName = $this->name;
                if ($test->detect($dir, true)) {
                    $res = true;
                    // TODO: Take action when $this->name and 
                    // detected layout appName differ
                    if (!strlen($this->name)) $this->name = $test->appName;
                    $this->setLayout($test);
                    $this->type = $test->getAppType();
                } elseif ($test->isRecommended($dir)) {
                    $recommendedLayout = $test;
                    $recommendedLayout->pathRoot = $dir;
                }
            }
            if (!$res && $bubble) {
                $newDir = dirname($p = realpath($dir));
                if ($newDir === $p || !strlen($newDir)) $newDir = false;
                $dir = $newDir;
            }
        } while (!$res && $bubble && $dir !== false);
        return $res;
    }

    function layoutExists() {
        $lay = $this->getLayout();
        if (!$lay) return false;
        $res = is_dir($lay->pathRoot) && $lay->detect($lay->pathRoot);
        return $res;
    }
    
    function setSkelParams(array $params) {
        $skel = $this->getSkel();
        foreach ($params as $k => $v) {
            $skel->$k = $v;
        }
    }
    
    /**
     * @param string $dir
     * @param Ac_Cg_Generator $gen
     * @param array $params
     * @return Ac_Cg_Generator
     */
    function createSkel($dir, Ac_Cg_Generator $gen = null, array $params = array()) {
        $layout = $this->getLayout();
        $layout->pathRoot = $dir;
        $skel = $this->getSkel();
        if ($params) $this->setSkelParams ($params);
        if (is_null($gen)) {
            $gen = new Ac_Cg_Generator();
            $gen->logFileName = false;
            $gen->outputDir = $dir;
            $gen->ovrEditable = false;
            $gen->ovrNonEditable = false;
        }
        $skel->generator = $gen;
        $skel->appName = $this->name;
        $gen->begin();
        $gen->processTemplate($skel);
        $gen->end();
        return $gen;
    }
    
    function cleanCache(& $found, & $files, & $dirs) {
        $res = false;
        $cacheDir = false;
        if ($this->layoutExists()) {
            $cacheDir = $this->getLayout()->getPathVar('pathVarCache', true, true);
        }
        $found = ($cacheDir !== false);
        $files = 0;
        $dirs = 0;
        if ($found) {
            Ac_Cg_Util::cleanDir($cacheDir, $files, $dirs);
            $res = true;
        }
        return $res;
    }

    /**
     * @return Ac_Cg_Generator
     * @param type $genEditable
     * @param type $genNonEditable
     * @param type $deployEditable
     * @param type $deployNonEditable
     */
    function generateCode($genEditable, $genNonEditable, $deployEditable, $deployNonEditable, $skipLint = false) {
        if (!$this->layoutExists()) 
            throw new Ac_E_InvalidUsage("Cannot ".__METHOD__." when app layout not found");
        $layout = $this->getLayout();
        $configFile = $layout->findCodegenConfig();
        if ($configFile === false)
            throw new Ac_E_InvalidUsage("Cannot locate codegen.config.php");
        $gen = new Ac_Cg_Generator($configFile);
        $gen->deployPath = $layout->getPathVar('pathGen', true, true);
        if ($skipLint) $gen->lintify = false;
        foreach (compact('genEditable', 'genNonEditable', 'deployEditable', 'deployNonEditable') as $k => $v)
            $gen->$k = $v;
        $gen->run();
        return $gen;
    }
    
    /**
     * @return Ac_Cg_Generator
     */
    function copyAvancore(Ac_Cg_Generator $gen = null, $srcDir = false) {
        if (!$this->layoutExists() && !$this->layout->hasDefaultCopyTarget()) 
            throw new Ac_E_InvalidUsage("Cannot ".__METHOD__." when app layout not found");
        $avancoreDir = $this->getLayout()->getPathVar('pathAvancore', true);
        $webDir = $this->getLayout()->getPathVar('pathAvancoreAssets');
        if (is_null($gen)) $gen = new Ac_Cg_Generator();
        $gen->syncAvancore($avancoreDir, $webDir, $srcDir, true, true);
        return $gen;
    }
    
    function getDir() {
        if ($this->layout) $res = $this->getLayout()->getPathVar('pathRoot', false, true);
        else $res = false;
        return $res;
    }
        
}