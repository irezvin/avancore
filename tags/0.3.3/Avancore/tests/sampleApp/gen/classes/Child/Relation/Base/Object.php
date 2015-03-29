<?php

class Child_Relation_Base_Object extends Sample_Relation {

    public $_incomingPerson = false;
    public $_outgoingPerson = false;
    
    var $_mapperClass = 'Child_Relation_Mapper';
    
    /**
     * @var Child_Relation_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Child_Relation_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), array ( 1 => 'incomingPerson', 2 => 'outgoingPerson', )));
    }
    
    
    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = array (
            'relationType' => array (
                'className' => 'Child_Relation_Type',
                'mapperClass' => 'Child_Relation_Type_Mapper',
            ),
            'incomingPerson' => array (
                'className' => 'Child_Person',
                'mapperClass' => 'Child_Person_Mapper',
                'otherModelIdInMethodsPrefix' => 'incoming',
                'caption' => 'People',
                'relationId' => '_incomingPerson',
                'referenceVarName' => '_incomingPerson',
            ),
            'outgoingPerson' => array (
                'className' => 'Child_Person',
                'mapperClass' => 'Child_Person_Mapper',
                'otherModelIdInMethodsPrefix' => 'outgoing',
                'caption' => 'People',
                'relationId' => '_outgoingPerson',
                'referenceVarName' => '_outgoingPerson',
            ),
            'personId' => array (
                'values' => array (
                    'mapperClass' => 'Child_Person_Mapper',
                ),
                'objectPropertyName' => 'outgoingPerson',
            ),
            'otherPersonId' => array (
                'values' => array (
                    'mapperClass' => 'Child_Person_Mapper',
                ),
                'objectPropertyName' => 'incomingPerson',
            ),
            'relationTypeId' => array (
                'values' => array (
                    'mapperClass' => 'Child_Relation_Type_Mapper',
                ),
            ),
        );
    
        return $pi;
                
    }
    
        
    
    /**
     * @return Child_Relation_Type 
     */
    function getRelationType() {
        return parent::getRelationType();
    }
    
    /**
     * @param Child_Relation_Type $relationType 
     */
    function setRelationType($relationType) {
        if ($relationType && !is_a($relationType, 'Child_Relation_Type')) 
            trigger_error('$relationType must be an instance of Child_Relation_Type', E_USER_ERROR);
        return parent::setRelationType($relationType);
    }
    
    /**
     * @return Child_Relation_Type  
     */
    function createRelationType($values = array(), $isReference = false) {
        return parent::createRelationType($values, $isReference);
    }

    
        
    
    /**
     * @return Child_Person 
     */
    function getIncomingPerson() {
        return parent::getIncomingPerson();
    }
    
    /**
     * @param Child_Person $incomingPerson 
     */
    function setIncomingPerson($incomingPerson) {
        if ($incomingPerson && !is_a($incomingPerson, 'Child_Person')) 
            trigger_error('$incomingPerson must be an instance of Child_Person', E_USER_ERROR);
        return parent::setIncomingPerson($incomingPerson);
    }
    
    /**
     * @return Child_Person  
     */
    function createIncomingPerson($values = array(), $isReference = false) {
        return parent::createIncomingPerson($values, $isReference);
    }

    
        
    
    /**
     * @return Child_Person 
     */
    function getOutgoingPerson() {
        return parent::getOutgoingPerson();
    }
    
    /**
     * @param Child_Person $outgoingPerson 
     */
    function setOutgoingPerson($outgoingPerson) {
        if ($outgoingPerson && !is_a($outgoingPerson, 'Child_Person')) 
            trigger_error('$outgoingPerson must be an instance of Child_Person', E_USER_ERROR);
        return parent::setOutgoingPerson($outgoingPerson);
    }
    
    /**
     * @return Child_Person  
     */
    function createOutgoingPerson($values = array(), $isReference = false) {
        return parent::createOutgoingPerson($values, $isReference);
    }

    
  
    
}

