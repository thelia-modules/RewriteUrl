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
    INDEX `rewriteurl_error_url_referer_fi_e0277e` (`rewriteurl_error_url_id`),
    CONSTRAINT `rewriteurl_error_url_referer_fk_e0277e`
        FOREIGN KEY (`rewriteurl_error_url_id`)
            REFERENCES `rewriteurl_error_url` (`id`)
            ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
