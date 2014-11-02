#!/usr/local/bin/php -q
Robo-Tweeting
<?php
error_reporting(E_ALL);

include dirname(__FILE__).'/db-conf.php'; # configure database connection variables
include dirname(__FILE__).'/lib-php-cli-io.php';
include dirname(__FILE__).'/tweet.php';
include dirname(__FILE__).'/tweet_templates.php';
include dirname(__FILE__).'/authentication_tokens.php';

$interval = 60; # 190 seconds
$number_of_petitioners_per_run = 500;

$get_signers_sql = "SELECT * 
FROM anonymous_petitioners p LEFT JOIN last_tweet l on p.id = l.petitioner_key
ORDER BY l.last_tweeted ASC
 LIMIT $number_of_petitioners_per_run;";

$log_tweet_sql = "INSERT INTO last_tweet (`petitioner_key`, `last_tweeted`, `tweet`) VALUES ('%s', NOW(), '%s')
ON DUPLICATE KEY UPDATE `last_tweeted` = VALUES(`last_tweeted`), `tweet` = VALUES(`tweet`);";

### END CONFIG ###

$tweet_templates = get_tweet_templates(); 

$db = new mysqli($HOST, $USER, $PASSWORD, $DB);
if ($db->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}

$signers = $db->query($get_signers_sql);
$count = $signers->num_rows;
printf("# %d signers.\n", $count);

if ($count < 1) die('<br/>nothing to tweet.');

function num_templates() {
  global $tweet_templates;
  return count($tweet_templates);
}

function tpl_pointer($zero = null) {
	$file = dirname(__FILE__).'/tpl_pointer';
	$pointer = 0;

	if (file_exists($file)) {
		$pointer = unserialize(file_get_contents($file));
		$pointer = $pointer+1;
		if ($zero === 0) $pointer = 0;
	}
	file_put_contents($file, serialize($pointer));
	return $pointer;
}

function get_tpl($pntr = NULL) {
  global $tweet_templates;
  $max = num_templates()-1;
  $tpl_pointer = tpl_pointer();
  $tpl_pointer = ($tpl_pointer > $max) ?  tpl_pointer(0) : $tpl_pointer;

  if (is_null($pntr)) {
    return $tweet_templates[$tpl_pointer];
  } else { #return a specific tpl
    while ($pntr > $max ) {
      #emulate infinite tpl's: shutup YAGNI
      $pntr = $pntr - $max; 
    }
    return $tweet_templates[$pntr];
  }
}

function get_tweet($signer) {
/****
 * Returns a tweet using the next template
 * UNLESS, resulting tweet is too long, will try every template
 ****/

  global $tpl_pointer;

  $tweet = sprintf(get_tpl(), $signer['first'], $signer['l-initial'], $signer['city'], $signer['state']);

  if ( strlen($tweet) > 140 ) {
    #try a different template:
    for ($n = 0; $n < num_templates(); $n++) {
      $tweet = sprintf(get_tpl($n), $signer['first'], $signer['l-initial'], $signer['city'], $signer['state']);
      if ( strlen($tweet) < 140 ) return $tweet;
    }
  }

  if ( strlen($tweet) > 140 ) { #still?
    return null;
  }

  return $tweet;
}

while ($signer = mysqli_fetch_array($signers) ) {
    if (file_exists(dirname(__FILE__).'/STOP')) die('STOP signal received');

    $tweet = get_tweet($signer);

    if (is_null($tweet)) {
        $db->query(sprintf($log_tweet_sql, $signer['id'], 'TOO LONG') );
        continue;
    }

    # TWEET TWEET
    $tweet_status = 999;
    $tweet_status = tweet($tweet, get_oauth());
    if ($tweet_status['http status code'] != 200) var_dump($tweet_status);

    #log tweet so we don't repeat ourselves
    $db->query( sprintf($log_tweet_sql, $signer['id'], $tweet) );

    sleep($interval);
}

$signers->close();

?>
