<?php
/**
 * @property Child $app Access to App instance (via Mapper)
 */
class Child_Relation_Base_Object extends Sample_Relation {


    var $_incomingPerson = false;

    var $_outgoingPerson = false;
    
    var $_mapperClass = 'Child_Relation_Mapper';
    
    /**
     * @var Child_Relation_Mapper 
     */
    protected $mapper = false;

    /**
     * @return Child 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * @return Child_Relation_Mapper 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), [ 1 => 'incomingPerson', 2 => 'outgoingPerson', ]));
    }
    
    
    protected function getOwnPropertiesInfo() {
        static $pi = false; 
        if ($pi === false) $pi = [
            'relationType' => [
                'className' => 'Child_Relation_Type',
                'mapperClass' => 'Child_Relation_Type_Mapper',
                'caption' => 'Relation type',
            ],
            'incomingPerson' => [
                'className' => 'Child_Person',
                'mapperClass' => 'Child_Person_Mapper',
                'otherModelIdInMethodsPrefix' => 'incoming',
                'caption' => 'People',
                'idPropertyName' => 'otherPersonId',
                'relationId' => '_incomingPerson',
                'referenceVarName' => '_incomingPerson',
            ],
            'outgoingPerson' => [
                'className' => 'Child_Person',
                'mapperClass' => 'Child_Person_Mapper',
                'otherModelIdInMethodsPrefix' => 'outgoing',
                'caption' => 'People',
                'idPropertyName' => 'personId',
                'relationId' => '_outgoingPerson',
                'referenceVarName' => '_outgoingPerson',
            ],
            'relationId' => [
                'caption' => 'Relation Id',
            ],
            'personId' => [
                'values' => [
                    'mapperClass' => 'Child_Person_Mapper',
                ],
                'assocPropertyName' => 'outgoingPerson',
                'caption' => 'Person Id',
            ],
            'otherPersonId' => [
                'values' => [
                    'mapperClass' => 'Child_Person_Mapper',
                ],
                'assocPropertyName' => 'incomingPerson',
                'caption' => 'Other Person Id',
            ],
            'relationTypeId' => [
                'values' => [
                    'mapperClass' => 'Child_Relation_Type_Mapper',
                ],
                'caption' => 'Relation Type Id',
            ],
            'relationBegin' => [
                'caption' => 'Relation Begin',
            ],
            'relationEnd' => [
                'caption' => 'Relation End',
            ],
            'notes' => [
                'caption' => 'Notes',
            ],
        ];
    
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
    function createRelationType($values = array()) {
        return parent::createRelationType($values);
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
    function createIncomingPerson($values = array()) {
        return parent::createIncomingPerson($values);
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
    function createOutgoingPerson($values = array()) {
        return parent::createOutgoingPerson($values);
    }

    
  
    
}

