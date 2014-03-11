<?php

$properDbi = array (
      'tables' => array (  
          '#__orientation' => array (    
              'columns' => array (      
                  'sexualOrientationId' => array (        
                      'type' => 'int',
                      'unsigned' => true,
                      'width' => '10',
                      'nullable' => false,
                      'default' => NULL,
                      'autoInc' => true,
                      'name' => 'sexualOrientationId',
                  ),
                  'title' => array (        
                      'type' => 'varchar',
                      'width' => '45',
                      'nullable' => false,
                      'default' => NULL,
                      'name' => 'title',
                  ),
              ),
              'indexes' => array (      
                  'PRIMARY' => array (        
                      'primary' => true,
                      'unique' => true,
                      'columns' => array (          
                          1 => 'sexualOrientationId',
                      ),
                      'name' => 'PRIMARY',
                  ),
                  'Index_2' => array (        
                      'primary' => false,
                      'unique' => false,
                      'columns' => array (          
                          1 => 'title',
                      ),
                      'name' => 'Index_2',
                  ),
              ),
          ),
          '#__people' => array (    
              'columns' => array (      
                  'personId' => array (        
                      'type' => 'int',
                      'unsigned' => true,
                      'width' => '10',
                      'nullable' => false,
                      'default' => NULL,
                      'autoInc' => true,
                      'name' => 'personId',
                  ),
                  'name' => array (        
                      'type' => 'varchar',
                      'width' => '255',
                      'nullable' => false,
                      'default' => NULL,
                      'name' => 'name',
                  ),
                  'gender' => array (        
                      'type' => 'enum',
                      'enumValues' => array (          
                          0 => 'F',
                          1 => 'M',
                      ),
                      'nullable' => false,
                      'default' => 'F',
                      'name' => 'gender',
                  ),
                  'isSingle' => array (        
                      'type' => 'int',
                      'unsigned' => true,
                      'width' => '1',
                      'nullable' => false,
                      'default' => '1',
                      'name' => 'isSingle',
                  ),
                  'birthDate' => array (        
                      'type' => 'date',
                      'nullable' => false,
                      'default' => NULL,
                      'name' => 'birthDate',
                  ),
                  'lastUpdatedDatetime' => array (        
                      'type' => 'datetime',
                      'nullable' => true,
                      'default' => NULL,
                      'name' => 'lastUpdatedDatetime',
                  ),
                  'createdTs' => array (        
                      'type' => 'timestamp',
                      'nullable' => false,
                      'default' => 'CURRENT_TIMESTAMP',
                      'name' => 'createdTs',
                  ),
                  'sexualOrientationId' => array (        
                      'type' => 'int',
                      'unsigned' => true,
                      'width' => '10',
                      'nullable' => true,
                      'default' => NULL,
                      'name' => 'sexualOrientationId',
                  ),
              ),
              'relations' => array (      
                  'FK_ac_people_1' => array (        
                      'table' => '#__orientation',
                      'columns' => array (          
                          'sexualOrientationId' => 'sexualOrientationId',
                      ),
                      'name' => 'FK_ac_people_1',
                  ),
              ),
              'indexes' => array (      
                  'PRIMARY' => array (        
                      'primary' => true,
                      'unique' => true,
                      'columns' => array (          
                          1 => 'personId',
                      ),
                      'name' => 'PRIMARY',
                  ),
                  'FK_ac_people_1' => array (        
                      'primary' => false,
                      'unique' => false,
                      'columns' => array (          
                          1 => 'sexualOrientationId',
                      ),
                      'name' => 'FK_ac_people_1',
                  ),
              ),
          ),
          '#__people_tags' => array (    
              'columns' => array (      
                  'idOfPerson' => array (        
                      'type' => 'int',
                      'unsigned' => true,
                      'width' => '10',
                      'nullable' => false,
                      'default' => NULL,
                      'name' => 'idOfPerson',
                  ),
                  'idOfTag' => array (        
                      'type' => 'int',
                      'unsigned' => true,
                      'width' => '10',
                      'nullable' => false,
                      'default' => NULL,
                      'name' => 'idOfTag',
                  ),
              ),
              'relations' => array (      
                  'FK_ac_people_tags_1' => array (        
                      'table' => '#__people',
                      'columns' => array (          
                          'idOfPerson' => 'personId',
                      ),
                      'name' => 'FK_ac_people_tags_1',
                  ),
                  'FK_ac_people_tags_2' => array (        
                      'table' => '#__tags',
                      'columns' => array (          
                          'idOfTag' => 'tagId',
                      ),
                      'name' => 'FK_ac_people_tags_2',
                  ),
              ),
              'indexes' => array (      
                  'PRIMARY' => array (        
                      'primary' => true,
                      'unique' => true,
                      'columns' => array (          
                          1 => 'idOfPerson',
                          2 => 'idOfTag',
                      ),
                      'name' => 'PRIMARY',
                  ),
                  'FK_ac_people_tags_2' => array (        
                      'primary' => false,
                      'unique' => false,
                      'columns' => array (          
                          1 => 'idOfTag',
                      ),
                      'name' => 'FK_ac_people_tags_2',
                  ),
              ),
          ),
          '#__relation_types' => array (    
              'columns' => array (      
                  'relationTypeId' => array (        
                      'type' => 'int',
                      'unsigned' => true,
                      'width' => '10',
                      'nullable' => false,
                      'default' => NULL,
                      'autoInc' => true,
                      'name' => 'relationTypeId',
                  ),
                  'title' => array (        
                      'type' => 'varchar',
                      'width' => '45',
                      'nullable' => false,
                      'default' => NULL,
                      'name' => 'title',
                  ),
                  'isSymmetrical' => array (        
                      'type' => 'int',
                      'unsigned' => true,
                      'width' => '1',
                      'nullable' => false,
                      'default' => '0',
                      'name' => 'isSymmetrical',
                  ),
              ),
              'indexes' => array (      
                  'PRIMARY' => array (        
                      'primary' => true,
                      'unique' => true,
                      'columns' => array (          
                          1 => 'relationTypeId',
                      ),
                      'name' => 'PRIMARY',
                  ),
              ),
          ),
          '#__relations' => array (    
              'columns' => array (      
                  'relationId' => array (        
                      'type' => 'int',
                      'unsigned' => true,
                      'width' => '10',
                      'nullable' => false,
                      'default' => NULL,
                      'autoInc' => true,
                      'name' => 'relationId',
                  ),
                  'personId' => array (        
                      'type' => 'int',
                      'unsigned' => true,
                      'width' => '10',
                      'nullable' => false,
                      'default' => NULL,
                      'name' => 'personId',
                  ),
                  'otherPersonId' => array (        
                      'type' => 'int',
                      'unsigned' => true,
                      'width' => '10',
                      'nullable' => false,
                      'default' => NULL,
                      'name' => 'otherPersonId',
                  ),
                  'relationTypeId' => array (        
                      'type' => 'int',
                      'unsigned' => true,
                      'width' => '10',
                      'nullable' => false,
                      'default' => NULL,
                      'name' => 'relationTypeId',
                  ),
                  'relationBegin' => array (        
                      'type' => 'datetime',
                      'nullable' => true,
                      'default' => NULL,
                      'name' => 'relationBegin',
                  ),
                  'relationEnd' => array (        
                      'type' => 'datetime',
                      'nullable' => true,
                      'default' => NULL,
                      'name' => 'relationEnd',
                  ),
                  'notes' => array (        
                      'type' => 'text',
                      'nullable' => false,
                      'default' => NULL,
                      'name' => 'notes',
                  ),
              ),
              'relations' => array (      
                  'FK_ac_relations_3' => array (        
                      'table' => '#__relation_types',
                      'columns' => array (          
                          'relationTypeId' => 'relationTypeId',
                      ),
                      'name' => 'FK_ac_relations_3',
                  ),
                  'FK_ac_relations_outgoing' => array (        
                      'table' => '#__people',
                      'columns' => array (          
                          'personId' => 'personId',
                      ),
                      'name' => 'FK_ac_relations_outgoing',
                  ),
                  'FK_ac_relations_incoming' => array (        
                      'table' => '#__people',
                      'columns' => array (          
                          'otherPersonId' => 'personId',
                      ),
                      'name' => 'FK_ac_relations_incoming',
                  ),
              ),
              'indexes' => array (      
                  'PRIMARY' => array (        
                      'primary' => true,
                      'unique' => true,
                      'columns' => array (          
                          1 => 'relationId',
                      ),
                      'name' => 'PRIMARY',
                  ),
                  'FK_ac_relations_3' => array (        
                      'primary' => false,
                      'unique' => false,
                      'columns' => array (          
                          1 => 'relationTypeId',
                      ),
                      'name' => 'FK_ac_relations_3',
                  ),
                  'FK_ac_relations_outgoing' => array (        
                      'primary' => false,
                      'unique' => false,
                      'columns' => array (          
                          1 => 'personId',
                      ),
                      'name' => 'FK_ac_relations_outgoing',
                  ),
                  'FK_ac_relations_incoming' => array (        
                      'primary' => false,
                      'unique' => false,
                      'columns' => array (          
                          1 => 'otherPersonId',
                      ),
                      'name' => 'FK_ac_relations_incoming',
                  ),
              ),
          ),
          '#__tags' => array (    
              'columns' => array (      
                  'tagId' => array (        
                      'type' => 'int',
                      'unsigned' => true,
                      'width' => '10',
                      'nullable' => false,
                      'default' => NULL,
                      'autoInc' => true,
                      'name' => 'tagId',
                  ),
                  'title' => array (        
                      'type' => 'varchar',
                      'width' => '45',
                      'nullable' => false,
                      'default' => NULL,
                      'name' => 'title',
                  ),
                  'titleM' => array (        
                      'type' => 'varchar',
                      'width' => '45',
                      'nullable' => true,
                      'default' => NULL,
                      'name' => 'titleM',
                  ),
                  'titleF' => array (        
                      'type' => 'varchar',
                      'width' => '45',
                      'nullable' => true,
                      'default' => NULL,
                      'name' => 'titleF',
                  ),
              ),
              'indexes' => array (      
                  'PRIMARY' => array (        
                      'primary' => true,
                      'unique' => true,
                      'columns' => array (          
                          1 => 'tagId',
                      ),
                      'name' => 'PRIMARY',
                  ),
                  'Index_2' => array (        
                      'primary' => false,
                      'unique' => true,
                      'columns' => array (          
                          1 => 'title',
                      ),
                      'name' => 'Index_2',
                  ),
              ),
          ),
      ),
);
    
