<?php
// Adapted from: https://github.com/abraham/twitteroauth-demo
// For more docuentation and explanation, see:
// "Authorization flow" on: https://twitteroauth.com/

// "bootstrap" handles session, library and constants initialization for demo
require 'bootstrap.php';
use Abraham\TwitterOAuth\TwitterOAuth;

// This page will be used for sign-in, sign-out
$homePage = "sign-in.php";

// Record in Sessions this page so the callback.php can return us here
$_SESSION['homePage'] = $homePage;

// The login flow is:
// This Page -> redirect.php -> Twitter's App Authorization Page -> Callback.php -> This Page

// For your site, you need to setup a Twitter App (https://apps.twitter.com) that:
//   Has at least Read-only access
//   Has a Callback URL
//   Has Sign in with Twitter: Yes
  
// Sign Out (See Sign Out link below)homePage
// Clear session info and reload page
if ($_GET['signout']) {
  session_destroy();
  header("location:" . $homePage);
  exit;
}

// Has a user signed in to our app with Twitter?
// Can we create a connection?
unset($connection);
if ($_SESSION['access_token']) {
  /* Build TwitterOAuth object with our key and the signed in user's key */
  $access_token = $_SESSION['access_token'];
  $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, 
    $access_token['oauth_token'], $access_token['oauth_token_secret']);
}

// Can we get the user's credentials?
unset($user);
if ($connection) {
  $user = $connection->get("account/verify_credentials");
}

unset($name);
unset($screen_name);
unset($status_text);
unset($profile_image_url_https);
if ($user) {
  $name = $user->name;
  $screen_name = $user->screen_name;
  $status_text = $user->status_text;
  $profile_image_url_https = $user->profile_image_url_https;
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Sign-In</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css" integrity="sha384-aUGj/X2zp5rLCbBxumKTCw2Z50WgIr1vs/PFN4praOTvYXWlVyh2UtNUU0KAUhAX" crossorigin="anonymous">
    
    <style type="text/css">
      body {
        background-color: lightblue;
      }

      .col {
        margin-top: 10px;
        padding-bottom: 30px;
        border: 4px solid darkgrey;
        border-radius: 8px;
      }
    </style>

  </head>
  <body>
    <div class="container">
      <div class="row">
        <div class="col-md-5 col-md-offset-3 col">
          <h2>Twitter Sign-In Flow Demo</h2>
          <hr>

<?php
  if ($name) {
    echo '<p class="lead">Welcome ' . $name . '</p>';
    echo '<img src="' . $profile_image_url_https . '" alt="Twitter User Picture" style="margin-right: 10px">';
    echo "@" . $screen_name . "<br>";
    echo "<hr>";
    echo '<a href="' . $homePage . '?signout=1">';
      echo '<strong>Sign Out</strong>';
    echo '</a>';
  } else {
    echo '<a href="redirect.php">';
      echo '<img border="0" alt="Sign in with Twitter" src="/images/sign-in-with-twitter-link.png">';
    echo '</a>';
  }
?>

        </div>
      </div>
    </div>




    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

    <!-- Bootstrap -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" integrity="sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script>

  </body>
</html>