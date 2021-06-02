
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- rewriting_redirect_type
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `rewriting_redirect_type`
(
    `id` INTEGER NOT NULL,
    `httpcode` INTEGER,
    PRIMARY KEY (`id`),
    CONSTRAINT `rewriting_redirect_type_FK_1`
        FOREIGN KEY (`id`)
        REFERENCES `rewriting_url` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;