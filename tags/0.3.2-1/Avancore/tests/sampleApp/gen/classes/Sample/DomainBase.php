<?php 

abstract class Sample_DomainBase extends Ac_Application {

    protected function doGetMapperPrototypes() {
        return array (
              'Sample_Orientation_Mapper' => array (
                  'class' => 'Sample_Orientation_Mapper',
              ),
              'Sample_Person_Mapper' => array (
                  'class' => 'Sample_Person_Mapper',
              ),
              'Sample_Relation_Type_Mapper' => array (
                  'class' => 'Sample_Relation_Type_Mapper',
              ),
              'Sample_Relation_Mapper' => array (
                  'class' => 'Sample_Relation_Mapper',
              ),
              'Sample_Tag_Mapper' => array (
                  'class' => 'Sample_Tag_Mapper',
              ),
              'Sample_Tree_Adjacent_Mapper' => array (
                  'class' => 'Sample_Tree_Adjacent_Mapper',
              ),
              'Sample_Tree_Combo_Mapper' => array (
                  'class' => 'Sample_Tree_Combo_Mapper',
              ),
              'Sample_Tree_Record_Mapper' => array (
                  'class' => 'Sample_Tree_Record_Mapper',
              ),
        );
    }
    
    /**
     * @return Sample_Orientation_Mapper 
     */
    function getSampleOrientationMapper() {
        return $this->getMapper('Sample_Orientation_Mapper');
    }
    
    /**
     * @return Sample_Person_Mapper 
     */
    function getSamplePersonMapper() {
        return $this->getMapper('Sample_Person_Mapper');
    }
    
    /**
     * @return Sample_Relation_Type_Mapper 
     */
    function getSampleRelationTypeMapper() {
        return $this->getMapper('Sample_Relation_Type_Mapper');
    }
    
    /**
     * @return Sample_Relation_Mapper 
     */
    function getSampleRelationMapper() {
        return $this->getMapper('Sample_Relation_Mapper');
    }
    
    /**
     * @return Sample_Tag_Mapper 
     */
    function getSampleTagMapper() {
        return $this->getMapper('Sample_Tag_Mapper');
    }
    
    /**
     * @return Sample_Tree_Adjacent_Mapper 
     */
    function getSampleTreeAdjacentMapper() {
        return $this->getMapper('Sample_Tree_Adjacent_Mapper');
    }
    
    /**
     * @return Sample_Tree_Combo_Mapper 
     */
    function getSampleTreeComboMapper() {
        return $this->getMapper('Sample_Tree_Combo_Mapper');
    }
    
    /**
     * @return Sample_Tree_Record_Mapper 
     */
    function getSampleTreeRecordMapper() {
        return $this->getMapper('Sample_Tree_Record_Mapper');
    }
    
 
    /**
     * @return Sample_Orientation 
     */
    static function Sample_Orientation ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Orientation 
     */
    function createSampleOrientation () {
        return $this->getMapper('Sample_Orientation_Mapper')->createRecord();
    }
    
 
    /**
     * @return Sample_Person 
     */
    static function Sample_Person ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Person 
     */
    function createSamplePerson () {
        return $this->getMapper('Sample_Person_Mapper')->createRecord();
    }
    
 
    /**
     * @return Sample_Relation_Type 
     */
    static function Sample_Relation_Type ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Relation_Type 
     */
    function createSampleRelationType () {
        return $this->getMapper('Sample_Relation_Type_Mapper')->createRecord();
    }
    
 
    /**
     * @return Sample_Relation 
     */
    static function Sample_Relation ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Relation 
     */
    function createSampleRelation () {
        return $this->getMapper('Sample_Relation_Mapper')->createRecord();
    }
    
 
    /**
     * @return Sample_Tag 
     */
    static function Sample_Tag ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Tag 
     */
    function createSampleTag () {
        return $this->getMapper('Sample_Tag_Mapper')->createRecord();
    }
    
 
    /**
     * @return Sample_Tree_Adjacent 
     */
    static function Sample_Tree_Adjacent ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Tree_Adjacent 
     */
    function createSampleTreeAdjacent () {
        return $this->getMapper('Sample_Tree_Adjacent_Mapper')->createRecord();
    }
    
 
    /**
     * @return Sample_Tree_Combo 
     */
    static function Sample_Tree_Combo ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Tree_Combo 
     */
    function createSampleTreeCombo () {
        return $this->getMapper('Sample_Tree_Combo_Mapper')->createRecord();
    }
    
 
    /**
     * @return Sample_Tree_Record 
     */
    static function Sample_Tree_Record ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Tree_Record 
     */
    function createSampleTreeRecord () {
        return $this->getMapper('Sample_Tree_Record_Mapper')->createRecord();
    }
    

}