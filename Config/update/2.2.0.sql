SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- rewriteurl_gone_url
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `rewriteurl_gone_url`;

CREATE TABLE `rewriteurl_gone_url`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `url_source` VARCHAR(255) NOT NULL,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `rewriteurl_gone_url_url_source_unq` (`url_source`),
    INDEX `rewriteurl_gone_url_url_source_idx` (`url_source`)
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;