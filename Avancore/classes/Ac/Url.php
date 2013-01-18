<?php

/**
 * Class for URL handling (parsing, modifying and generating)
 * 
 * @package Avancore Lite
 * @copyright Copyright &copy; 2007, Ilya Rezvin, Avansite (I.Rezvin@avansite.com)
 * @license http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php
 */
 
class Ac_Url {
    
    /**
     * Scheme, i.e. "http://"
     */
    var $scheme = false; 
    
    /**
     * Host name, i.e. "www.example.com"
     */
    var $host = false;
    
    /**
     * Port, i.e. "80"
     */
    var $port = false;
    
    /**
     * Username
     */
    var $user = false;
    
    /**
     * Password
     */
    var $pass = false;
    
    /**
     * Path, i.e. /index.php 
     */
    var $path = false;
    
    /**
     * Part of the path that is 'sef-type' (not interpreted by a server) 
     * @var string
     */
    var $pathInfo = false;
    
    /**
     * Query array (format similar to one returned by parse_str function)
     */
    var $query = array();
    
    /**
     * Fragment id (the string that goes after "#")
     */
    var $fragment = false;
    
    /**
     * @param string strUrl Ac_Url string to populate scheme, host, path etc...
     */
    function Ac_Url($strUrl = false) {
        if ($strUrl !== false) {
            if ($strUrl instanceof Ac_Url) {
                foreach (array_keys(get_class_vars(get_class($this))) as $f) if ($f{0} != '_') {
                    if (isset($strUrl->$f)) $this->$f = $strUrl->$f;
                }
            } else {
                $this->fromString($strUrl);
            }
        }
    }
    
    /**
     * @return Ac_Url
     */
    function & cloneObject() {
        $res = new Ac_Url();
        foreach (get_object_vars($this) as $f => $v) if ($f{0} != '_') $res->$f = $v;
        return $res;
    }
    
    /**
     * @param string strUrl Ac_Url string to populate scheme, host, path etc...
     */
    function fromString($strUrl) {
        static $defs = array('scheme' => false, 'host' => false, 'port' => false, 'user' => false, 'pass' => false, 'path' => false, 'query' => array(), 'fragment' => false);
        if (is_string($strUrl) && strlen($strUrl)) {
            $props = array_merge($defs, parse_url ($strUrl));
            if (!$props['query']) $props['query'] = array(); else parse_str($props['query'], $props['query']);
            foreach($props as $propName=>$propValue) $this->$propName = $propValue;
        }
    }

    /**
     * @returns string queryString part of URL in string format (using $this->query array as a source)
     */
    function getQueryString() {
        return Ac_Url::array2queryString($this->query);
    }
    
    /**
     * sets pathInfo part of URL; if $pathInfo wasn't set before, and $path ends with it, strips
     * $pathInfo from $path
     *
     * <code>
     * 		$u = new Ac_Url("http://example.com/foo/bar/baz");
     * 
     * 		echo $u->path; 
     * 		// 		"/foo/bar/baz"
     * 
     * 		$u->setPathInfo("/bar/baz");
     * 
     * 		echo $u->path; 
     * 		// 		"/foo"
     * 
     * 		echo $u->pathInfo;
     * 		// 		"/bar/baz"
     * 
     * 		echo $u->toString();
     * 		// 		"http://example.com/foo/bar/baz"
     * 
     * </code>
     */
    function setPathInfo($pathInfo) {
        if (!strlen($this->pathInfo) && (substr($this->path, -strlen($pathInfo)) == $pathInfo))
            $this->path = substr($this->path, 0, strlen($this->path) - strlen($pathInfo));
        $this->pathInfo = $pathInfo;
    }
    
    function __toString() {
        return $this->toString();
    }
    
