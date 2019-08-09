ALTER TABLE `ac_shop_meta`
    CHANGE COLUMN `sharedObjectType` `sharedObjectType` VARCHAR(255) NOT NULL DEFAULT 'other' AFTER `metaNoindex`;
