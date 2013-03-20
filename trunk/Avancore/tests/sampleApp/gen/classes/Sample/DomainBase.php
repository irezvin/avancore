<?php 

abstract class Sample_DomainBase extends Ac_Application {

    protected function doGetMapperPrototypes() {
        return array (
              'Sample_Orientation_Mapper' => array (
                  'class' => 'Sample_Orientation_Mapper',
              ),
              'Sample_People_Mapper' => array (
                  'class' => 'Sample_People_Mapper',
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
        );
    }
    
    /**
     * @return Sample_Orientation_Mapper 
     */
    function getSampleOrientationMapper() {
        return $this->getMapper('Sample_Orientation_Mapper');
    }
    
    /**
     * @return Sample_People_Mapper 
     */
    function getSamplePeopleMapper() {
        return $this->getMapper('Sample_People_Mapper');
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
     * @return Sample_Orientation 
     */
    static function Sample_Orientation ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_Orientation 
     */
    function createSampleOrientation () {
        return $this->getMapper('Sample_Orientation_Mapper')->factory();
    }
    
 
    /**
     * @return Sample_People 
     */
    static function Sample_People ($object = null) {
        return $object;
    }
    
    /**
     * @return Sample_People 
     */
    function createSamplePeople () {
        return $this->getMapper('Sample_People_Mapper')->factory();
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
        return $this->getMapper('Sample_Relation_Type_Mapper')->factory();
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
        return $this->getMapper('Sample_Relation_Mapper')->factory();
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
        return $this->getMapper('Sample_Tag_Mapper')->factory();
    }
    

}
