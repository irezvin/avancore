<?php

class Ae_Param_Std {

    private static $i = null;
    
    var $defaults = array();
    
    function __construct(array $extraDefaults = array()) {
        $this->defaults = array(
        
			'simpleString' => array(
                'description' => 'Simple string (trimmed, no tags allowed)',
				'filters' => array('f1' => array(
					'class' => 'Ae_Param_Decorator',
					'decorator' => array(
						'class' => 'Ae_Decorator_String',
						'stripTags' => true,
						'trim' => true,
					),
				)),
			),
            
			'natural' => array(
			    'description' => 'Natural number, greater than zero',
				'conditions' => array('c1' => array(
					'class' => 'Ae_Param_Condition_Number',
					'type' => Ae_Param_Condition_Number::typeInt,
					'gt' => 0,
				)),
			),
            
			'enum' => array(
				'conditions' => array('c1' => array(
					'class' => 'Ae_Param_Condition_Enum',
					'values' => array(), 
				)),
			),
            
            'bool' => array(
                'filters' => array(
                    'f1' => array(
                        'class' => 'Ae_Param_Decorator',
                        'decorator' => array(
                            'class' => 'Ae_Decorator_Cast',
                            'type' => 'bool',
                        ),
                    ),
                ),
            ),
        
        );
        Ae_Util::ms($this->defaults, $extraDefaults);
    }
    
    function enum($values, array $overrides = array()) {
        $overrides = Ae_Util::m(
            array('conditions' => array('c1' => array('values' => $values))),
            $overrides
        );
        $res = $this->get('enum', $overrides);
        return $res;
    }
    
    function get($paramName, array $overrides = array()) {
        $res = isset($this->defaults[$paramName])? $this->defaults[$paramName] : array();
        if ($overrides) $res = $res? Ae_Util::m($res, $overrides) : $overrides;
        return $res;
    }
    
    /**
     * @return Ae_Param_Std
     */
    static function i() {
        if (!Ae_Param_Std::$i) Ae_Param_Std::$i = new Ae_Param_Std();
        return  Ae_Param_Std::$i;
    }
    
}