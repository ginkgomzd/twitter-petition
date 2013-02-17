

SELECT * 
FROM anonymous_petitioners p
LEFT JOIN
last_tweet l on p.id = l.petitioner_key
ORDER BY l.last_tweeted ASC
LIMIT 5
