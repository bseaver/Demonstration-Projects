<?php
   // Weather Scraper

   ///////////////////////////////
   // ***** Function Section *****
   ///////////////////////////////

   // millimeters to inches - for outputting precipation
   function mmToInches ($matches) {
      $inches = $matches[1] * 0.03937;
      if ($inches < 5) {
         // If under 5 inches - show a single decimal
         $output = number_format( $inches, 1);
      } else {
         // If 5 inches and over - skip the decimal part
         $output = number_format( $inches, 0);
      }
      return $output . "in";
   }

   // Centigrade to Fahrenheit - for outside temperature range
   function degCToDegF ($matches) {
      $output = number_format( ($matches[1] * 9 / 5) + 32, 0) . "&deg;F";
      return $output;
   }

   // Changes the C temperatures in the forecast to F
   // and precipitation mm to in(ches)
   function metricToStandard ($forecastM) {
      // Grab a copy of the forecast to convert
      $forecastS = $forecastM;

      // Search for and convert degrees C to degrees F
      $forecastS = preg_replace_callback('/(\d*[.]?\d*)&deg;C/', "degCToDegF", $forecastS);

      // Search for and replace precipitation from mm to inches
      $forecastS = preg_replace_callback('/(\d*[.]?\d*)mm/', "mmToInches", $forecastS);

      return $forecastS;
   }

   ////////////////////////
   // ***** Main Body *****
   ////////////////////////

   // constant
   // When giving the passed in city back in an error message, how many characters of this may we use?
   $MAXCITYLENBACK = 30;

   // State variables:
   $successRetrieve = "";
   $successScrape = "";
   $successExtractCity = "";

   // Fetch the page for the city

   // Build URL to retrieve city's weather forecast
   $city = $_GET['city'];
   $city = str_replace(" ", "", $city);
   $weatherSite = "www.weather-forecast.com";
   $url = 'http://' . $weatherSite . '/locations/' . $city . '/forecasts/latest';

   // Retrieve contents of URL, but suppress error handling with @.
   // We'll handle the case if no data is retrieved.
   $successRetrieve = @file_get_contents($url);

   // Some sample data
   //$successRetrieve = '>New York 1 &ndash; 3 Day Weather Forecast Summary:</b><span class="read-more-small"><span class="read-more-content"> <span class="phrase">Moderate rain (total 17mm), heaviest on Sat night. Warm (max 27&deg;C on Fri afternoon, min 15&deg;C on Sun night). Winds increasing (calm on Fri night, fresh winds from the NW by Sun night).</span></span></span></p><div class="forecast-cont"><div class="units-cont"><a class="units metric active">&deg;C</a><a class="units imperial">&deg;F</a></div><table class="forecasts"><tr valign="top"><td class="first_header_tds invis" rowspan="2" style="background-color: #FFF;"></td><td class="date-header day-end" colspan="3" style="background-color: #999999;"><div class="dayname"><span class="show-for-medium-up">Friday</span><span class="show-for-small-only">';
   //$successRetrieve = '<p class="summary"><b>Bend 1 &ndash; 3 Day Weather Forecast Summary:</b><span class="read-more-small"><span class="read-more-content"> <span class="phrase">Moderate rain (total 11mm), heaviest on Wed afternoon. Very mild (max 18&deg;C on Fri afternoon, min 6&deg;C on Wed morning). Winds decreasing (strong winds from the W on Thu morning, calm by Fri night).</span>';

   // Extract the 3 day Weather

   // The following pattern suggested in the Udemy course will yeild forecast in $matches[1] as of September 10, 2015 in the U.S.A.
   // However the multiple <span> blocks may possibly change within the ($weatherSite) causing this logic to fail in the future.
   if (false AND $successRetrieve) {
      $matches = array();
      $pattern = '/3 Day Weather Forecast Summary:<\/b><span class="read-more-small"><span class="read-more-content"> <span class="phrase">(.*?)<\/span/s';
      preg_match( $pattern, $successRetrieve, $matches);
      $successScrape = $matches[1];

   }

   // Instead, let's take a hopefully more robust approach and extract the forecast from $matches[0] using a less <span> intensive pattern
   // Note 1: We separately keep track of the search prefix, so we can remove it from the beginning of $matches[0].
   // Note 2: We end the search on pattern not with '<', but with '<\/span>' because we need to make sure we search to the first closing </span>
   // Note 3: We clean up the miscellaneous </b>, <span ...> and </span> that remain in the matched string. 
   if (true AND $successRetrieve) {
      $matches = array();
      $patternPrefix = '3 Day Weather Forecast Summary:';
      $pattern = '/' . $patternPrefix . '(.*?)<\/span>/s';
      preg_match( $pattern, $successRetrieve, $matches);
      // $matches[0] starts with the $patternPrefix, so remove that from the beginning.
      $successScrape = substr($matches[0], strlen($patternPrefix));
      // Clean up (see Note 3)
      if (false) {
         // This works, but trying more general solution below
         $successScrape = preg_replace('/<\/b>/', '', $successScrape);
         $successScrape = preg_replace('/<span.*?>/', '', $successScrape);
         $successScrape = preg_replace('/<\/span>/', '', $successScrape);
      } else {
         // More general solution
         $successScrape = preg_replace('/<.*?>/', '', $successScrape);
      }
      // Final trim of whitespace
      $successScrape = trim($successScrape);
   }

   // Optional Metric to Standard conversion
   if ($successScrape AND $_GET['convert']) {
      $successScrape = metricToStandard($successScrape);
   }

   // Extract City code from web page
   if ($successRetrieve) {
      $matches = array();
      $pattern = '/' . $weatherSite . '\/locations\/(.*?)\/forecasts\/latest/s';
      preg_match( $pattern, $successRetrieve, $matches);
      $successExtractCity = $matches[1];
   }


   // Create an array
   //   errorLevel: 0 = all ok, 1 = Failed to retrieve, 2 = failed to process
   //   errorMessage: Explain what went wrong if anything
   //   cityCode: (may be re-used to lookup cities' weather again)
   //   cityWeather: Scraped weather forecast

   $returnVal = array (
      "errorLevel"  => 0,
      "errorMessage" => "",
      "cityCode" => $successExtractCity,
      "cityWeather" => $successScrape
   );

   if (!$successRetrieve) {
      $returnVal["errorLevel"] = 1;
      $errorCity = $city;
      if (strlen($errorCity) > $MAXCITYLENBACK) {
         $errorCity = substr($city, 0 , $MAXCITYLENBACK) . '...';
      }
      $returnVal["errorMessage"] = 'Failed to retrieve weather for ' . $errorCity . '.';
   }

   if (!$returnVal["errorLevel"] && !$successScrape) {
      $returnVal["errorLevel"] = 2;
      $returnVal["errorMessage"] = "We failed to extract your weather from " . $weatherSite . ".";
   }

   if (!$returnVal["errorLevel"] && !$successExtractCity) {
      $returnVal["errorLevel"] = 2;
      $returnVal["errorMessage"] = "We failed to extract the city code from " . $weatherSite . ".";
   }


   // Output array as both displayable for unit testing and parsable for easy use in calling page
   foreach ($returnVal as $key => $value) {
      echo $key . "=>" . $value . "<br>";
   }


