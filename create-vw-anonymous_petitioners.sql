
DROP VIEW IF EXISTS `anonymous_petitioners`;

CREATE VIEW `anonymous_petitioners` AS
(
  SELECT id, first, CONCAT( SUBSTRING( last from 1 for 1 ), '.') as 'l-initial', city, state
  FROM petitioners
);
