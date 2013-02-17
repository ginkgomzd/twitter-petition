
DROP TABLE IF EXISTS `last_tweet`;

CREATE TABLE `last_tweet` 
(
    `petitioner_key` BIGINT NOT NULL,
    `last_tweeted` DATETIME NOT NULL,
    `tweet` char(140),
    `twitter_handle` varchar(100) NULL,
    PRIMARY KEY (`petitioner_key`)
);

