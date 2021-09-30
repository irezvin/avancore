<?php

/**
 * Class for URL handling (parsing, modifying and generating)
 */
 
class Ac_Url implements Ac_I_RedirectTarget, JsonSerializable {
    
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
     * @var Ac_UrlMapper_UrlMapper
     */
    protected $urlMapper = null;
    
    /**
     * @param string strUrl Ac_Url string to populate scheme, host, path etc...
     */
    function __construct($strUrl = false) {
        if ($strUrl !== false) {
            if ($strUrl instanceof Ac_Url) {
                foreach (array_keys(get_class_vars(get_class($this))) as $f) if ($f[0] != '_') {
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
        foreach (get_object_vars($this) as $f => $v) if ($f[0] != '_') $res->$f = $v;
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
            $props['query'] = self::parseTrickyQuery($props['query']);
            foreach($props as $propName => $propValue) $this->$propName = $propValue;
        }
    }
    
    /**
     * Extends parse_str by saving ?foo&bar (args without '=') as ['foo' => FALSE, 'bar' => FALSE].
     * Falls back fully to parse_str when no such query arguments exist.
     * Reverse composition is done using Ac_Url::buildTrickyQuery
     */
    static function parseTrickyQuery($query) {
        if (is_array($query)) return $query;
        if (!strlen($query)) return [];
        //$segments = $origSegments = preg_split('/(?:^|&)([^&=]+)(?:&|$)/', $query, -1, PREG_SPLIT_DELIM_CAPTURE);
        $segments = $origSegments = explode('&', $query);
        
        $res = [];
        
        // we save query parameters without '=' as FALSE, so isset() will still detect them
        while(($curr = array_shift($segments)) !== null) {
            if (!strlen($curr)) continue;
            if (strpos($curr, '=') !== false) {
                // fragment of query string without segments that need special handling
                parse_str($curr, $currParsed);
                Ac_Util::ms($res, $currParsed);
                continue;
            }
            // parse the tricky part (without '=')
            
            // not a path?
            if (substr($curr, -1) !== ']') {
                $res[$curr] = false;
                continue;
            }
            
            $firstSecond = explode('[', $curr, 2);
            if (count($firstSecond) == 1) {
                $res[$firstSecond[0]] = false;
                continue;
            }
            
            // substr($firstSecond[1], 0, -1) is needed to strip ']' from last segment
            $path = explode('][', substr($firstSecond[1], 0, -1));
            array_unshift($path, $firstSecond[0]);
            Ac_Util::setArrayByPath($res, $path, false);
        }
        //Ac_Debug::dd(compact('query', 'origSegments', 'res'));
        return $res;
    }
    
    static function buildTrickyQuery(array $query) {
        $res = http_build_query($query);
        
        // our Ac_Url extension parses args w/o '=' as FALSE
        // http_build_query encodes FALSE as 0
        if (strpos($res, '=0') === false) return $res; // nothing to fix
        $vv = Ac_Util::flattenArray($query, -1, '[', '', ']');
        ksort($vv, SORT_DESC);
        foreach ($vv as $key => $item) if ($item === false) {
            $path = urlencode($key);
            $res = str_replace($path.'=0', $path, $res);
        }
        return $res;
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
    
    function getPathWithPathInfo(& $remainingQuery = null) {
        $remainingQuery = $this->query;
        $pathInfo = $this->pathInfo;
        if ($this->urlMapper) {
            // TODO: check if this URL is applicable
            $newPathInfo = $this->urlMapper->moveParamsToString($remainingQuery);
            if (!is_null($newPathInfo)) $pathInfo = $newPathInfo;
        }
//        if (preg_match('#/sc/#', $this->path)) {
//            Ac_Debug::ddd($pathInfo, $this->path);
//        }
        if (strlen($pathInfo)) {
            $res = rtrim($this->path, '/').'/'.ltrim($pathInfo, '/');
        } else {
            $res = $this->path;
        }
        return $res;
    }
    
    /**
     * @returns string representation of URL
     */
    function toString($withQuery = true) {
        $pathWithPathInfo = $this->getPathWithPathInfo($query);
        if (!$withQuery) $query = array();
        $uri  = strlen($this->scheme) ? $this->scheme.':'.((strtolower($this->scheme) == 'mailto') ? '':'//') : '';
        $uri .= strlen($this->user) ? $this->user.(strlen($this->pass)? ':'.$this->pass:'').'@':'';
        $uri .= strlen($this->host) ? $this->host : '';
        $uri .= strlen($this->port) ? ':'.$this->port : '';
        $uri .= strlen($pathWithPathInfo) ? $pathWithPathInfo : '';
        if (!strlen($pathWithPathInfo) && (($query) || strlen($this->fragment)) && substr($uri, -1) !== '/') $uri .= '/';
        if ($query && strlen($q = self::buildTrickyQuery($query))) {
            $uri .= '?'.$q;
        }
        $uri .= $this->fragment ? '#'.$this->fragment : '';
        
        return $uri;
    }

    /**
     * Converts array representation of the query string to string
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
                if (is_null($value)) continue;
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
     * @return Ac_Url
     */
    function stripFilename() {
        $res = clone $this;
        if (strlen($res->path) && substr($res->path, -1) !== '/') $res->path = dirname($res->path).'/';
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
     * @TODO ability to accept Ac_Cr_Request->server as $server
     * 
     * @return Ac_Url
     */
    static function guess($withPathInfo = false, array $server = null) {
        if (!isset($server)) {
            if (!isset($_SERVER) || !isset($_SERVER['REQUEST_URI'])) return new Ac_Url("http://localhost/");
            else $server = $_SERVER;
        }
        $scheme = 'http';
        if (isset($server['REQUEST_SCHEME']) && strlen($server['REQUEST_SCHEME'])) {
            $scheme = $server['REQUEST_SCHEME'];
        } elseif (!empty($server['HTTPS']) && $server['HTTPS'] != 'off') {
            $scheme = 'https';
        }
        
        if (isset($server['HTTP_HOST'])) $host = $server['HTTP_HOST'];
        elseif (isset($server['HTTPS_HOST'])) $host = $server['HTTPS_HOST'];
        elseif (isset($server['SERVER_NAME'])) $host = $server['SERVER_NAME'];
        else $host = 'localhost';
        
        if (isset($server['REQUEST_URI'])) $uri = $server['REQUEST_URI'];
            else $uri = '/';
        
        $res = new Ac_Url($scheme.'://'.$host.$uri);
        
        if ($withPathInfo) {
            $res->pathInfo = self::guessPathInfo($server['REQUEST_URI'], $server['SCRIPT_NAME'], $res->path);
        }
        return $res;
    }
    
    static function guessPathInfo($requestUri, $scriptName, & $resultPath = null) {
        $scriptPath = explode('/', $scriptName);
        $requestPath = explode('?', $requestUri, 2); // strip everything after '?'
        $requestPath = explode('/', $requestPath[0]);
        $path = array();
        $maxLen = min(count($scriptPath), count($requestPath));
        for ($i = 0; $i < $maxLen && ($scriptPath[$i] == $requestPath[$i]); $i++) {
            $path[] = $scriptPath[$i];
            unset($requestPath[$i]);
        }
        $resultPath = implode('/', $path);
        if (count($requestPath)) $pathInfo = implode('/', $requestPath);
        else $pathInfo = '';
        if (substr($resultPath, -1) !== '/' && ($pathInfo !== false && substr($pathInfo, 0, 1) !== '/')) {
            // replace paths like 'http://example.com/foo' with '/foo/', but leave /index.php without trailing backslash
            $fileSpecified = basename($scriptName) == basename($resultPath);
            if (!$fileSpecified) {
                $resultPath = $resultPath.'/';
            } else {
                if (strlen($pathInfo)) {
                    $pathInfo = '/'.$pathInfo;
                }
            }
        }
        return $pathInfo;
    }
    
    function isFullyQualified() {
        return strlen($this->scheme) && strlen($this->host);
    }
    
    function isRelative() {
        return !strlen($this->scheme) && !strlen($this->host) && strlen($this->path) && substr($this->path, 0, 1) !== '/';
    }
    
    /**
     * @return Ac_Url
     */
    function resolve($baseUrl) {
        if ($this->isFullyQualified()) return clone $this;
        if ($baseUrl instanceof Ac_Url) $b = $baseUrl;
        else $b = new Ac_Url($baseUrl);
        $res = clone $this;
        if (!strlen($res->scheme)) $res->scheme = $b->scheme;
        if (!strlen($res->host)) $res->host = $b->host;
        if (substr($res->path, 0, 1) === '/') {
            // nothing to do
            return $res;
        }
        
        // resolve the path
        $path = $b->path;
        if (!strlen($res->path)) {
            $res->path = $path;
            return $res;
        }
        if (substr($path, -1) !== '/') $path = dirname($path);
        $path = str_replace('//', '/', rtrim($path, '/').'/'.ltrim($res->path, '/'));
        if (preg_match('#(^|/)\.\.?(/|$)#', $path)) { // have "/../" or "/.." in the path - must resolve
            $resPathAbsolute = !strlen($path) || substr($path, 0, 1) === '/';
            $path = explode('/', $path);
            $i = 0;
            while ($i < count($path)) {
                $curr = $path[$i];
                if ($curr === '.' || $curr === '') {
                    array_splice($path, $i, 1);
                } elseif ($curr === '..') {
                    if ($i > 1) {
                        array_splice($path, $i - 1, 2);
                        $i--;
                    } else {
                        array_splice($path, $i, 1);
                    }
                } else {
                    $i++;
                }
            }
            $path = implode('/', $path);
            if ($resPathAbsolute) $path = '/'.$path;
        }
        $res->path = $path;
        return $res;
    }
    
    function setUrlMapper(Ac_UrlMapper_UrlMapper $urlMapper) {
        $this->urlMapper = $urlMapper;
    }

    /**
     * @return Ac_UrlMapper_UrlMapper
     */
    function getUrlMapper() {
        return $this->urlMapper;
    }

    function hasBase($baseUrl, & $pathInfo = null) {
        $pathInfo = null;
        if (!$this->isFullyQualified()) {
            return $this->resolve($baseUrl)->hasBase($baseUrl, $pathInfo);
        }
        
        if (!(is_object($baseUrl) && $baseUrl instanceof Ac_Url)) {
            $baseUrl = new Ac_Url($baseUrl);
        }
        
        // compare pre-path parts
        $ok = !strcasecmp($baseUrl->scheme, $this->scheme);
        $ok = $ok && !strcasecmp($baseUrl->port, $this->port);
        $ok = $ok && !strcasecmp($baseUrl->host, $this->host);
        if (!$ok) return false;
        
        // now compare paths
        $myPath = $this->getPathWithPathInfo();
        $basePath = $baseUrl->getPathWithPathInfo();
        $baseLen = strlen($basePath);
        if (strlen($myPath) < $baseLen) return false;
        if (strncmp($myPath, $basePath, $baseLen)) return false;
        
        // common string must end with '/', or the paths must be completely identical
        $lastChar = $myPath[$baseLen - 1];
        if ($lastChar !== '/') {
            $nextChar = substr($myPath, $baseLen, 1);
            if (!($nextChar === '' || $nextChar === '/')) return false;
        }
        
        $pathInfo = substr($myPath, $baseLen);
        
        return true;
    }
    
    /**
     * Tries to guess base URL (site root) from SCRIPT_NAME and REQUEST_URI
     * 
     * @return Ac_Url
     */
    static function guessBase(array $server = null) {
        $res = Ac_Url::guess(true, $server);
        $res->setPathInfo('');
        if (substr($res->path, -1) !== '/') $res->path = dirname($res->path).'/';
        return $res;
    }

    public function jsonSerialize() {
        return ''.$this;
    }

}
