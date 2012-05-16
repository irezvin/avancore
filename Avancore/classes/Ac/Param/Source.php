<?php

/**
 * Abstract source of structured (hierarchical) data.
 * 
 * Each source is like an associative array. 
 * It can contain any number of "keys"; each "key" can contain a value (scalar) or another source.
 * 
 * @abstract
 * @author Ilya Rezvin
 * @package Avancore
 * @subpackage Params
 * @example param/source.php Ac_Param_Source usage example.
 */
abstract class Ac_Param_Source {
    
    /**
     * Path of this source in the parent source.
     * @var string|false
     */
    var $path = false;
    
    /**
     * Parent source.
     * @var Ac_Param_Source|false|null
     * @access private
     */
    protected $parent = false;
    
    /**
     * Data that is used by this source to provide values and sub-sources.
     * @access private
     * @var mixed
     */
    protected $data = false;
    
    /**
     * Array that contains list of child Ac_Param_Source instances.
     * It is used to remove references to this instance (to free memory on destroy() call).
     * @see Ac_Param_Source::destroy()
     * @var array
     * @access private
     */
    var $_sourceInstances = array();
    
    /**
     * Sets reference to the parent of this source.
     * 
     * @param Ac_Param_Source|null $source Reference to the parent parameter source.
     * @param string $key Key of this source in the parent.
     */
    function setParent(Ac_Param_Source $source = null, $path = false) {
        $this->parent = $source;
        $this->path = $path;
    }
    
    /**
     * Returns parent source.
     * @return Ac_Param_Source
     */
    function getParent() {
        return $this->parent;
    }
    
    /**
     * Returns root source.
     * @return Ac_Param_Source
     */
    function getRoot() {
        $res = Ac_Param_Impl_Paths::getRoot($this, 'getParent');
        return $res;
    }
    
    /**
     * Returns path of this source in the sources hierarchy (relative to root).
     * @see Ac_Param_Impl_Paths::getPath
     * @param bool $asString
     * @return string
     */
    function getPath($asString = false) {
        $res = & Ac_Param_Impl_Paths::getPath($this, $asString, 'key', '_parent');
        return $res;
    }
    
    /**
     * Converts array path to the string.
     * 
     * @see Ac_Param_Impl_Paths::pathToString()
     * @param array $arrPath Array path (or string; it won't be converted)
     * @return string
     */
    function pathToString($arrPath) {
        return Ac_Param_Impl_Paths::pathToString($arrPath);
    }
    
    /**
     * Converts string path to the array one.
     * 
     * @see Ac_Param_Impl_Paths::pathToArray()
     * @param string $strPath String path (or array; it won't be converted)
     * @return string
     */
    function pathToArray($strPath) {
        return Ac_Param_Impl_Paths::pathToArray($strPath); 
    }
    
    /**
     * Locates the value (not the source!) by the path.
     *  
     * @param string|array $path Path to the source + last segment of path is the key to return the value
     * @param mixed $default Value to return if the source that contains the target key or the target key not found.
     * @return mixed
     */
    function getByPath($path, $default = null) {
        $res = $default;
        $this->getByPathIfFound($path, $res, true);
        return $res;
    }
    
    /**
     * Locates value or a source by the path; allows to ensure that result was found (since no default value is used). 
     * 
     * @param string|array $path Path to result. Last segment is the searched key (or the source if $mustBeValue is false).  
     * @param mixed $result Result to return if the value is found. If it's not, $result won't be changed. 
     * @param bool $mustBeValue True if result should *not* be Ac_Param_Source and if source is found it will be consifered as *not found*.   
     * @return bool True if target value or source were found 
     */
    function getByPathIfFound($path, & $result, $mustBeValue = true) {
        $a = $this->pathToArray($path);
        $left = array_splice($path, 0, count($path) - 1, array());
        $last = $path[count($path) - 1];
        $src = & Ac_Param_Impl_Paths::getByPath($this, $path, '_parent', 'getSource', 'isSource');
        $res = false;
        if ($src) {
            if ($src->hasKey($last)) {
                if ($mustBeValue) {
                    if (!$src->isSource($last)) {
                        $result = & $src->getSource($last);
                        $res = true;
                    }
                } else {
                    $res = true;
                    if ($src->isSource($last)) {
                        $result = & $src->getSource($last);
                    } else {
                        $result = $src->getKey($last);
                    }
                }
            }
        }   
        return $res;
    }

