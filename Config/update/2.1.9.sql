SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- rewriteurl_error_url
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `rewriteurl_error_url`;

CREATE TABLE `rewriteurl_error_url`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `url_source` VARCHAR(255) NOT NULL,
    `count` INTEGER NOT NULL,
    `user_agent` VARCHAR(255) NOT NULL,
    `rewriteurl_rule_id` INTEGER,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `fi_riteurl_error_url_param_FK_id` (`rewriteurl_rule_id`),
    CONSTRAINT `rewriteurl_error_url_param_FK_id`
        FOREIGN KEY (`rewriteurl_rule_id`)
            REFERENCES `rewriteurl_rule` (`id`)
            ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
