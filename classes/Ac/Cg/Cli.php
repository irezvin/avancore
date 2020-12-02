<?php

class Ac_Cg_Cli extends Ac_Util_Cli {

    const APP_TYPE_NATIVE = 'native';
    const APP_TYPE_JOOMLA = 'joomla';
    const APP_TYPE_AUTO = 'auto';
    
    protected $hint = 'Try \'%1$s --help\' or \'%1$s help\' for more information';
    
    protected $prefix = false;
    
    /**
     * @var Ac_Cg_App
     */
    protected $app = false;
    
    protected $allowedArgs = array(
        'action',
        'app',
        'type',
        'db',
        'tables',
        'avancore-dir',
        'dir',
        'mode',
        'deploy',
        'gen',
        'skip-copy',
        'skip-deploy',
        'skip-lint',
        'help',
    );
    
    function actionHelp() {
        ob_start();
?>
    Usage: {avan} ACTION [arguments...]
    Avancore CLI tool that performs various tasks related to Avancore code generation.    

    Possible ACTION values are (case-insensitive):

            create  - create skeleton of an application
            copy    - create or update the copy of Avancore framework
            gen     - generate or re-generate the model code (and clean cache)
            clean   - clean cache directory
            info    - show information on detected directories and application type
            help    - show this help message
            
    Synopsis:
    
        {avan} [--action=]create [--app=]APP_NAME [--skip-copy=true] 
             [--db=DB_NAME] [--tables=REGEX|true] [--cg[=true]]
             [--type=auto|native|joomla] [--dir=DIR] 

        {avan} [--action=]copy [--avancore-path=DIR]
             [--type=auto|native|joomla] [--dir=DIR]
    
        {avan} [--action=]gen [--mode=all|editable|non-editable] [--skip-deploy[=true]]
             [--type=auto|native|joomla] [--dir=DIR] [--skip-lint[=true]]
             
        {avan} [--action=]clean
        
        {avan} [--action=]info
             
        {avan} [--[action=]]help

    Common arguments:

        --action=ACTION     May be specified instead of positional argument #1.

        --dir=DIR           Application/CMS root (will be calculated automatically 
                            if not provided).

        --type=TYPE         Type of application/CMS to choose code templates:
                            auto   - try to guess automatically
                            native - standalone Avancore application
                            joomla - Joomla CMS

    CREATE arguments:


        --app=APP_NAME      Short name of application. Will be prefix of all app 
                            classes (i.e. 'cms' will result in classes Cms, 
                            Cms_Controller etc). Required argument.

        --db=DB_NAME        Database name (will be put into config/app.config.local.php)

        --tables=TABLES     Which tables to use during initial code generation 
                            (will be put into 'autoTables' option of codegen 
                            configuration)

                            a) PHP-valid regular PCRE expression with starting 
                            and ending delimiters, i.e. '/^app_(product|category)/

                            b) 'true' - default to all tables

        --gen[=true]        Immediately generate the code based on all tables in the 
                            database (requires --db argument)

        --skip-copy[=true]  Don't copy Avancore to the created application skeleton

    COPY arguments:

        --avancore-dir=DIR  Path to the Avancore library (defaults to the directory 
                            where Avancore installation with Ac_Avancore class used 
                            by the current tool is located)

    GEN arguments:

        --mode=MODE         Which files to generate:
                            all            - (default) generate all types of files
                            editable       - generate only editable files
                            non-editable   - generate only non-editable files

        --skip-deploy[=true]
                            
                            Don't copy generated code to the application classes' 
                            and gen' directories (note: 'editable' files are never
                            overwritten)
                            
        --skip-lint[=true]  Don't lint every generated file with PHP interpreter (faster)

    Avancore <?php echo Ac_Avancore::version ?> (c) 2007-<?php echo date('Y'); ?> Ilya Rezvin irezvin@gmail.com
    License: Modified BSD License

