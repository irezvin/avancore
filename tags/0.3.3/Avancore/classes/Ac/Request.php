<?php
    
// TODO: function overrideWithUrl($url) - will set values in server & get
// TODO: integrate with Ac_Application
// TODO: add FILES?

/**
 * 
 */

/**
 * @property Ac_Request_Accessor $value
 * @property Ac_Request_Accessor $get
 * @property Ac_Request_Accessor $post
 * @property Ac_Request_Accessor $server
 * @property Ac_Request_Accessor $env
 * @property Ac_Request_Accessor $cookie
 * @property Ac_Request_Accessor $request
 */
class Ac_Request {
    
    protected $get = false;
    protected $post = false;
    protected $server = false;
    protected $env = false;
    protected $cookie = false;
    protected $request = false;
    
    protected $fullOverrides = array();
    
    protected $stripSlashes = false;
    
    private static $map = array(
        'get' => '_GET',
        'post' => '_POST',
        'cookie' => '_COOKIE',
        'request' => '_REQUEST',
        'server' => '_SERVER',
        'env' => '_ENV',
    );
    
    const get = 'get';
    const post = 'post';
    const cookie = 'cookie';
    const request = 'request';
    const server = 'server';
    const env = 'env';
    
    protected $accessors = array();

    /**
     * @param bool|null $stripSlashesGPC Strip slashes from global arrays GET, COOKIE & REQUEST
     * If NULL is provided, value depending on ini_get('magic_quotes_gpc') will be used
     */
    function __construct($stripSlashesGPC = null) {
        if ($stripSlashesGPC === null) $this->stripSlashes = (bool) ini_get('magic_quotes_gpc');
            else $this->stripSlashes = (bool) $stripSlashesGPC;
    }
    
    function __isset($name) {
        return $name == 'value' || in_array($name, array_merge(self::$map));
    }
    
    function __get($name) {
        if ($name == 'value' || array_key_exists($name, self::$map)) {
            if (!isset($this->accessors[$name])) 
                $this->accessors[$name] = new Ac_Request_Accessor($this, $name == 'value'? false : $name, in_array($name, array('server', 'env')));
            return $this->accessors[$name];
        } else throw Ac_E_InvalidCall::noSuchProperty ($this, $name, array_keys(self::$map));
    }
    
    protected $defaultSrc = array('post', 'get', 'cookie');
    
    function getFullOverride($src) {
        if (!isset(self::$map[$src])) throw Ac_E_InvalidCall::outOfSet('src', $src, array_keys(self::$map));
        return isset($this->fullOverrides[$src]);
    }
    
    function setIsOverride($src, $fullOverride = true) {
        if (!isset(self::$map[$src])) throw Ac_E_InvalidCall::outOfSet('src', $src, array_keys(self::$map));
        if ($fullOverride) $this->fullOverrides[$src] = true;
            else unset($this->fullOverrides[$src]);
    }
    
    function setValues($src, array $values, $isOverride = false) {
        if (!isset(self::$map[$src])) throw Ac_E_InvalidCall::outOfSet('src', $src, array_keys(self::$map));
        $this->$src = $values;
        $this->setIsOverride($src, $isOverride);
    }
    
    function setValueByPath($src, $path, $value) {
        if (!isset(self::$map[$src])) throw Ac_E_InvalidCall::outOfSet('src', $src, array_keys(self::$map));
        if (!is_array($this->$src)) $this->$src = array();
        if (!is_array($path)) $this->{$src}[$path] = $value;
            else Ac_Util::setArrayByPath ($this->$src, $path, $value);
    }

    /**
     * Important! Deletes override entry only; if full override isn't enabled by $isOverride, 
     * returning data will revert back to the original global value. 
     */
    function unsetValueByPath($src, $path) {
        if (!isset(self::$map[$src])) throw Ac_E_InvalidCall::outOfSet('src', $src, array_keys(self::$map));
        if (is_array($this->$src)) {
            if (!is_array($path)) {
                $foo = & $this->$src;
                unset($foo[$path]);
            }
            else Ac_Util::unsetArrayByPath ($this->$src, $path);
        }
    }
    
    function removeValues($src) {
        if (!isset(self::$map[$src])) throw Ac_E_InvalidCall::outOfSet('src', $src, array_keys(self::$map));
        $this->$src = false;
    }

    /**
     * @param Ac_Request_Src|string $src
     * @param type $path
     * @param type $defaultValue
     * @param type $found
     * @return type
     * @throws type 
     */
    function getValueFrom($src, $path, $defaultValue = null, & $found = false) {
        if (is_object($src) && $src instanceof Ac_Request_Src) {
            $res = $src->getValue($this, $path, $defaultValue, $found);
        } else {
            if (is_array($src)) foreach ($src as $s) {
                $res = $this->getValueFrom($s, $path, $defaultValue, $found);
                if ($found) {
                    break;
                }
            } else {
                if (!isset(self::$map[$src])) throw Ac_E_InvalidCall::outOfSet('src', $src, array_keys(self::$map));
                $res = $this->implGetValue($src, $path, self::$map[$src], !isset($this->fullOverrides[$src]), $found, $defaultValue);
                if ($found) $found = $src;
            }
        }
        return $res;
    }
    
