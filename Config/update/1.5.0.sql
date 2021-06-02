
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- rewriteurl_rule
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `rewriteurl_rule`;

CREATE TABLE `rewriteurl_rule`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `rule_type` VARCHAR(64) NOT NULL,
    `value` VARCHAR(255),
    `only404` TINYINT(1) NOT NULL,
    `redirect_url` VARCHAR(255) NOT NULL,
    `position` INTEGER(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- rewriteurl_rule_param
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `rewriteurl_rule_param`;

CREATE TABLE `rewriteurl_rule_param`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `id_rule` INTEGER NOT NULL,
    `param_name` VARCHAR(255) NOT NULL,
    `param_condition` VARCHAR(64) NOT NULL,
    `param_value` VARCHAR(255),
    PRIMARY KEY (`id`),
    INDEX `rewriteurl_rule_rule_param_FI_id` (`id_rule`),
    CONSTRAINT `rewriteurl_rule_rule_param_FK_id`
        FOREIGN KEY (`id_rule`)
        REFERENCES `rewriteurl_rule` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;