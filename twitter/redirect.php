<?php
// Adapted from: https://github.com/abraham/twitteroauth-demo - redirect.php
// For more docuentation and explanation, see:
// "Authorization flow" on: https://twitteroauth.com/

// Flow Summary:
// 1. User clicks link for "Sign in with Twitter"
// 2. Link opens redirect.php - which is this PHP script.
// 3. Redirect opens Twitter URL, so user may (or may not) authorize our app.
// 4. Upon Authorization, Twitter URL opens our callback.php page.
// 5. Callback.php can access user Twitter data via collected tokens and twitteroauth library.

// "bootstrap" handles session, library and constants initialization for demo
require 'bootstrap.php';

use Abraham\TwitterOAuth\TwitterOAuth;
/* Build TwitterOAuth object with client credentials. */
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);

/* Get temporary credentials. */
$request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => OAUTH_CALLBACK));

/* If last connection failed don't display authorization link. */
switch ($connection->getLastHttpCode()) {
    case 200:
        /* Save temporary credentials to session. */
        $_SESSION['oauth_token'] = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
        /* Build authorize URL and redirect user to Twitter. */
        $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
        break;
    default:
        /* Show notification if something went wrong. */
        exit('(redirect.php): Could not connect to Twitter. Please try again later.');
}

// Go the Twitter App Authorisation page, where user says OK or No to the app
header("location:" . $url);
