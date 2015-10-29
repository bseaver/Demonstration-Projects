<?php
// For guidance and more info on this Twitter API script see:
// http://iag.me/socialmedia/build-your-first-twitter-app-using-php-in-8-easy-steps/

// Libraries for our twitter project will be in a "lib" folder.
// The version of the library is downloaded from:
//   https://github.com/J7mbo/twitter-api-php/releases
// The selected version was the most recent one as of October 28, 2015. 
require_once('lib/twitter-api-php-1.0.5/TwitterAPIExchange.php');

   // Load our specificly assigned Twitter API info or exit with message
   // File has the structure:
   //   [top]
   //   API_Key = "N.........................X"
   //   API_Secret = "............................................"
   //   Owner = "s........."
   //   Owner_ID = "40111111"
   //   Access_Token = "4.............................................C"
   //   Access_Token_Secret = "..........................................."

   $myinfo = @parse_ini_file("myinfo.private");
   if (!$myinfo) {
      die("Failed to load Twitter API parameters.");
   }

   // For testing
   /*
   print_r(
      'API_Key=' . $myinfo['API_Key'] . ", ". 
      'API_Secret=' . $myinfo['API_Secret'] . ", ". 
      'Access_Token=' . $myinfo['Access_Token'] . ", ".  
      'Access_Token_Secret=' . $myinfo['Access_Token_Secret'] 
   );
   */


/** Set access tokens here - see: https://dev.twitter.com/apps/ **/
$settings = array(
'oauth_access_token' => $myinfo['Access_Token'],
'oauth_access_token_secret' => $myinfo['Access_Token_Secret'],
'consumer_key' => $myinfo['API_Key'],
'consumer_secret' => $myinfo['API_Secret']
);

$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
$requestMethod = "GET";
if (isset($_GET['user']))  {$user = $_GET['user'];}  else {$user  = "iagdotme";}
if (isset($_GET['count'])) {$count = $_GET['count'];} else {$count = 20;}
$getfield = "?screen_name=$user&count=$count";
$twitter = new TwitterAPIExchange($settings);
$string = json_decode($twitter->setGetfield($getfield)
->buildOauth($url, $requestMethod)
->performRequest(),$assoc = TRUE);
if($string["errors"][0]["message"] != "") {echo "<h3>Sorry, there was a problem.</h3><p>Twitter returned the following error message:</p><p><em>".$string[errors][0]["message"]."</em></p>";exit();}
foreach($string as $items)
    {
        echo "Time and Date of Tweet: ".$items['created_at']."<br />";
        echo "Tweet: ". $items['text']."<br />";
        echo "Tweeted by: ". $items['user']['name']."<br />";
        echo "Screen name: ". $items['user']['screen_name']."<br />";
        echo "Followers: ". $items['user']['followers_count']."<br />";
        echo "Friends: ". $items['user']['friends_count']."<br />";
        echo "Listed: ". $items['user']['listed_count']."<br /><hr />";
    }
?>
