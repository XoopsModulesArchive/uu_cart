#
# Table structure for table `buy_user`
#
CREATE TABLE `buy_user` (
    `id`        INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`      VARCHAR(128)    NOT NULL DEFAULT '',
    `name2`     VARCHAR(128)    NOT NULL DEFAULT '',
    `read_it`   VARCHAR(255)             DEFAULT NULL,
    `read2_it`  VARCHAR(255)             DEFAULT NULL,
    `uid`       INT(8)          NOT NULL DEFAULT '0',
    `sex`       TINYINT(1)               DEFAULT '0',
    `pass`      VARCHAR(48)              DEFAULT NULL,
    `entering`  DATE                     DEFAULT NULL,
    `birthday`  DATE                     DEFAULT NULL,
    `email`     VARCHAR(255)    NOT NULL DEFAULT '',
    `zip`       VARCHAR(20)     NOT NULL DEFAULT '',
    `pref`      VARCHAR(5)      NOT NULL DEFAULT '',
    `address`   TEXT,
    `tel`       VARCHAR(60)     NOT NULL DEFAULT '',
    `fax`       VARCHAR(60)              DEFAULT NULL,
    `mobile`    VARCHAR(60)              DEFAULT NULL,
    `sess_id`   VARCHAR(32)              DEFAULT '',
    `point`     INT(8)                   DEFAULT '0',
    `magazine`  TINYINT(1)               DEFAULT '0',
    `l_time`    TIMESTAMP(14)   NOT NULL,
    `falseness` VARCHAR(5)      NOT NULL DEFAULT 'f',
    PRIMARY KEY (`id`, `name`),
    KEY `uid` (`uid`),
    KEY `name1` (`name`),
    KEY `email` (`email`),
    KEY `tel` (`tel`),
    KEY `sess_id` (`sess_id`)
)
    ENGINE = ISAM
    AUTO_INCREMENT = 1;

# --------------------------------------------------------

#
# Table structure for table `goods`
#
CREATE TABLE `goods` (
    `gid`       INT(8) UNSIGNED       NOT NULL AUTO_INCREMENT,
    `gcid`      INT(5) UNSIGNED       NOT NULL DEFAULT '0',
    `top`       VARCHAR(255)          NOT NULL DEFAULT '',
    `sub`       VARCHAR(255)                   DEFAULT NULL,
    `exp`       TEXT                  NOT NULL,
    `package`   MEDIUMINT(8) UNSIGNED          DEFAULT '0',
    `stock`     MEDIUMINT(8) UNSIGNED          DEFAULT '0',
    `price`     MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    `rank`      SMALLINT(2) UNSIGNED  NOT NULL DEFAULT '0',
    `tax`       TINYINT(1)                     DEFAULT '5',
    `images`    VARCHAR(255)                   DEFAULT NULL,
    `addmag`    VARCHAR(255)                   DEFAULT NULL,
    `img_count` INT(8)                         DEFAULT '0',
    `p_type`    VARCHAR(20)                    DEFAULT NULL,
    `on_view`   TINYINT(1)                     DEFAULT '1',
    `on_sale`   TINYINT(1)                     DEFAULT '1',
    `inventory` MEDIUMINT(8)                   DEFAULT '0',
    `cool`      VARCHAR(5)                     DEFAULT 'f',
    `carriage`  SMALLINT(2)                    DEFAULT '1000',
    `hit`       INT(5) UNSIGNED       NOT NULL DEFAULT '0',
    `days`      DATE                           DEFAULT NULL,
    `weight`    MEDIUMINT(8) UNSIGNED          DEFAULT NULL,
    `length`    MEDIUMINT(8) UNSIGNED          DEFAULT NULL,
    PRIMARY KEY (`gid`, `gcid`),
    KEY `price` (`price`),
    KEY `rank` (`rank`)
)
    ENGINE = ISAM
    AUTO_INCREMENT = 1;

# --------------------------------------------------------

#
# Table structure for table `u_pickup`
#
CREATE TABLE u_pickup (
    gid     INT(8) UNSIGNED NOT NULL,
    type    TINYINT(1) DEFAULT '0',
    ondays  DATE,
    offdays DATE,
    PRIMARY KEY (gid)
)
    ENGINE = ISAM;

# --------------------------------------------------------

#
# Table structure for table `goods_category`
#
CREATE TABLE `goods_category` (
    `gcid`    INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `top`     VARCHAR(255)    NOT NULL DEFAULT '',
    `sub`     VARCHAR(255)             DEFAULT NULL,
    `exp`     TEXT            NOT NULL,
    `images`  VARCHAR(255)             DEFAULT NULL,
    `p_type`  VARCHAR(20)              DEFAULT NULL,
    `on_view` TINYINT(1)               DEFAULT '1',
    `on_sale` TINYINT(1)               DEFAULT '1',
    `days`    DATE                     DEFAULT NULL,
    PRIMARY KEY (`gcid`)
)
    ENGINE = ISAM
    AUTO_INCREMENT = 1;

# --------------------------------------------------------

#
# Table structure for table `goods_review`
#
CREATE TABLE `goods_review` (
    `rid`  INT(8)               NOT NULL AUTO_INCREMENT,
    `id`   INT(8) UNSIGNED      NOT NULL DEFAULT '0',
    `gid`  INT(8) UNSIGNED      NOT NULL DEFAULT '0',
    `exp`  TEXT                 NOT NULL,
    `rank` SMALLINT(2) UNSIGNED NOT NULL DEFAULT '0',
    `days` DATE                          DEFAULT NULL,
    PRIMARY KEY (`rid`),
    KEY `id` (`id`),
    KEY `gid` (`gid`)
)
    ENGINE = ISAM
    AUTO_INCREMENT = 1;

# --------------------------------------------------------

#
# Table structure for table `uu_magazine`
#
CREATE TABLE `uu_magazine` (
    `magazineid` SMALLINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
    `bid`        TEXT,
    `dat`        TEXT,
    PRIMARY KEY (`magazineid`)
)
    ENGINE = ISAM
    AUTO_INCREMENT = 1;
# --------------------------------------------------------

#
# Table structure for table `cart_sessions`
#
CREATE TABLE `cart_sessions` (
    `bid`      INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sessions` VARCHAR(32)     NOT NULL DEFAULT '',
    `id`       INT(8)          NOT NULL DEFAULT '0',
    `buy`      MEDIUMINT(8)             DEFAULT '0',
    `gid`      INT(8)          NOT NULL DEFAULT '0',
    `sess_ip`  VARCHAR(15)     NOT NULL DEFAULT '',
    `times`    TIMESTAMP(14)   NOT NULL,
    PRIMARY KEY (`bid`, `sessions`),
    KEY `id` (`id`),
    KEY `gid` (`gid`)
)
    ENGINE = ISAM
    AUTO_INCREMENT = 1;

