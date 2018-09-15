CREATE TABLE IF NOT EXISTS `cache` ( 
	`key` VARCHAR(255) NOT NULL , 
	`value` TEXT NOT NULL COMMENT 'JSON' , 
	`update` INT NOT NULL , 
	PRIMARY KEY (`key`)
) ENGINE = InnoDB;