    function getValue($path, $defaultValue = null, & $found = false) {
        if (is_array($path) && isset($path[0]) && is_object($path[0]) && $path[0] instanceof Ac_Request_Src) {
            $src = $path[0];
            $path = array_slice($path, 1);
            if (count($path) == 1) $path = implode('', $path);
        } else {
            $src = $this->defaultSrc;
        }
        $res = $this->getValueFrom($src, $path, $defaultValue, $found);
        return $res;
    }
 
    protected function implGetValue($localArrayName, $path, $globalArrayName, $localfullOverrides, & $found, $defaultValue = null) {
        
        $found = null;
        $srcIsGlob = false;

        if ($this->$localArrayName !== false) {
            $src = & $this->$localArrayName;    
            if ($localfullOverrides) {
                if (is_scalar($path)) {
                    if (array_key_exists($path, $src)) {
                        $res = $src[$path];
                        $found = true;
                    } else {
                        $src = & $GLOBALS[$globalArrayName];
                        $srcIsGlob = true;
                    }
                } else {
                    $res = Ac_Util::getArrayByPath($src, $path, $defaultValue, $found);
                    if (!$found) {
                        $found = null;
                        $src = & $GLOBALS[$globalArrayName];
                        $srcIsGlob = true;
                    }
                }
            }
        } else {
            $src = & $GLOBALS[$globalArrayName];
            $srcIsGlob = true;
        }
        
        if (is_null($found)) {
            if (is_scalar($path)) {
                if (($found = array_key_exists($path, $src))) {
                    $res = $src[$path];
                } else {
                    $res = $defaultValue;
                }
            } else {
                $res = Ac_Util::getArrayByPath($src, $path, $defaultValue, $found);
            }
        }
        if ($this->stripSlashes && $found && $srcIsGlob && in_array($globalArrayName, array('_GET', '_POST', '_COOKIE', '_REQUEST'))) {
            $res = self::stripSlashesRecursive($res);
        }
        
        return $res;
    }
    
    static function stripSlashesRecursive($value) {
        $res = '';
        if (is_string( $value )) {
            $res = stripslashes( $value );
        } else {
            if (is_array( $value )) {
                $res = array();
                foreach ($value as $key => $val) {
                    $res[$key] = self::stripSlashesRecursive( $val );
                }
            } else {
                $res = $value;
            }
        }
        return $res;
    }
    
    /**
     * Checks whether given source definition is a cascade
     * @param string|array|Ac_Request_Src $src 
     */
    static function isCascade($src) {
        if (is_object($src) && $src instanceof Ac_Request) $res = self::isCascade($src);
        elseif (is_string($src)) $res = false;
        elseif (is_array($src)) $res = count($src) == 1;
        else throw Ac_E_InvalidCall::wrongType ('src', $src, array('string', 'array', 'Ac_Request'));
        return $res;
    }
    
    // 
    
    /**
     * Populates the Request with data taken from URL in degree enough to build that URL back using
     * Ac_Url::guess. Also sets GET and, optionally, POST data.
     * 
     * $scriptName may be provided to make future guessing more reliable.
     * 
     * Other SERVER and ENV parameters, also FILES and COOKIE will remain unchanged (to set them proprly,
     * we need RFC-compliant parsing code for HTTP request.
     * 
     * @param string|Ac_Url $url
     * @param string $scriptName If provided, will be used to populate $_SERVER['SCRIPT_NAME'] since it is important for the future pathinfo calculation
     * @param array $postData If provider, REQUEST_METHOD will be POST and post data will be set; otherwise REQUEST_METHOD will be GET
     * @param bool $dontChange Only returns array with overrides, without changing the request
     * 
     * @return array ('get' => array(), 'post' => array(), 'env' => array(), 'server' => array() -- what have (should have) been changed
     */
    function populate($url, $scriptName = false, $postData = false, $dontChange = false) {
        $u = parse_url($url);
        if (is_array($postData)) {
            $post = $postData;
            $rm = 'POST';
        } else {
            $post = array();
            $rm = 'GET';
        }
        $q = isset($u['query'])? $u['query'] : null;
        $serverData = array(
            'SERVER_PROTOCOL' => strtoupper($u['scheme']),
            'HTTP_HOST' => $u['host'].(isset($u['port'])? ':'.$u['port'] : ''),
            'PATH_INFO' => NULL,
            'REQUEST_METHOD' => $rm,
            'REQUEST_URI' => $u['path'].(strlen($q)? '?'.$q : ''),
            'QUERY_STRING' => $q,
        );
        if (strlen($scriptName)) {
            $serverData['SCRIPT_NAME'] = $scriptName;
        } elseif ($scriptName === false) { 
            $scriptName = $this->getValueFrom(self::server, 'SCRIPT_NAME');
        }
        if (($l = strlen($scriptName)) && !strncmp($scriptName, $u['path'], $l)) {
            $serverData['PATH_INFO'] = substr($u['path'], $l);
        }
        if (isset($u['query'])) {
            parse_str($u['query'], $get);
        } else $get = array();
        
        $request = array_merge($get, $post);
        
        $res = array(
            'get' => $get,
            'post' => $post,
            'request' => $request,
            'server' => $serverData,
            'env' => $serverData
        );
        
        if (!$dontChange) {
            $this->setValues(self::server, $serverData, false);
            $this->setValues(self::env, $serverData, false);
            $this->setValues(self::get, $get, true);
            $this->setValues(self::post, $post, true);
            $this->setValues(self::request, $request, true);
        }
        return $res;
    }
    
}