    /**
     * "Destroys" currents source (unsets all known references to it).
     * 
     * @param bool $recursive Whether to destroy spawned sources also.
     */
    function destroy($recursive) {
        unset($this->_parent);
        foreach (array_keys($this->_sourceInstances) as $k) {
            if ($recursive) $this->_sourceInstances[$k]->destroy(true);
            if (Ac_Util::sameObject($this->_sourceInstances[$k]->_parent, $this)) {
                $this->_sourceInstances[$k]->_parent = null;
            }
            unset($this->_sourceInstances[$k]);
        }
        $this->_doOnDestroy();
    }
    
    /**
     * Template method that is called from destroy().
     * 
     * @see Ac_Param_Source::destroy()
     * @access private
     */
    function _doOnDestroy() {
    }
    
    /**
     * Sets source data.
     * 
     * @param mixed $data
     */
    function setData(& $data) {
        $this->_data = & $data;
    }
    /**
     * @param array $options Configuration array. Can contain keys 'parent', 'key' and 'data' to set respective properties.
     */
    function Ac_Param_Source($options = array()) {
        if (strtolower(get_class($this)) === 'ae_param_source')
            trigger_error("Attempt to instantiate abstract class", E_USER_ERROR);
        if (isset($options['parent']) && array_key_exists('key', $options)) {
            $this->setParent($options['parent'], $options['key']);
            unset($options['parent']);
            unset($options['key']);
        }
        if (array_key_exists('data', $options)) {
            $this->setData($options['data']);
            unset($options['data']);
        }
    }
    
    function getKey($i) {
        if ($i <= 0 || $i >= $this->countKeys()) trigger_error("No such key: \$i in ".$this->getPath(true), E_USER_ERROR);
        else {
            $kk = $this->listKeys();
            $res = $kk[$i];
        }
        return $res;
    }
    
    /**
     * Returns number of keys in this source.
     * @abstract
     * @return int
     */
    function countKeys() {
        trigger_error("Call to abstract method", E_USER_ERROR);
    }
    
    /**
     * Returns all available keys.
     * @abstract
     * @return array
     */
    function listKeys() {
        trigger_error("Call to abstract method", E_USER_ERROR);
    }

    /**
     * Checks whether given key points to a sub-source. Returns false if key is non-existent or points to a value. 
     * @abstract
     * @param string $key
     * @return bool
     */
    function isSource($key) {
        trigger_error("Call to abstract method", E_USER_ERROR);
    }
    
    /**
     * Returns child source by given key. If a key is non-existent or points to a value, error will be triggered.
     * @abstract
     * @return Ac_Param_Source
     * @param string $key
     */
    function getSource($key) {
        trigger_error("Call to abstract method", E_USER_ERROR);
    }

    /**
     * Returns value by given key. If the key points to a source, it's data will be returned in most cases 
     * (actual behavior depends on a concrete implementation).
     * 
     * @see Ac_Param_Source::setData()
     * @abstract
     * @param string $key
     * @return mixed
     */

    abstract function getValue($key);
    
//  function getValue($key)  {
//      trigger_error("Call to abstract method", E_USER_ERROR);
//  }
    
    /**
     * Checks if given key exists in the source.
     * @param string $key
     * @return bool
     */
    function hasKey($key) {
        return in_array($key, $this->listKeys());
    }
    
}

?>