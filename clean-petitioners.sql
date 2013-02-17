

DELETE FROM petitioners 
WHERE
last like '%@%'
OR first like '%@%'
;

DELETE FROM petitioners 
WHERE 
first like '%&#32%'
;

DELETE FROM petitioners
WHERE
first like '%http%'
or last like '%http%'
;
