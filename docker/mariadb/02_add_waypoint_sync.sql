ALTER TABLE `gk-waypointy`
  ALTER `kraj` DROP DEFAULT;
ALTER TABLE `gk-waypointy`
  CHANGE COLUMN `kraj` `kraj` VARCHAR(200) NULL COLLATE 'utf8_polish_ci' AFTER `typ`;

ALTER TABLE `gk-waypointy`
  ALTER `lat` DROP DEFAULT,
  ALTER `lon` DROP DEFAULT,
  ALTER `name` DROP DEFAULT,
  ALTER `owner` DROP DEFAULT,
  ALTER `typ` DROP DEFAULT,
  ALTER `link` DROP DEFAULT;
ALTER TABLE `gk-waypointy`
  CHANGE COLUMN `lat` `lat` DOUBLE(8,5) NULL AFTER `waypoint`,
  CHANGE COLUMN `lon` `lon` DOUBLE(8,5) NULL AFTER `lat`,
  CHANGE COLUMN `name` `name` VARCHAR(255) NULL COLLATE 'utf8_polish_ci' AFTER `country`,
  CHANGE COLUMN `owner` `owner` VARCHAR(150) NULL COLLATE 'utf8_polish_ci' AFTER `name`,
  CHANGE COLUMN `typ` `typ` VARCHAR(200) NULL COLLATE 'utf8_polish_ci' AFTER `owner`,
  CHANGE COLUMN `link` `link` VARCHAR(255) NULL COLLATE 'utf8_polish_ci' AFTER `kraj`;


CREATE TABLE `gk-waypointy-sync` (
                                   `service_id` VARCHAR(5) NOT NULL,
                                   `last_update` VARCHAR(15) NULL DEFAULT NULL
)
  COMMENT='Last synchronization time for GC services'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;