    /**
     * @returns string representation of URL
     */
    function toString($withQuery = true) {
        
        if ($withQuery && function_exists('sefRelToAbs')) {
            $lUri  = strlen($this->scheme) ? $this->scheme.':'.((strtolower($this->scheme) == 'mailto') ? '':'//') : '';
            $lUri .= strlen($this->user) ? $this->user.(strlen($this->pass)? ':'.$this->pass:'').'@':'';
            $lUri .= strlen($this->host) ? $this->host : '';
            $lUri .= strlen($this->port) ? ':'.$this->port : '';
            $lUri .= strlen($this->path) ? $this->path : '';
            $lUri .= strlen($this->pathInfo) ? $this->pathInfo : '';
            
            $rUri = '';
            if (!strlen($this->path) && !strlen($this->pathInfo) && (($withQuery && $this->query) || strlen($this->fragment)) && substr($uri, -1) !== '/') $uri .= '/';
            if ($withQuery && $this->query) {
                $rUri .= '?'.http_build_query($this->query);
//                if (function_exists('http_build_query')) $rUri .= '?'.http_build_query($this->query);
//                    else $rUri .= $withQuery && $this->query ? '?'.(Ac_Url::array2queryString($this->query)) : '';
            }
            $rUri .= $this->fragment ? '#'.$this->fragment : '';
            if (strlen($lUri)) {
                if ($lUri === $GLOBALS['mosConfig_live_site']."/index.php") {
                    $rUri = "index.php".$rUri;
                    $uri = sefRelToAbs($rUri);
                } else {
                    $uri = $lUri.$rUri;
                }
            }
            
        } else {
            $uri  = strlen($this->scheme) ? $this->scheme.':'.((strtolower($this->scheme) == 'mailto') ? '':'//') : '';
            $uri .= strlen($this->user) ? $this->user.(strlen($this->pass)? ':'.$this->pass:'').'@':'';
            $uri .= strlen($this->host) ? $this->host : '';
            $uri .= strlen($this->port) ? ':'.$this->port : '';
            $uri .= strlen($this->path) ? $this->path : '';
            $uri .= strlen($this->pathInfo) ? $this->pathInfo : '';
            if (!strlen($this->path) && !strlen($this->pathInfo) && (($withQuery && $this->query) || strlen($this->fragment)) && substr($uri, -1) !== '/') $uri .= '/';
            //$uri .= $withQuery && strlen($this->query) ? '?'.(Ac_Url::array2queryString($this->query)) : '';
            if ($withQuery && $this->query) {
                $uri .= '?'.http_build_query($this->query);
            }
            $uri .= $this->fragment ? '#'.$this->fragment : '';
        }
        
        return $uri;
    }

    /**
     * Converts array representation of the query string to array
     * This function can be called statically
     * 
     * @copyright  roberlamerma at gmail dot com, linus at flowingcreativity dot net, i dot rezvin at avansite dot com ;-))
     */ 
    function array2queryString($arr_request, $var_name = '', $separator='&') {
        $ret = "";
        if (is_array($arr_request)) {
            foreach ($arr_request as $key => $value) {
                if (is_object($value) && is_a($value, 'Ac_Url')) $value = $value->toString();
                if (is_array($value)) {
                    if ($var_name) {
                        $ret .= Ac_Url::array2queryString($value, "{$var_name}[{$key}]", $separator);
                    } else {
                        $ret .= Ac_Url::array2queryString($value, "{$key}", $separator);
                    }
                } else {
                    if ($var_name) {
                        $ret .= "{$var_name}[{$key}]=".urlencode($value)."&";
                    } else {
                        $ret .= "{$key}=".urlencode($value)."&";
                    }
                }
            }
        }
        if (!$var_name) {
            $ret = substr($ret,0,-1);
        }
        return $ret;
    }
    
    /**
     * Converts array representation of the query string to a string - set of hidden fields that can be used in the form 
     * For example:
     * <code>
     *      $url->query = array('foo' => 'bar', 'baz' => array('quux' => 'moo'));
     *      echo $url->getHidden();
     *      // will output:
     *      // &lt;input type="hidden" name="foo" value="bar" /&gt;
     *      // &lt;input type="hidden" name="baz[quux]" value="moo" /&gt;
     * </code>
     * 
     * @param mixed query if null (by default), $this->query will be taken. If query is provided, the function can be called statically.
     * @param string var_name prefix to put before every var name
     * @param string glue string to put between input's
     */    
    function getHidden($query = null, $var_name="", $glue = "\n") {
        return self::queryToHidden($this->query, $var_name, $glue);
    }
    