<?php
        $res = preg_replace("/^ {4}/m", "", ob_get_clean());
        $res = str_replace("{avan}", $this->prefix, $res);
        echo $res;
    }
    
    function run() {
        if ($this->prefix === false) {
            if (isset($_SERVER['argv'])) $this->prefix = basename($_SERVER['argv'][0]);
                else $this->prefix = "avan";
        }
        
        try {
            if (!$this->args) $this->acceptArgs ();
            if (!isset($this->args['action'])) $this->args['action'] = Ac_Util_Cli::shift ($this->args);
            $action = $this->get('action');
            if (isset($this->args['help']) && $this->args['help'] === true) {
                $action = 'help';
            }
            if (!$action) {
                throw new Ac_E_Cli("--action is required");
            }
            $action = strtolower($action);
            if (!method_exists($this, $method = 'action'.$action)) {
                throw new Ac_E_Cli("wrong action: {$action}");
            }
            $this->$method();
        } catch (Exception $e) {
            $err = fopen("php://stderr", "a");
            $prefix = $this->prefix;
            fputs($err, $prefix.": ".$e->getMessage()."\n");
            if ($e instanceof Ac_E_Cli) fputs($err, sprintf($this->hint, $prefix)."\n");
            $c = $e->getCode();
            die($c? $c : -1);
        }
    }    
    
    function parseArgv(array $argv = array()) {
        if (func_num_args() == 0) $res = parent::parseArgv ();
        else $res = parent::parseArgv($argv);
        foreach ($res as $k => $v) {
            if (!is_numeric($k)) {
                if ($v === 'true') $res[$k] = true;
                if ($v === 'false') $res[$k] = false;
            }
        }
        return $res;
    }
    
    function getDir() {
        if (!$this->args) $this->acceptArgs ();
        $dir = $this->get('dir', null);
        if (is_null($dir)) {
            $dir = getcwd();
        }
        return $dir;
    }
    
    function ensureUnambiguousApp($dontThrow = false) {
        $res = true;
        $app = $this->getApp();
        if (count($fa = $app->getLayout()->foundApps) > 1 && !strlen($this->get('app'))) {
            $res = false;
            if (!$dontThrow) {
                throw new Ac_E_Cli("Please specify which app to use with --app (one of '".implode("', '", $fa)."')");
            }
        }
        return $res;
    }
    
    function actionCreate() {
        if (!isset($this->args['action'])) $this->args['action'] = Ac_Util_Cli::shift ($this->args);
        if (!isset($this->args['app'])) $this->args['app'] = Ac_Util_Cli::shift ($this->args);
        
        $app = $this->getApp();
        
        if (!strlen($app->getName()))
            throw new Ac_E_Cli("--app is required");

        $db = $this->get('db');
        $tables = $this->get('tables');
        
        $gen = $this->get('gen', (bool) $db);
        
        if (!is_bool($gen)) throw Ac_E_InvalidCall::outOfSet('gen', $gen, array('true', 'false'));
        
        $dontCopy = $this->get('skip-copy', false);
        if (!is_bool($dontCopy)) throw Ac_E_InvalidCall::outOfSet('skip-copy', $dontCopy, array('true', 'false'));
        
        $destDir = $this->getDir();
        if ($this->get('dir', null) === null && strlen($app->getLayout()->pathRoot)) {
            $destDir = $app->getLayout()->pathRoot;
        }
        
        // now the real work

        $app->setSkelParams(array(
            'tables' => $tables,
            'dbName' => $db,
        ));
        
        if ($gen) {
            if (!strlen($db) && $app->getSkel()->getDbRequired())
                throw new Ac_E_Cli("--db is required with --cg=true"
                    ." for '".$app->getType()."' app type");
            
            if (!strlen($tables) && $app->getSkel()->getTablesRequired())
                throw new Ac_E_Cli("--tables is required with --cg=true"
                ." for '".$app->getType()."' app type");
        }
        
        $app->createSkel($destDir);
        
        if ($gen) {
            if (!$app->detect($destDir)) throw new Exception ("Cannot detect the generated application");
            $gen = $app->generateCode(true, true, true, true);
            $this->genStats($gen);
        }
        
        if (!$dontCopy) {
            $app->copyAvancore(null, $this->get('avancore-dir', false));
        }
        
    }

    function actionCopy() {
        $app = $this->getApp();
        $app->copyAvancore(null, $this->get('avancore-dir', false));
    }
    
    function actionGen() {
        $skipDeploy = $this->get('skip-deploy', false);
        if (!is_bool($skipDeploy)) throw Ac_E_InvalidCall::outOfSet('skip-deploy', $skipDeploy, array('true', 'false'));
        
        $skipLint = $this->get('skip-lint', false);
        if (!is_bool($skipLint)) throw Ac_E_InvalidCall::outOfSet('skip-lint', $skipLint, array('true', 'false'));
        
        $mode = $this->get('mode', 'all');
        if (!in_array($mode, $a = array('editable', 'non-ediable', 'all')))
            throw Ac_E_InvalidCall::outOfSet('mode', $mode, $a);
            
        $genEditable = $mode == 'all' || $mode == 'editable';
        $genNonEditable = $mode == 'all' || $mode == 'nonEditable';
 
        $deploy = !$skipDeploy;

        $mode = $this->getApp(true)->generateCode($genEditable, $genNonEditable, $deploy, $deploy, $skipLint);
        $this->getApp()->cleanCache($found, $files, $dirs);
        $this->genStats($mode);
    }

    function actionClean() {
        $this->getApp(true)->cleanCache($found, $files, $dirs);
        if (!$found) throw new Ac_E_Cli("Cache directory not found");
        echo "Deleted {$files} file(s), {$dirs} dir(s)\n";
    }

    function actionInfo() {
        $app = $this->getApp();
        $res = array(
            'avancore' => Ac_Avancore::version,
        );
        if ($app) {
            Ac_Util::ms($res, array(
                'name' => $app->getName()? $app->getName() : null,
                'exists' => $app->layoutExists(),
                'type' => $app->getType(),
                'paths' => $app->getLayout()->getPathVars(),
            ));
        }
        if ($l = $app->getLayout()) {
            Ac_Util::ms($res, $l->getCliInfo());
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
    }
    
    function getTypeArg() {
        $type = $this->args['type'] = $this->get('type', self::APP_TYPE_AUTO);
        if (!in_array($type, $s = array(
            self::APP_TYPE_JOOMLA, 
            self::APP_TYPE_NATIVE, 
            'auto'
        ))) {
            throw Ac_E_InvalidCall::outOfSet('type', $type, $s);
        }
        return $type;
    }
    
    /**
     * @return Ac_Cg_Layout[]
     */
    protected function getPossibleLayouts() {
        $proto = array(
            self::APP_TYPE_NATIVE => array('class' => 'Ac_Cg_Layout_Native'),
            self::APP_TYPE_JOOMLA => array('class' => 'Ac_Cg_Layout_Joomla'),
        );
        $type = $this->getTypeArg();
        if (isset($proto[$type])) $proto = array($type => $proto[$type]);
        $res = Ac_Prototyped::factoryCollection($proto, 'Ac_Cg_Layout');
        return $res;
    }
    
    /**
     * @return Ac_Cg_App
     */
    function getApp($required = false) {
        if ($this->app === false) {
            $this->app = new Ac_Cg_App();
            $dir = $this->getDir();
            $bubble = $this->get('dir', null) === null;
            $appName = $this->get('app');
            if (strlen($appName)) $this->app->setName($appName);
            $detected = $this->app->detect($dir, $poss = $this->getPossibleLayouts(), $bubble, $recommended);
            if (!$detected && !$recommended && isset($poss[self::APP_TYPE_NATIVE])) {
                $recommended = $poss[self::APP_TYPE_NATIVE];
            }
            if (!$detected) {
                if (!$recommended) {
                    $typeArg = $this->getTypeArg();
                    if ($typeArg === self::APP_TYPE_AUTO) {
                        throw new Ac_E_Cli("Cannot detect real app or preferred app type while '".self::APP_TYPE_AUTO."' requested");
                    } else {
                        $recommended = $poss[$typeArg];
                    }
                }
                $this->app->setLayout(clone $recommended);
                if ($required) throw new Ac_E_Cli("Cannot find app in directory '{$dir}'".($bubble? " and its' parents" : ""));
            } else {
                if ($required) $this->ensureUnambiguousApp();
            }
        }
        return $this->app;
    }

    // TODO: detect an app
    function detectDir($start) {
        $curr = $start;
        $res = null;
        do {
            $found = $this->isAvancore($curr);
            if ($found) $res = $curr;
            $prev = $curr;
            $curr = dirname($curr);
        } while (!$found && strlen($curr) && $curr !== $prev);
        return $res;
    }
    
    
    protected function genStats(Ac_Cg_Generator $gen) {
        echo "Generator run complete in ".$gen->getOutputTime()." sec: "
            .$gen->getOutputBytes()." bytes in "
            .$gen->getOutputFiles()." files\n";
    }
    
}