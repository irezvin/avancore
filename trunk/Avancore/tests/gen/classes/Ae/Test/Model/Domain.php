<?php
class Ae_Test_Model_Domain {
 
    /**
     * @return Ae_Test_Model_Orientation_Mapper 
     */
    static function & getAeTestModelOrientationMapper () {
        $res = & Ae_Dispatcher::getMapper('Ae_Test_Model_Orientation_Mapper');
        return $res;
    }
 
    /**
     * @return Ae_Test_Model_People_Mapper 
     */
    static function & getAeTestModelPeopleMapper () {
        $res = & Ae_Dispatcher::getMapper('Ae_Test_Model_People_Mapper');
        return $res;
    }
 
    /**
     * @return Ae_Test_Model_Relation_Type_Mapper 
     */
    static function & getAeTestModelRelationTypeMapper () {
        $res = & Ae_Dispatcher::getMapper('Ae_Test_Model_Relation_Type_Mapper');
        return $res;
    }
 
    /**
     * @return Ae_Test_Model_Relation_Mapper 
     */
    static function & getAeTestModelRelationMapper () {
        $res = & Ae_Dispatcher::getMapper('Ae_Test_Model_Relation_Mapper');
        return $res;
    }
 
    /**
     * @return Ae_Test_Model_Tag_Mapper 
     */
    static function & getAeTestModelTagMapper () {
        $res = & Ae_Dispatcher::getMapper('Ae_Test_Model_Tag_Mapper');
        return $res;
    }

 
    /**
     * @return Ae_Test_Model_Orientation 
     */
    static function & Ae_Test_Model_Orientation (& $object) {
        return $object;
    }
 
    /**
     * @return Ae_Test_Model_People 
     */
    static function & Ae_Test_Model_People (& $object) {
        return $object;
    }
 
    /**
     * @return Ae_Test_Model_Relation_Type 
     */
    static function & Ae_Test_Model_Relation_Type (& $object) {
        return $object;
    }
 
    /**
     * @return Ae_Test_Model_Relation 
     */
    static function & Ae_Test_Model_Relation (& $object) {
        return $object;
    }
 
    /**
     * @return Ae_Test_Model_Tag 
     */
    static function & Ae_Test_Model_Tag (& $object) {
        return $object;
    }

}
?>