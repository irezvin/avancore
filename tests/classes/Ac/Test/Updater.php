<?php 

class Ac_Test_Updater extends Ac_Test_Base {
    
    function testInsertSelect() {
        
        $db = $this->getDb();
        $strSelect = "FROM `#__items` AS `i` WHERE `published` = 1 ORDER BY `modified` DESC";
        $updater = new Ac_Sql_Updater([
            'src' => $strSelect,
            'srcTableAlias' => 'i',
            'destTableName' => 'item_title_cache',
            'colMap' => [
                'id',
                'contentTitle' => 'title',
                'contentData' => 'description',
                'contentModified' => new Ac_Sql_Expression('GREATEST(i.created, i.modified)'),
            ],
            'updateMap' => [
                'id' => false,
                'contentTitle' => true,
                'cacheModified' => 'NOW()'
            ],
            'insertIgnore' => true
        ]);
        
        $this->assertSqlMatch($updater->getExpression($db), $expr = "
            INSERT IGNORE INTO `item_title_cache` (
                `id`,
                `contentTitle`,
                `contentData`,
                `contentModified`
            )
            SELECT
                `i`.`id` AS `id`,
                `i`.`title` AS `contentTitle`,
                `i`.`description` AS `contentData`,
                GREATEST(i.created, i.modified) AS `contentModified`
            FROM `#__items` AS `i` WHERE `published` = 1 ORDER BY `modified` DESC
            ON DUPLICATE KEY UPDATE
                `contentTitle` = VALUES(`contentTitle`),
                `contentData` = VALUES(`contentData`),
                `contentModified` = VALUES(`contentModified`),
                `cacheModified` = NOW()
        ", "Basic upsert statement generated");
        
        $updater->setUpdateColumnsByDefault(false);
        
        $this->assertSqlMatch($updater->getExpression($db), "
            INSERT IGNORE INTO `item_title_cache` (
                `id`,
                `contentTitle`,
                `contentData`,
                `contentModified`
            )
            SELECT
                `i`.`id` AS `id`,
                `i`.`title` AS `contentTitle`,
                `i`.`description` AS `contentData`,
                GREATEST(i.created, i.modified) AS `contentModified`
            FROM `#__items` AS `i` WHERE `published` = 1 ORDER BY `modified` DESC
            ON DUPLICATE KEY UPDATE
                `contentTitle` = VALUES(`contentTitle`),
                `cacheModified` = NOW()
        ", "since `updateColumnsByDefault` is FALSE, columns NOT in updateMap disappeared");
        
        $updater->setUpdateMap([]);
        $updater->setColMap([1 => new Ac_Sql_Expression('i.`1`')], true);
        
        $this->assertSqlMatch($updater->getExpression($db), "
            INSERT IGNORE INTO `item_title_cache` (
                `id`,
                `contentTitle`,
                `contentData`,
                `contentModified`,
                `1`
            )
            SELECT
                `i`.`id` AS `id`,
                `i`.`title` AS `contentTitle`,
                `i`.`description` AS `contentData`,
                GREATEST(i.created, i.modified) AS `contentModified`,
                i.`1` AS `1`
            FROM `#__items` AS `i` WHERE `published` = 1 ORDER BY `modified` DESC
        ", "there's no ON DUPLICATE KEY UPDATE clause when !updateColumnsByDefault && !updateMap");
        
    }
    
    function testUpdaterWithSqlSelect() {
        $db = $this->getDb();
        $sqlSelect = new Ac_Sql_Select([
            'db' => $db,
            'tables' => [
                'i' => ['name' => '#__items'],
            ],
            'where' => ['`published` = 1'],
            'orderBy' => ['`modified` DESC']
            
        ]);
        $updater = new Ac_Sql_Updater([
            'src' => $sqlSelect,
            'srcTableAlias' => 'i',
            'destTableName' => 'item_title_cache',
            'colMap' => [
                'id',
                'contentTitle' => 'title',
                'contentData' => 'description',
                'contentModified' => new Ac_Sql_Expression('GREATEST(i.created, i.modified)'),
            ],
            'updateMap' => [
                'id' => false,
                'contentTitle' => true,
                'cacheModified' => 'NOW()'
            ],
            'insertIgnore' => true
        ]);
        $this->assertSqlMatch($updater->getExpression($db), $expr = "
            INSERT IGNORE INTO `item_title_cache` (
                `id`,
                `contentTitle`,
                `contentData`,
                `contentModified`
            )
            SELECT
                `i`.`id` AS `id`,
                `i`.`title` AS `contentTitle`,
                `i`.`description` AS `contentData`,
                GREATEST(i.created, i.modified) AS `contentModified`
            ".($sqlSelect->getStatementTail(true))."
            ON DUPLICATE KEY UPDATE
                `contentTitle` = VALUES(`contentTitle`),
                `contentData` = VALUES(`contentData`),
                `contentModified` = VALUES(`contentModified`),
                `cacheModified` = NOW()
        ", "upsert with Ac_Sql_Select as `src` works");
        
    }
    
    function testSelectAndBatch() {
        
        $db = $this->getDb();
        $db->query("DROP TABLE IF EXISTS tmp_batch_data");
        $db->query("
            CREATE TEMPORARY TABLE tmp_batch_data AS 
                SELECT 1 AS id, 'First Item' AS title, 1 AS published
                UNION
                SELECT 2 AS id, 'Second Item' AS title, 0 AS published
                UNION
                SELECT 3 AS id, 'Third Item' AS title, 1 AS published
                UNION
                SELECT 4 AS id, 'Fourth Item' AS title, 0 AS published
        ");
        $oldSrc = 'FROM tmp_batch_data WHERE id < 10';
        $updater = new Ac_Sql_Updater([
            'src' => $oldSrc,
            'colMap' => [
                'id',
                'title'
            ],
            'updateMap' => [
                'id' => false,
            ],
            'destTableName' => 'tmp_batch_data',
            'batchCallback' => function($row) {
                if ($row['id'] == '3') return null;
                return ['id' => $row['id']*10, 'title' => 'The '.$row['title']];
            },
            'batchSize' => 2,
        ]);
        $inserts = $updater->getExpression($db, true);
        $this->assertTrue(is_array($inserts), 'getExpression(\$db, \$asArray = true) returns array');
        $this->assertEqual(count($inserts), 2, 'there are several stmts because of batch size');
        $this->assertSqlMatch($inserts[0], "
            INSERT INTO `tmp_batch_data` (
                `id`,
                `title`
            )
            VALUES
                ('10', 'The First Item'),
                ('20', 'The Second Item')
            ON DUPLICATE KEY UPDATE
                `title` = VALUES(`title`)            
        ", 'first batch ok (data modified by callback)');
        
        $this->assertSqlMatch($inserts[1], "
            INSERT INTO `tmp_batch_data` (
                `id`,
                `title`
            )
            VALUES
                ('40', 'The Fourth Item')
            ON DUPLICATE KEY UPDATE
                `title` = VALUES(`title`)            
        ", 'we skip item with id == 3, so second batch contains only 1 item');
        
        $updater->setBatchSize(0);
        
        $inserts = $updater->getExpression($db, true);
        $this->assertTrue(is_array($inserts));
        $this->assertEqual(count($inserts), 1, "setBatchSize(0) => only one batch");
        $this->assertSqlMatch($inserts[0], "
            INSERT INTO `tmp_batch_data` (
                `id`,
                `title`
            )
            VALUES
                ('10', 'The First Item'),
                ('20', 'The Second Item'),
                ('40', 'The Fourth Item')
            ON DUPLICATE KEY UPDATE
                `title` = VALUES(`title`)            
        ", "setBatchSize(0) => only one batch, SQL ok");
        
        
        $updater->setBatchCallbackOnMappedColumns(false);
        // we need to add columns to the source
        $updater->setSrc('SELECT * '.$oldSrc);
        $updater->setBatchCallbackForMany(true);
        $updater->setColMap(['title', 'published']);
        $updater->setBatchCallback(function($rows) {
            $res = [];
            foreach ($rows as $row) if ($row['published']) {
                $row['title'] .= 'Again';
                unset($row['id']);
                $res[] = $row;
            }
            return $res;
        });
        $this->assertSqlMatch($updater->getExpression($db), "
            INSERT INTO `tmp_batch_data` (
                `title`,
                `published`
            )
            VALUES
                ('First ItemAgain', '1'),
                ('Third ItemAgain', '1')
            ON DUPLICATE KEY UPDATE
                `title` = VALUES(`title`),
                `published` = VALUES(`published`)            
        ", "setBatchCallbackOnMappedColumns(False) works");
        
        // test with batchCallback := TRUE
        $updater->setSrc($oldSrc);
        $updater->setBatchCallback(true);
        $updater->setBatchCallbackOnMappedColumns(true);
        $updater->setColMap(['title' => new Ac_Sql_Expression("CONCAT(title, ' Again')")]);
        $this->assertSqlMatch($updater->getExpression($db), "
            INSERT INTO `tmp_batch_data` (
                `title`
            )
            VALUES
                ('First Item Again'),
                ('Second Item Again'),
                ('Third Item Again'),
                ('Fourth Item Again')
            ON DUPLICATE KEY UPDATE
                `title` = VALUES(`title`)
        ", "setBatchCallback(true) works (using batch flow without actual batch callback)");
        
    }
    
    function testDataAndBatch() {
        $db = $this->getDb();
        $updater = new Ac_Sql_Updater([
            'src' => [
                ['Item 1', 1],
                ['Item 2', 0],
                ['Item 3', 1],
                ['Item 4', 0],
            ],
            'destTableName' => '#__items',
            'colMap' => ['title', 'published'],
        ]);
        $this->assertSqlMatch($updater->getExpression($db), "
            INSERT INTO `#__items` (
                `title`,
                `published`
            )
            VALUES
                ('Item 1', '1'),
                ('Item 2', '0'),
                ('Item 3', '1'),
                ('Item 4', '0')
            ON DUPLICATE KEY UPDATE
                `title` = VALUES(`title`),
                `published` = VALUES(`published`)            
        ", "2-d array as `src` works");
        $updater->setBatchCallback(function($row) {
            $row[0] .= ' *';
            return $row;
        });
        $this->assertSqlMatch($updater->getExpression($db), "
            INSERT INTO `#__items` (
                `title`,
                `published`
            )
            VALUES
                ('Item 1 *', '1'),
                ('Item 2 *', '0'),
                ('Item 3 *', '1'),
                ('Item 4 *', '0')
            ON DUPLICATE KEY UPDATE
                `title` = VALUES(`title`),
                `published` = VALUES(`published`)            
        ", "batch callback with 2-d array as `src` works");
    }
    
}