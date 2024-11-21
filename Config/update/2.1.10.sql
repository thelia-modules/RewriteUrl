SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- rewriteurl_error_url_referer
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `rewriteurl_error_url_referer`;

CREATE TABLE `rewriteurl_error_url_referer`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `rewriteurl_error_url_id` INTEGER NOT NULL,
    `referer` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `fi_riteurl_error_url_referer_FK_id` (`rewriteurl_error_url_id`),
    CONSTRAINT `rewriteurl_error_url_referer_FK_id`
        FOREIGN KEY (`rewriteurl_error_url_id`)
            REFERENCES `rewriteurl_error_url` (`id`)
            ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