    static function queryToHidden($query, $var_name = "", $glue = "\n") {
        $ret = "";
        if (is_array($query)) {
            foreach ($query as $key => $value) {
                if (is_object($value) && is_a($value, 'Ac_Url')) $value = $value->toString();
                if (is_array($value)) {
                    if ($var_name) {
                        $ret .= self::queryToHidden($value, "{$var_name}[{$key}]");
                    } else {
                        $ret .= self::queryToHidden($value, "{$key}");
                    }
                } else {
                    if ($var_name) 
                        $nm = htmlspecialchars("{$var_name}[{$key}]");
                    else
                        $nm = htmlspecialchars($key);
                    $ret .= "<input type=\"hidden\" name=\"$nm\" value=\"".htmlspecialchars($value)."\" />".$glue;
                }
            }
        }
        if (!$var_name) {
            $ret = substr($ret,0,strlen($ret)-strlen($glue));
        }
        return $ret;
    }
    
    function toSingleButtonForm($buttonAttribs = array(), $formAttribs = array()) {
        $buttonAttribs = Ac_Util::m(array('type' => 'submit'), $buttonAttribs);
        $formAttribs = Ac_Util::m(array('method' => 'post', 'action' => $this->toString(false)), $formAttribs);
        $res = "<form ".Ac_Util::mkAttribs($formAttribs).">".$this->getHidden()."<input ".Ac_Util::mkAttribs($buttonAttribs)." /></form>";
        return $res;
    }

    function getJsPostRedirect($formName = false) {
        static $ctr = 0; 
        if (!$formName) $formName = "avcUrlGetJsPostRedirect".$ctr++;
        $res = '<form name="'.htmlspecialchars($formName).'" action="'.$this->toString(false).'" method="post">'.$this->getHidden(null, "", "").'</form>';
        $res .= '<script type="text/javascript">document.forms["'.addslashes($formName).'"].submit();</script>';
        return $res;
    }
    
    /**
     * Guesses and returns URL of current script
     * Note that it takes $query part from REQUEST_URI (so it will ignore various manipulations with $_GET array)
     * 
     * @return Ac_Url
     */
    static function guess($withPathInfo = false) {
        $protocol = explode('/', strtolower($_SERVER['SERVER_PROTOCOL']));
        $res = new Ac_Url($protocol[0].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        if ($withPathInfo) {
            if (isset($_SERVER['PATH_INFO'])) {
                $myPathInfo = $_SERVER['PATH_INFO'];
                $res->path = $_SERVER['SCRIPT_NAME'];
                if (isset($_SERVER['PATH_INFO'])) $res->pathInfo = $_SERVER['PATH_INFO'];
            } else {
                $sn = explode('/', $_SERVER['SCRIPT_NAME']);
                $ru = $_SERVER['REQUEST_URI'];
                if (isset($_SERVER['QUERY_STRING']) && ($l = strlen($_SERVER['QUERY_STRING']))) $ru = substr($ru, 0, strlen($ru) - $l - 1);
                $ru = explode('/', $ru);
                $path = array();
                $pathInfo = array();
                $maxLen = min(count($sn), count($ru));
                for ($i = 0; $i < $maxLen && ($sn[$i] == $ru[$i]); $i++) {
                    $path[] = $sn[$i];
                    unset($ru[$i]);
                }
                $res->path = implode('/', $path);
                if (count($ru)) $res->pathInfo = implode('/', $ru);
                if (substr($res->path, -1) !== '/' && ($res->pathInfo !== false && substr($res->pathInfo, 0, 1) !== '/')) {
                    $res->path = $res->path. '/';
                }
            }
        }
        return $res;
    }
    
}
