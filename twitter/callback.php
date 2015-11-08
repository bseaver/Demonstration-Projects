<?php
// Adapted From: https://github.com/abraham/twitteroauth-demo - callback.php
// See redirect.php for more details

require 'bootstrap.php';
use Abraham\TwitterOAuth\TwitterOAuth;

/* Get temporary credentials from session. */
$request_token = [];
$request_token['oauth_token'] = $_SESSION['oauth_token'];
$request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];

/* If denied, bail. */
if (isset($_REQUEST['denied'])) {
    exit('Permission was denied. Please start over.');
}

/* If the oauth_token is not what we expect, bail. */
if (isset($_REQUEST['oauth_token']) && $request_token['oauth_token'] !== $_REQUEST['oauth_token']) {
    $_SESSION['oauth_status'] = 'oldtoken';
    header('Location: ./clearsessions.php');
    exit('Did not connect to Twitter account (invalid token). Please try again.');
}

/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, 
    $request_token['oauth_token'], $request_token['oauth_token_secret']);

/* Request access tokens from twitter */
$access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $_REQUEST['oauth_verifier']));

/* If HTTP response is 200 continue otherwise send to connect page to retry */
if (200 == $connection->getLastHttpCode()) {
    /* Save the access tokens. Normally these would be saved in a database for future use. */
    $_SESSION['access_token'] = $access_token;
    /* Remove no longer needed request tokens */
    unset($_SESSION['oauth_token']);
    unset($_SESSION['oauth_token_secret']);
    /* The user has been verified and the access tokens can be saved for future use */
    $_SESSION['status'] = 'verified';
} else {
    /* Save HTTP status for error dialog on connnect page.*/
    header('Location: ./clearsessions.php');
    exit('Did not connect to Twitter account.  Please try again.');
}

// Head back to home page after Twitter Authorization
header("location:" . $_SESSION['homePage']);

