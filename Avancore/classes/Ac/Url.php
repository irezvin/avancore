<?php

/**
 * Class for URL handling (parsing, modifying and generating)
 */
 
class Ac_Url implements Ac_I_RedirectTarget {
    
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
    function __construct($strUrl = false) {
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
    function cloneObject() {
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
            $u = parse_url($strUrl);
            if (is_array($u)) $props = array_merge($defs, $u);
            else $props = $defs;
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
    
    function getTargetUrl() {
        return $this->toString();
    }
    
    /**
     * @returns string representation of URL
     */
    function toString($withQuery = true) {
        
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
        
        return $uri;
    }

    /**
     * Converts array representation of the query string to array
     * This function can be called statically
     * 
     * @copyright  roberlamerma at gmail dot com, linus at flowingcreativity dot net and me
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

    /**
     * @param string $formName
     * @param array $list
     * @param array $listIsForPost
     * @return string
     */
    function getJsPostRedirect($formName = false, $list = array(), $listIsForPost = false) {
        static $ctr = 0; 
        if (!$formName) $formName = "avcUrlGetJsPostRedirect".$ctr++;
        if ($list) {
            $a = array_intersect_key($this->query, $f = array_flip($list));
            $b = array_diff_key($this->query, $f);
            if ($listIsForPost) {
                list ($post, $get) = array($a, $b);
            } else {
                list ($get, $post) = array($a, $b);
            }
            $tmp = $this->query;
            $this->query = $get;
            $action = $this->toString();
            $this->query = $post;
            $hidden = $this->getHidden(null, "", "");
            $this->query = $tmp;
        } else {
            $action = $this->toString(false);
            $hidden = $this->getHidden(null, "", "");
        }
        $res = '<form name="'.htmlspecialchars($formName).'" action="'.$action.'" method="post">'.$hidden.'</form>';
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
        if (!isset($_SERVER)) {
            // TODO: fix me
            return new Ac_Url("http://localhost/");
        }
        $scheme = 'http';
        if (isset($_SERVER['REQUEST_SCHEME']) && strlen($_SERVER['REQUEST_SCHEME'])) {
            $scheme = $_SERVER['REQUEST_SCHEME'];
        } elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $scheme = 'https';
        }
        $res = new Ac_Url($scheme.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
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