# --------------------------------------------------------

#
# Table structure for table `buy_table`
#
CREATE TABLE `buy_table` (
    `buy`         INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id`          INT(8) UNSIGNED NOT NULL DEFAULT '0',
    `sessions`    VARCHAR(32)     NOT NULL DEFAULT '',
    `uniq`        VARCHAR(100)    NOT NULL DEFAULT '',
    `gid`         INT(8) UNSIGNED NOT NULL DEFAULT '0',
    `num`         MEDIUMINT(8)    NOT NULL DEFAULT '0',
    `transportid` SMALLINT(2)              DEFAULT NULL,
    `receipt`     INT(8) UNSIGNED          DEFAULT '0',
    `r_day`       DATE                     DEFAULT NULL,
    `msend`       TINYINT(1)               DEFAULT '0',
    `slipnum`     VARCHAR(100)             DEFAULT '0',
    `times`       TIMESTAMP(14)   NOT NULL,
    `p_day`       DATE                     DEFAULT NULL,
    `cool`        SMALLINT(2) UNSIGNED     DEFAULT NULL,
    `carriage`    SMALLINT(2) UNSIGNED     DEFAULT NULL,
    `packages`    SMALLINT(2) UNSIGNED     DEFAULT NULL,
    `postage`     SMALLINT(2) UNSIGNED     DEFAULT NULL,
    `falseness`   VARCHAR(5)      NOT NULL DEFAULT 'f',
    PRIMARY KEY (`buy`, `id`),
    KEY `gid` (`gid`),
    KEY `uniq` (`uniq`),
    KEY `sessions` (`sessions`)
)
    ENGINE = ISAM
    AUTO_INCREMENT = 1;
# --------------------------------------------------------

#
# Table structure for table `buy_addressee`
#
CREATE TABLE `buy_addressee` (
    `buy_addressee` INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sessions`      VARCHAR(32)     NOT NULL DEFAULT '',
    `name`          VARCHAR(128)    NOT NULL DEFAULT '',
    `readit`        VARCHAR(255)             DEFAULT NULL,
    `zip`           VARCHAR(12)     NOT NULL DEFAULT '',
    `pref`          VARCHAR(5)      NOT NULL DEFAULT '',
    `address`       TEXT            NOT NULL,
    `tel`           VARCHAR(20)     NOT NULL DEFAULT '',
    `times`         TIMESTAMP(14)   NOT NULL,
    `uniq`          VARCHAR(100)    NOT NULL DEFAULT '',
    PRIMARY KEY (`buy_addressee`, `sessions`),
    KEY `uniq` (`uniq`),
    KEY `sessions` (`sessions`)
)
    ENGINE = ISAM
    AUTO_INCREMENT = 1;

