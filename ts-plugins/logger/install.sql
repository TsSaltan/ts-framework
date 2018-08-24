CREATE TABLE `log` ( 
	`id` INT NOT NULL AUTO_INCREMENT , 
	`type` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , 
	`data` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'JSON' , 
	`date` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
	PRIMARY KEY (`id`)
) ENGINE = InnoDB;