<?php

class Ae_Indent extends Ae_Autoparams {

    protected $tmpCache = array();

    protected $minIndent = false;
    protected $isFirstLine = false;
    protected $newSpaces = false;
    protected $spaces = false;

    private static $instance = null;

    /**
     * @var Ae_Indent_Cache
     */
    protected $cache = null;

    /**
     * @return Ae_Indent
     */
    static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Ae_Indent();
            self::$instance->setCache(new Ae_Indent_Cache_Default());
        }
        return self::$instance;
    }

    static function setInstance(Ae_Indent $indent = null) {
        self::$instance = $indent;
    }

    /**
     * @return Ae_Indent_Cache
     */
    function getCache() {
        return $this->cache;
    }

    function setCache(Ae_Indent_Cache $cache = null) {
        $this->cache = $cache;
    }

    function show($text) {
        $ss = debug_backtrace();
        $c = count($ss);
        $indent = false;
        for ($i = 0; $i < $c; $i++ ) {
            $s = $ss[$i];
            if (isset($s['file']) && ($s['file'] !== __FILE__) && isset($s['line'])) {
                $indent = $this->getIndent($s['file'], $s['line']);
                if ($indent !== -1) break;
            }
        }
        if ($indent > 0) {
            $this->showWithIndent($text, $indent);
        } else {
            echo $text;
        }
    }

    function showWithIndent($text, $indent) {
        $this->minIndent = false;
        $text = preg_replace_callback("/^([ \t]*)[^ \t].*$/m", array($this, 'reportIndent'), $text);

        if ($this->minIndent)
            $this->spaces = str_repeat(" ", $this->minIndent);
        else
            $this->spaces = "";

        if ($indent)
            $this->newSpaces = str_repeat(" ", $indent);
        else
            $this->newSpaces = "";
        
        $this->isFirstLine = true;
        
        echo preg_replace_callback("/^.*$/m", array($this, 'replaceIndents'), $text);
    }
    
    function replaceIndents($matches) {
        $res = $matches[0];
        if ($this->isFirstLine) {
            $this->isFirstLine = false;
            if (substr($matches[0], 0, $this->minIndent) === $this->spaces) $res = substr($matches[0], $this->minIndent);
        }
        elseif (substr($matches[0], 0, $this->minIndent) === $this->spaces) $res = $this->newSpaces.substr($matches[0], $this->minIndent);
        return $res;
    }

    function reportIndent($matches) {
        $matches[0] = str_replace("\t", "    ", $matches[0]);
        if (strlen(trim($matches[0], " \n\r"))) {
            $matches[1] = str_replace("\t", "    ", $matches[1]);
            if ($this->minIndent === false) $this->minIndent = strlen($matches[1]);
                else $this->minIndent = min($this->minIndent, strlen($matches[1]));
        }
        return $matches[0];
    }

    static function s($text) {
        Ae_Indent::getInstance()->show($text);
    }

    static function si($indent, $text) {
        Ae_Indent::getInstance()->showWithIndent($indent, $text);
    }

    function getIndent($file, $line) {
        $mtime = filemtime($file);
        $res = false;
        if ($this->cache) {
            $res = $this->cache->getIndent($file, $line, $mtime);
        }
        if ($res === false) {
            $res = $this->getIndentFromFile($file, $line);
            if ($res !== false && $this->cache) $this->cache->addEntry($file, $line, $res);
        }
        return $res;
    }

    function getIndentFromFile($file, $line) {
        $res = false;
        $i = 0;

        if (!isset($this->tmpCache[$file])) {
            $this->tmpCache[$file] = array();
            $lines = preg_grep("/<\\?php/", file($file));
            foreach ($lines as $lineNo => $string) {
                $this->tmpCache[$file][$lineNo + 1] = strpos(str_replace("\t", "    ", $string), "<"."?php");
            }
        }
        if (isset($this->tmpCache[$file][$line])) {
            $res = $this->tmpCache[$file][$line];
        } else {
            $res = -1;
        }

//        if (($f = fopen($file, "r"))) {
//            do {
//                $s = fgets($f);
//                $i++;
//            } while (($s !== false) && ($i < $line));
//            if ($s !== false) {
//                $res = strpos($s, "<"."?php");
//                if ($res === false) $res = -1;
//            }
//            fclose($f);
//        }

//        if (($f = file($file)) && isset($f[$line - 1])) {
//            $res = strpos($f[$line - 1], "<"."?php");
//            if ($res === false) $res = -1;
//        }
        
        return $res;
    }

}