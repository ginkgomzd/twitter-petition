<?php

function tweet($status, $auth) {
  if (!is_array($auth)
    || ! array_key_exists('consumer key', $auth)
    || ! array_key_exists('consumer secret', $auth)
    || ! array_key_exists('oauth token', $auth)
    || ! array_key_exists('oauth secret', $auth)
  ) {
    return array('error', 'twitter api credentials not supplied');
  }

  require_once(dirname(__FILE__).'/oauth/twitteroauth.php');

  $tweet = new TwitterOAuth($auth['consumer key'], $auth['consumer secret'], $auth['oauth token'], $auth['oauth secret']);

  $tweet->post('statuses/update', array('status' => $status));

  return array('http status code' => $tweet->http_code, 'http info' => $tweet->http_info);
}

?>
