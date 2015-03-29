<?php

class Ac_Param_Std {

    private static $i = null;
    
    var $defaults = array();
    
    function __construct(array $extraDefaults = array()) {
        $this->defaults = array(
        
            'rawString' => array(
            ),
            
			'simpleString' => array(
                //'description' => 'Simple string (trimmed, no tags allowed)',
				'filters' => array('f1' => array(
					'class' => 'Ac_Param_Decorator',
					'decorator' => array(
						'class' => 'Ac_Decorator_String',
						'stripTags' => true,
						'trim' => true,
					),
				)),
			),
            
			'natural' => array(
			    //'description' => 'Natural number, greater than zero',
				'conditions' => array('c1' => array(
					'class' => 'Ac_Param_Condition_Number',
					'type' => Ac_Param_Condition_Number::typeInt,
					'gt' => 0,
				)),
			),
            
			'enum' => array(
				'conditions' => array('c1' => array(
					'class' => 'Ac_Param_Condition_Enum',
					'values' => array(), 
				)),
			),
            
            'bool' => array(
                'filters' => array(
                    'f1' => array(
                        'class' => 'Ac_Param_Decorator',
                        'decorator' => array(
                            'class' => 'Ac_Decorator_Cast',
                            'type' => 'bool',
                        ),
                    ),
                ),
            ),
        
        );
        Ac_Util::ms($this->defaults, $extraDefaults);
    }
    
    function enum($values, array $overrides = array()) {
        $overrides = Ac_Util::m(
            array('conditions' => array('c1' => array('values' => $values))),
            $overrides
        );
        $res = $this->get('enum', $overrides);
        return $res;
    }
    
    function get($paramName, array $overrides = array()) {
        $res = isset($this->defaults[$paramName])? $this->defaults[$paramName] : array();
        if ($overrides) $res = $res? Ac_Util::m($res, $overrides) : $overrides;
        return $res;
    }
    
    function simpleString() {
        return $this->get('simpleString');
    }
    
    function rawString() {
        return $this->get('rawString');
    }
    
    function bool() {
        return $this->get('bool');
    }
    
    function natural() {
        return $this->get('natural');
    }
    
    /**
     * @return Ac_Param_Std
     */
    static function i() {
        if (!Ac_Param_Std::$i) Ac_Param_Std::$i = new Ac_Param_Std();
        return  Ac_Param_Std::$i;
    }
    
}