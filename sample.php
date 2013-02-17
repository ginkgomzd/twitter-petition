#!/usr/bin/php 
<?php

error_reporting(E_ALL);

$consumerKey    = '6R6cvzNuAsHif6l9u3kviw';
$consumerSecret = 'nUkonnV8t3gS78EVztqoY8djXccrUCZUxIw4PgwSepw';
$oAuthToken     = '1186627088-zQnMnLUadWb7abVE91SGXjq4iPhUr8tLu7dBi6T';
$oAuthSecret    = 'NYFAM9RhS1p5O6E0Lm0pK7Us6ky9jIl8ijlnzB4XdAk';

require_once(dirname(__FILE__).'/oauth/twitteroauth.php');

// create a new instance
$tweet = new TwitterOAuth($consumerKey, $consumerSecret, $oAuthToken, $oAuthSecret);
//var_dump($tweet);

//send a tweet
$tweet->post('statuses/update', array('status' => 'Hello World'.rand()));

echo 'status:'.$tweet->http_code;
echo 'call:';
var_dump($tweet->http_info);

?>
