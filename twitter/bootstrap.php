<?php
   //*** Adapted from: https://github.com/abraham/twitteroauth-demo/blob/master/bootstrap.php

   session_start();

   // Not sure if this come into play or not.  
   // TODO: find out if this DEBUG may be deleted or how it will be used!
   define('DEBUG', 0);

   // Unlike in the abraham demo, we are not requiring 
   // http.php (secure website selection code)
   // templates.php (twig template system)

   // Note: In the folder with this script is a lib folder.
   // In the lib folder are folders of the various libary versions tested and used in this project.
   //   require "lib/twitteroauth-0.5.4/autoload.php";
   //   require "lib/twitteroauth-0.6.1/autoload.php";
   require "lib/twitteroauth-master/autoload.php";

   // Load our specificly assigned Twitter API info or exit with message
   // File has the structure:
   //   [top]
   //   API_Key = "N.........................X"
   //   API_Secret = "............................................"
   //   Owner = "s........"
   //   Owner_ID = "1111111111"
   //   Access_Token = "4.............................................C"
   //   Access_Token_Secret = "..........................................."
   //   http_site = "http.....com"
   //   https_site = "https.....com"

   $myinfo = @parse_ini_file("myinfo-reader.private");
   if (!$myinfo) {
      exit("(bootstrap.php): Failed to load Twitter API parameters from file.");
   }

   // Create constants that support abraham demo code
   define("CONSUMER_KEY", $myinfo['API_Key'] );
   define("CONSUMER_SECRET", $myinfo['API_Secret'] );

   // Running on http for now!
   // 11/3/15 Sent request to upgrade site to https
   // Next  line is intended to make code run under http until https is available.
   define("OAUTH_CALLBACK", $myinfo['https_site'] . '/demo/twitter/callback.php');

   // Complain and exit if missing some key data
   if (!CONSUMER_KEY || !CONSUMER_SECRET || !OAUTH_CALLBACK) {
       exit('(bootstrap.php): The CONSUMER_KEY, CONSUMER_SECRET, and OAUTH_CALLBACK environment variables must be set to use this demo.'
            . 'You can register an app with Twitter at https://apps.twitter.com/.');
   }