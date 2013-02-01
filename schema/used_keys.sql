CREATE TABLE IF NOT EXISTS `prefix_jettmail_used_keys` (
    `key` VARCHAR( 40 ) NOT NULL ,
    `expires` DATE NOT NULL ,

    UNIQUE (
    `key`
    )
) ENGINE = MYISAM ;