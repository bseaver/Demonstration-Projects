<?php
   // Post a tweet!

   // Working with Twitter API
   // For library documentation see:
   // https://twitteroauth.com/

   // To download the "master" or "releases" of the required library:
   // https://github.com/abraham/twitteroauth

   // Using Session - keep at top of code
   session_start();

   // Load our specificly assigned Twitter API info or exit with message
   // File has the structure:
   //   [top]
   //   API_Key = "N.........................X"
   //   API_Secret = "............................................"
   //   Owner = "seaver99"
   //   Owner_ID = "1111111111"
   //   Access_Token = "4.............................................C"
   //   Access_Token_Secret = "..........................................."

   $myinfo = @parse_ini_file("myinfo.private");
   if (!$myinfo) {
      die("Failed to load Twitter API parameters.");
   }


   // Note: In the folder with this script is a lib folder.
   // In the lib folder are folders of the various libary versions used here

//   require "lib/twitteroauth-0.5.4/autoload.php";
//   require "lib/twitteroauth-0.6.1/autoload.php";
   require "lib/twitteroauth-master/autoload.php";
   use Abraham\TwitterOAuth\TwitterOAuth;

   // Create a connection with the required tokens and keys
   $connection = new TwitterOAuth(
      $myinfo['API_Key'], 
      $myinfo['API_Secret'], 
      $myinfo['Access_Token'], 
      $myinfo['Access_Token_Secret']
   );


   // Post a tweet!
   if (true) {
      $response = $connection->post(
         "statuses/update", 
         array(
            "status" => "This is a test. I'm going to delete it ASAP." 
         )
      );
      print_r($response);
      exit;
   }

?>