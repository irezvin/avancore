<?php

class Ac_Test_ModelCondition extends Ac_Test_Base {

    function testCondition() {
        
        $people = Ac_Util::indexArray([
            (object) ['name' => 'Matt', 'employeed' => true, 'salary' => 1900],
            (object) ['name' => 'John', 'employeed' => true, 'salary' => 2000],
            (object) ['name' => 'Jane', 'employeed' => false],
            (object) ['name' => 'Jim',  'employeed' => true, 'salary' => 3000],
        ], 'name', true);
        
        $cond = Ac_Prototyped::factory([
            'class' => \Ac_Model_Condition_PropertyCondition::class,
            'matchAll' => true,
            'conditions' => [
                [
                    'property' => 'employeed',
                    'conditions' => [
                        [
                            'class' => \Ac_Model_Condition_EqualsCondition::class,
                            'value' => true,
                        ]
                    ],
                ],
                [
                    'property' => 'salary',
                    'conditions' => [
                        [
                            'class' => \Ac_Model_Condition_RangeCondition::class,
                            'min' => 2000,
                        ]
                    ],
                ],
            ],
        ]);
        
        $found = [];
        
        foreach ($people as $i => $guy) if ($cond($guy)) $found[$i] = $guy;
        
        $this->assertEqual(array_keys($found), ['John', 'Jim']);
        
    }
    
    function testJsonParser() {
        
        $people = Ac_Util::indexArray([
            (object) ['name' => 'Matt',  'employeed' => true, 'salary' => 1900],
            (object) ['name' => 'John',  'employeed' => true, 'salary' => 2000],
            (object) ['name' => 'Jane',  'employeed' => false],
            (object) ['name' => 'Aaron', 'employeed' => true, 'salary' => 3000],
        ], 'name', true);
        
        $parser = new Ac_Model_Condition_Parser_OmniFilterParser;
        
        $proto = $parser->parseJsonNotation([
            'name' => [ 'rx' => '/a/i' ],
            'salary' => [
                ['empty' => true], 
                ['max' => 2000]
            ]
        ]);

        $cond = Ac_Prototyped::factory($proto);
        
        $found = array_filter($people, $cond);
        
        $this->assertEqual(array_keys($found), ['Matt', 'Jane']);
        
        $proto = $parser->parseFieldsNotation([
            'name' => '/a/i',
            'salary' => ',..2000',
        ]);
        
        $cond = Ac_Prototyped::factory($proto);
        
        $found = array_filter($people, $cond);
        
        $this->assertEqual(array_keys($found), ['Matt', 'Jane']);
        
        $proto = $parser->parseFieldsNotation([
            'name' => 'John',
            'salary' => '2000',
        ]);
        
    }
    
    function testOmniSqlFilter() {
        
        $sel = $this->getSampleApp()->getSamplePersonMapper()->createSqlSelect();
        $sel->setParts(['omni' => new Ac_Sql_Filter_Omni(['id' => 'omni', 'fieldsNotation' => true])]);
        $sel->getPart('omni')->setValue([
            'tags[title]' => 'Ум',
        ]);
        //var_dump(''.$sel);
        
        
    }
    
}
