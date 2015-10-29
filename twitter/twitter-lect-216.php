<?php
   // Working with Twitter API
   // For library documentation see:
   // https://twitteroauth.com/

   // To download Required Software:
   // https://github.com/abraham/twitteroauth

   // According to documentation it seems:
   // Versions 0.6.0 and above of the abraham twitteroath library require PHP 5.5 or above.
   // Version 0.5.4 of this library will run under PHP 5.4.
   // However, testing shows that these two versions and also master as of Oct. 23, 2015
   // still run the script below under PHP 5.4.

   // To update the PHP version to 5.5 on the Eco Web Hosting Site do the following:
   // On CPanel of Eco Web Hosting Site
   // Under Web Tools, Click Switch PHP Version, Select PHP 5.5, Click Update
   // Also make a backup copy of /public_html/.htaccess file
   // Edit /public_html/.htaccess file, Find DEFAULT_PHP_VERSION and set to 55

   // Our working folder contains a lib folder which in turn contains all the library / versions.
   // Thus the twitteroauth software can be replaced or deleted without losing our code.
   // Example:
   // /public_html
   //   /demo
   //     /twitter (our working directory including this file)
   //       /lib
   //         /twitteroauth-0.5.4 (Highest version of Abraham twitteroath supporting PHP 5.4)
   //         /twitteroauth-0.6.1 (Most recent release of Abraham twitteroath as of Oct. 28, 2015)
   //                             (0.6.1 still runs under PHP 5.4!)
   //         /twitteroauth-master(Downloaded Oct 23, 2015 - last change Sep 3, 2015)
   //                             (This still runs under PHP 5.4 also!)



   // Using Session - keep at top of code
   session_start();

   // For testing version of PHP (change false to true)
   if (false) {
      phpinfo();
      exit;
   }

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

   // For testing
   if (false) {
      print_r(
         'API_Key=' . $myinfo['API_Key'] . ", ". 
         'API_Secret=' . $myinfo['API_Secret'] . ", ". 
         'Access_Token=' . $myinfo['Access_Token'] . ", ".  
         'Access_Token_Secret=' . $myinfo['Access_Token_Secret'] 
      );
      exit;
   }

   // Utilize PHP Twitter API library from https://github.com/abraham/twitteroauth
//   require "lib/twitteroauth-0.5.4/autoload.php";
//   require "lib/twitteroauth-0.6.1/autoload.php";
   require "lib/twitteroauth-master/autoload.php";
   use Abraham\TwitterOAuth\TwitterOAuth;

   $connection = new TwitterOAuth(
      $myinfo['API_Key'], 
      $myinfo['API_Secret'], 
      $myinfo['Access_Token'], 
      $myinfo['Access_Token_Secret']
   );

   // For testing
   if (false) {
      print_r($connection);
      exit;
   }

   // Get Credentials
   if (false) {
      $content = $connection->get("account/verify_credentials");
      print_r($content);
      exit;
   }

   // Get Statuses
   // The following gets statuses from @twitterapi (official Twitter API information).
   // Change the "q" parameter below to search for a different person or organization
   if (false) {
      $statuses = $connection->get("search/tweets", array("q" => "twitterapi"));
      print_r($statuses);
      exit;
   }

   // The following gets statuses from @BostonLogan (Boston Massachussetts Logan Intl. Airport)
   if (false) {
      $statuses = $connection->get("search/tweets", array("q" => "@BostonLogan"));
      print_r($statuses);
      exit;
   }

   // Get User Timeline (change "screen_name" parameter to twitter handle of different person or org.)
   // "screen_name" => "united" gets tweets from United Airlines (@united)
   if (true) {
      $tweets = $connection->get(
         "statuses/user_timeline", 
         array(
            "include_entities" => "true", 
            "include_rts" => "true", 
            "screen_name" => "united", 
            "count" => "2"
         )
      );
      print_r($tweets);
      exit;
   }

?>