# --------------------------------------------------------

#
# Table structure for table `u_main_setting`
#
CREATE TABLE `u_main_setting` (
    `sid`          TINYINT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
    `send_out`     VARCHAR(128)        NOT NULL DEFAULT '',
    `use_im`       TINYINT(1) UNSIGNED NOT NULL DEFAULT '3',
    `magicpath`    VARCHAR(128)                 DEFAULT '',
    `thum`         SMALLINT(2)         NOT NULL DEFAULT '90',
    `main`         SMALLINT(2)         NOT NULL DEFAULT '150',
    `pic`          SMALLINT(2)         NOT NULL DEFAULT '240',
    `transport`    TINYINT(1)                   DEFAULT '0',
    `islock`       TINYINT(1)                   DEFAULT '0',
    `carriage`     SMALLINT(2)                  DEFAULT '0',
    `carriagefree` TINYINT(1)                   DEFAULT '0',
    `freeval`      MEDIUMINT(8)                 DEFAULT '10000',
    `setting`      TEXT,
    `usefax`       TINYINT(4)                   DEFAULT '0',
    `faxno`        VARCHAR(20)                  DEFAULT '',
    `usessl`       TINYINT(1)                   DEFAULT '0',
    `policy`       TEXT,
    `bank`         TEXT,
    `welcome`      TEXT,
    `welcometop`   VARCHAR(255)                 DEFAULT NULL,
    `welcometype`  TINYINT(2) UNSIGNED          DEFAULT '0',
    PRIMARY KEY (`sid`)
)
    ENGINE = ISAM
    AUTO_INCREMENT = 1;

# --------------------------------------------------------

#
# Table structure for table `zipcode`
#

CREATE TABLE `zipcode` (
    `zid`   INT(7) UNSIGNED NOT NULL AUTO_INCREMENT,
    `zip`   VARCHAR(7)      NOT NULL DEFAULT '',
    `prefk` VARCHAR(8)      NOT NULL DEFAULT '',
    `cityk` VARCHAR(128)    NOT NULL DEFAULT '',
    `townk` VARCHAR(128)    NOT NULL DEFAULT '',
    `pref`  VARCHAR(8)      NOT NULL DEFAULT '',
    `city`  VARCHAR(128)    NOT NULL DEFAULT '',
    `town`  VARCHAR(128)    NOT NULL DEFAULT '',
    PRIMARY KEY (`zid`),
    KEY `zip` (`zip`),
    KEY `prefk` (`prefk`),
    KEY `pref` (`pref`)
)
    ENGINE = ISAM
    PACK_KEYS = 0
    AUTO_INCREMENT = 1;
