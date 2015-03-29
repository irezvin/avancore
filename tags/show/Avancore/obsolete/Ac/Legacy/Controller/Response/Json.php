<?php

class Ac_Legacy_Controller_Response_Json extends Ac_Legacy_Controller_Response_Html {
    
    static $skipJsonComments = false;

	var $contentType = 'text/javascript; charset=utf-8';
	
	var $noWrap = true;
	
	var $noHtml = true;
	
	var $jsonComments = '';
	
	protected $innerResponses = array();
	
	var $values = array();

	/**
	 * How inner response' variables are mapped to json variables
	 * Format: array ($localVar => $jsonVar)
	 * If $jsonVar === false, variable won't be in json
	 * 
	 * @var array
	 */
	var $valueMap = array();
	
	/**
	 * Whether extract values from innerResponse that are not declared in it's class
	 * @var bool
	 */
	var $forwardUndeclaredVars = true;
	
	/**
	 * Wehther to replace placeholders in asset library names or not
	 * @var bool
	 */
	var $replacePlaceholders = true;

    var $autoExtractScripts = true;
	
	/**
	 * @return Ac_Legacy_Controller_Response
	 * @param bool|int $index If FALSE, returns last innerResponse
	 */
	function getInnerResponse($index = false) {
	    if ($index === false) $index = count($this->innerResponses) - 1;
	    if (isset($this->innerResponses[$index])) $res = $this->innerResponses[$index];
	       else $res = false;
		return $res;
	}
	
	function setInnerResponse(Ac_Legacy_Controller_Response $innerResponse, $add = false) {
		if (!$add) $this->innerResponses = array();
	    $this->innerResponses[] = $innerResponse;
		$this->update();
	}
	
	function getInnerResponses() {
	    return $this->innerResponses;
	}
	
	function listInnerResponses() {
	    return array_keys($this->innerResponses);
	}
	
    /**
     * Adds javascript to JSONed response
     * @param string $javascript
     * @param bool $before If true, it will be added to beforeScript part, not afterScript
     */
    function addJavascript($javascript, $before = false) {
        if ($before) {
            $this->beforeScript[] = $javascript;
        } else {
            $this->afterScript[] = $javascript;
        }
    }
    
	/**
	 * Converts content of inner response to json
	 */
	function update() {
		$this->content = $this->getJson();
		foreach($this->innerResponses as $innerResponse) {
			foreach (array('expire', 'extraHeaders') as $k) {
				$this->$k = $innerResponse->$k;
			}
		}
	}
	
	function getJson($asArray = false) {

        $allRes = array();
        $jc = $this->jsonComments;
	    foreach ($this->innerResponses as $innerResponse) {
	        
	        $res = array();
			
	        $arr = $innerResponse->toArray();
	        
	        $this->parts = array_merge($this->parts, $innerResponse->parts);
	        
	        if ($innerResponse instanceof Ac_Legacy_Controller_Response_Json && isset($arr['values'])) {
	            $tmp = $arr['values'];
	            unset($arr['values']);
	            $arr = array_merge($arr, $tmp);
	        }
			
			if ($this->forwardUndeclaredVars) 
				$arr = array_merge($arr, self::getUndeclaredVars($innerResponse));
				
			$map = $this->valueMap;
			foreach ($arr as $k => $v) {
				if (isset($map[$k])) {
					$k = $map[$k];
				}
				if (strlen($k)) {
					$res[$k] = $v;
				}
			}
			foreach ($this->values as $k => $v) {
				$res[$k] = $v;
			}
			if (isset($res['jsonComments']) && strlen($res['jsonComments'])) {
                if (!Ac_Legacy_Controller_Response_Json::$skipJsonComments) 
                    $jc .= "\n" .$res['jsonComments'];
			} 
			unset($res['jsonComments']);
			if (isset($res['content'])) {
                $content = $res['content'];
                if ($this->autoExtractScripts) {
                    $cs = self::extractScripts($content);
                    foreach ($cs as $i => $part) {
                        if ($part instanceof Ac_Js_Script) {
                            $res['afterScript'][] = $part;
                            unset($cs[$i]);
                        }
                    }
                    $content = implode('', $cs);
                }
                $res['content'] = new Ac_Js_String($content, true);
            }
    		$allRes[] = $res;
        }
        if (count($allRes) == 1) $allRes = $allRes[0];
        if (!$asArray) {
            $allRes = (string) new Ac_Js_Val($allRes);
            if (!Ac_Legacy_Controller_Response_Json::$skipJsonComments && strlen($jc)) {
                $jc = str_replace("/*", "", $jc);
                $jc = str_replace("*/", "", $jc);
                $allRes .= "\n\n/*\n".$jc."\n*/";
            }
        }
        elseif (strlen($jc)) $allRes['jsonComments'] = $jc;
        return $allRes;
	}

    static function extractScripts($content) {
        $preg = '#(\<\s*script[^>]*\>)|(\<\s*/\s*script\s*>)#i';
        $res = array();
        $left = 0;
        $isScript = false;
        if (preg_match_all($preg, $content, $arr, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            foreach ($arr as $item) {
                $data = substr($content, $left, $item[0][1] - $left);
                if ($isScript) $data = new Ac_Js_Script($data);
                $res[] = $data;
                $isScript = strlen($item[1][0]);
                $left = $item[0][1] + strlen($item[0][0]);
            }
        }
        $data = substr($content, $left);
        if ($isScript) $data = new Ac_Js_Script($data);
        $res[] = $data;
        return $res;
    }
	
	static function getUndeclaredVars($object) {
		$vars1 = array_keys(get_class_vars(get_class($object)));
		$vars2 = get_object_vars($object);
		$res = array();
		foreach (array_diff(array_keys($vars2), $vars1) as $k)
			$res[$k] = $vars2[$k];
			
		return $res;
	}
    
    function mergeWithResponse ($subResponse, $withTitleAndPathway = true, $putContent = false) {
        parent::mergeWithResponse($subResponse, $withTitleAndPathway, $putContent);
        if ($subResponse instanceof Ac_Legacy_Controller_Response_Json) {
            if ($subResponse->forwardUndeclaredVars) {
                $this->forwardUndeclaredVars = true;
                foreach (self::getUndeclaredVars($subResponse) as $k => $v) $this->$k = $v;
            }
           Ac_Util::ms($this->values, $subResponse->values);
        }
    }
    
}