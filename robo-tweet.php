#!/usr/bin/php -q
Robo-Tweeting
<?php
error_reporting(E_ALL);

include dirname(__FILE__).'/db-conf.php'; # configure database connection variables
include dirname(__FILE__).'/lib-php-cli-io.php';
include dirname(__FILE__).'/tweet.php';

$tweet_templates = array(
    'Another opponent of #CISPA: %s %s %s @RepMikeRogers @Call_Me_Dutch',
    '%s %s %s Opposes #CISPA  @RepMikeRogers @Call_Me_Dutch',
    'Also against #CISPA: %s %s %s @RepMikeRogers @Call_Me_Dutch',
    '%s %s %s Says #NoCISPA  @RepMikeRogers @Call_Me_Dutch',
    'Dear @RepMikeRogers & @Call_Me_Dutch: #NoCISPA, Thx: %s %s %s',
);

$interval = 15; # seconds
$number_of_petitioners_per_run = 3;

$get_signers_sql = "SELECT * 
FROM anonymous_petitioners p LEFT JOIN last_tweet l on p.id = l.petitioner_key
ORDER BY l.last_tweeted ASC
 LIMIT $number_of_petitioners_per_run;";

$log_tweet_sql = "INSERT INTO last_tweet (`petitioner_key`, `last_tweeted`, `tweet`) VALUES ('%s', NOW(), '%s')
ON DUPLICATE KEY UPDATE `last_tweeted` = VALUES(`last_tweeted`), `tweet` = VALUES(`tweet`);";

function get_oauth() {
  return array(
    'consumer key' => '6R6cvzNuAsHif6l9u3kviw',
    'consumer secret' => 'nUkonnV8t3gS78EVztqoY8djXccrUCZUxIw4PgwSepw',
    'oauth token' => '1186627088-zQnMnLUadWb7abVE91SGXjq4iPhUr8tLu7dBi6T',
    'oauth secret' => 'NYFAM9RhS1p5O6E0Lm0pK7Us6ky9jIl8ijlnzB4XdAk',
  );
}

### END CONFIG ###

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

$tpl_pointer = 0;
function get_tpl($pntr = NULL) {
  global $tpl_pointer;
  global $tweet_templates;
  $max = num_templates()-1;

  if (is_null($pntr)) {
    # use the global pointer
    if ($tpl_pointer > $max) $tpl_pointer = 0;
    return $tweet_templates[$tpl_pointer++];
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

  $tweet = sprintf(get_tpl(), $signer['first'], $signer['l-initial'], $signer['state']);

  if ( strlen($tweet) > 140 ) {
    #try a different template:
    for ($n = 0; $n < num_templates(); $n++) {
      $tweet = sprintf(get_tpl($n), $signer['first'], $signer['l-initial'], $signer['state']);
      if ( strlen($tweet) < 140 ) return $tweet;
    }
  }

  if ( strlen($tweet) > 140 ) { #still?
    return null;
  }

  return $tweet;
}

while ($signer = mysqli_fetch_array($signers) ) {
    $tweet = get_tweet($signer);

    if (is_null($tweet)) {
        $db->query(sprintf($log_tweet_sql, $signer['id'], 'TOO LONG') );
        continue;
    }

    # TWEET TWEET
    $tweet_status = tweet($tweet, get_oauth());
var_dump($tweet_status);

    #log tweet so we don't repeat ourselves
    $db->query( sprintf($log_tweet_sql, $signer['id'], $tweet) );

    sleep($interval);
}

$signers->close();

?>
