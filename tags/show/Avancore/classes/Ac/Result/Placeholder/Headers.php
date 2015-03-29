<?php

class Ac_Result_Placeholder_Headers extends Ac_Result_Placeholder {
    
    /**
     * @param string $strItem 'Header: value'
     * @return array ($headerNameLowerCased, $headerValue)
     */
    protected function parseHeader($strItem) {
        $iv = explode(':', $strItem, 2);
        if (count($iv) > 1) $res = array(strtolower (trim($iv[0])), ltrim($iv[1]));
        else $res = array('', ltrim($iv[0]));
        return $res;
    }

    /**
     * Returns all header values or values of specified header only
     * 
     * if $headerName is false, returns:
     *    array (
     *        $header1lowercase => array(
     *              $k1 => $header1value1, 
     *              $k2 => $header1value2
     *        ),
     *        $header2lowercase => array...
     *    )
     * 
     * if $headerName is NOT false, returns
     *    array(
     *      $k1 => $headervalue1, 
     *      $k2 => $headervalue2
     *    )
     * or an empty array if header isn't found.
     * 
     * $k1, $k2 keys match respecitve keys in $this->items.
     * 
     * @param string|FALSE $headerName Name of header to search (FALSE to return all headers)
     * @return array Header data
     */
    function getHeaders($headerName = false) {
        $res = array();
        foreach ($this->items as $k => $strItem) {
            list($header, $value) = $this->parseHeader($strItem);
            $res[$header][$k] = $value;
        }
        if ($headerName !== false) {
            $headerName = strtolower($headerName);
            if (isset($res[$headerName])) $res = $res[$headerName];
            else $res = array();
        }
        return $res;
    }

    /**
     * Removes header with specified name
     * @param string $headerName Name of header to remove (case-insensitive)
     * @return array old header values
     */
    function removeHeader($headerName) {
        $res = $this->getHeaders($headerName);
        foreach (array_keys($res) as $k) unset($this->items[$k]);
        return $res;
    }
    
    /**
     * Replaces old header value with new one
     * 
     * @param string|FALSE $headerName Name of header (if omitted, $header is expected to be string and to be in form 'headerName: value')
     * @param string|array $header Either 'header: value' or array('value1', 'value2'); array is allowed only if $headerName is provided
     * 
     * $headerName is case-insensitive
     * @return array Old values of replaced header
     */
    function replaceHeader($header, $headerName = false) {
        if ($headerName === false) {
            if (is_array($header)) throw new Ac_E_InvalidCall("\$headerName must be provided when \$header is array");
            list ($headerName, $header) = $this->parseHeader($header);
        }
        $res = $this->removeHeader($headerName);
        if (is_array($header)) {
            foreach ($header as $v) $this->items[] = $headerName.': '.$v;
        } else {
            $this->items[] = $headerName.': '.$header;
        }
        return $res;
    }
    
    /**
     * Sets all headers in a placeholder.
     * Format of $headers is:
     *    array (
     *        $header1 => array(
     *              $header1value1, 
     *              $header1value2
     *        ), 
     *        $header2 => $header2singleValue.
     *        ...
     *        $numericalKey => 'headerName: value',
     *    )
     * Keys in sub-arrays are ignored.
     * @param array $headers Header data in format returned by self::getHeaders(FALSE); non-array single values are allowed too
     */
    function setHeaders(array $headers) {
        $this->items = array();
        foreach ($headers as $headerName => $v) {
            if (is_numeric($headerName)) $px = "";
            else $px = $headerName.": ";
            if (is_array($v)) foreach ($v as $val) $this->items[] = $px.$val;
            else $this->items[] = $px.$v;
        }
    }
    
}