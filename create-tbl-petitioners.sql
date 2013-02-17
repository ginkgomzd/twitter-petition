
DROP TABLE IF EXISTS `petitioners`;

CREATE TABLE `petitioners`
(
  `id` BIGINT AUTO_INCREMENT NOT NULL PRIMARY KEY
, `first` VARCHAR(500)
, `last` VARCHAR(500)
, `city` VARCHAR(100)
, `state` CHAR(2)
);
