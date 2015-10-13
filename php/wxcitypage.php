<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8" />
   <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1" />
   <meta name="description" content="Testing PHP Form" />
   <title>PHP Post Form</title>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">

<!-- Optional theme -->
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">

<!-- JQuery -->
<Xscript src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></Xscript>


   <style>
      html, body {
         height: 100%;
         font-family: sans-serif;
      }

      .weatherContainer {
         background-image: url("//dl.dropboxusercontent.com/u/54537955/IMG_20150530_133139.jpg");
         background-size: cover;
         background-position: center;
         height: 100%;
         width: 100%;
      }

      label {
         font-size: 150%;
         font-weight: normal;
      }

      col {
         margin-top: 40px important!;
      }

      h2, h4 {
         color: blue;
      }

      .alert {
         margin-top: 10px;
         display: none;
      }

      .btn {
         margin-top: 0px;
         margin-bottom: 10px;
      }

      .col {
         border: 3px solid white;
         border-radius: 10px;
      }

      .highlight {
         color: black;
         background-color: pink;
      }

      .checkbox {
         margin-top: 10px;
         margin-bottom: 0px;
         color: blue;
         font-size: .8em;
      }
   </style>
</head>
<body>

   <?php
   ?>

          

   <div class="container weatherContainer">
      <div class="row">
         <div class="col col-md-6 col-md-offset-3 ">


            <!-- CITY WEATHER REQUEST FORM -->
            <div class="form">
               <h2 style="text-align: center">Weather Forecast by City </h2>
               <h4>
                  For all supported cities and more weather details see: 
                  <a href="http://www.weather-forecast.com" target="_blank" style="color:white;">www.weather-forecast.com</a>
               </h4>
               
               <input type="text" id="weatherCity"  name="weatherCity" class="form-control" 
                  placeholder="Enter your city here... such as San Franciso, Portland, London, Boston, Paris, Madrid etc!" value=""
               >
               <br>

               <div class="checkbox">
                  <label>
                     <input id="convertFromMetric" type="checkbox" value="1" checked>
                     Convert measurements from Metric to Standard
                  </label>
               </div>
               <br>

               <button type="submit" id="findMyWeatherButton" class="btn btn-info btn-lg">Find My Weather</button>
            </div> <!-- class="form" -->


            <!-- FORECAST ALERT -->
            <div id="weatherForecast" class="alert alert-success" role="alert">
               More sunny days ahead!
            </div>

            <!-- NO CITY ENTERED ALERT -->
            <div id="weatherCityEmpty" class="alert alert-warning" role="alert">
               <p>In order to retrieve a forecast, we need a city entered above.</p>
            </div>

            <!-- WEBSITE SCRAPING FAILURE ALERT -->
            <div id="processingError" class="alert alert-danger" role="alert">
               <h3>Sorry! We had an error on our site.</h3>
               <p class="errorMessage"></p>
            </div>

            <!-- NO DATA RETRIEVED ALERT -->
            <div id="retrieveError" class="alert alert-warning" role="alert">
               <p class="errorMessage">
               </p><br>
               <p>If you wonder if this site is working at all, 
                  try a city that is known to be supported such as Boston.
               </p><br>
               <p>To explore the the full set of supported countries and cities, 
                  please access the full featured website:
               <a href="http://www.weather-forecast.com" target="_blank">www.weather-forecast.com</a>
               </p><br>
               <p>When you find cities with complex or non-unique names for which you may 
                  wish to retrieve forecasts in the future, make note of the city name/code
                  imbedded in the web address.  For example the weather for 
                  "Lebanon New Hamshire USA" may be retrieved with the city name Lebanon-7. 
                  <br>
                  <a href="http://www.weather-forecast.com/locations/Lebanon-7/forecasts/latest" target="_blank">
                     http://www.weather-forecast.com/locations/
                     <span class="highlight">Lebanon-7</span>
                     /forecasts/latest
                  </a>
               </p>
            </div>


         </div> <!-- class="col col-md-6 col-md-offset-3 contactForm" -->
      </div> <!-- class="row" -->
   </div> <!-- class="container"-->

   
   <!-- Latest compiled and minified JavaScript -->
   <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
   <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

   <!-- Page Specific JavaScript Section -->
   <script type="text/javascript">
      // What we expect from our weather data source
      var fieldDelim = "<br>", keyValueDelim = "=>";
      var expectedArrayFields = ["errorLevel", "errorMessage", "cityCode", "cityWeather"];

      // State variables: 
      var onFindClick = false, waitOnWeather = false, weatherCity = ""; weatherForecast = "";
      var retrieveError = false, processingError = false, weatherErrorMessage = "";

      //stateProcessor();

      // State Processing:
      function stateProcessor() {
         // Update City and Forecast if changed
         if (!onFindClick && !waitOnWeather) {
            $("#weatherCity").val(weatherCity);
         }
         $("#weatherForecast").html(weatherForecast);
         $(".errorMessage").html(weatherErrorMessage);

         // Hide unneeded alerts from the bottom up
         if (!retrieveError) {
            $("#retrieveError").fadeOut();
         }
         if (!processingError) {
            $("#processingError").fadeOut();
         }
         if (weatherCity) {
            $("#weatherCityEmpty").fadeOut();
         }
         if (!weatherForecast) {
            $("#weatherForecast").fadeOut();
         }

         // Display alerts from the top down
         if (!waitOnWeather && weatherForecast) {
            $("#weatherForecast").fadeOut().fadeIn();
         }

         // Error in retrieve
         if (retrieveError) {
            $("#retrieveError").fadeOut().fadeIn();
         }

         // Error in retrieve
         if (processingError) {
            $("#processingError").fadeOut().fadeIn();
         }

          // Clicked button, but no City entered
         if (onFindClick && !weatherCity && !waitOnWeather) {
            onFindClick = false;
            $("#weatherCityEmpty").fadeOut().fadeIn();
         }

         // Clicked Find button, and City entered and not waiting on the weather site
         // Handle built in test cases
         if (onFindClick && weatherCity && !waitOnWeather) {
            // If this is a test case
            weatherArray = testCity(weatherCity);
            if (!$.isEmptyObject(weatherArray)) {
               // Set Find button click as handled since we will be displaying test data
               onFindClick = false;

                  // Validate expected fields and update our status variables
                  weatherFieldsFromArray(weatherArray);

                  // Update our page
                  stateProcessor();
            }
         }

         // Clicked Find button, and City entered and not waiting on the weather site
         if (onFindClick && weatherCity && !waitOnWeather) {
            // Find button click has now been handled
            onFindClick = false;

            // We are now waiting for the weather site to get back to us
            waitOnWeather = true;

            $.get("weatherscraper.php?city=" + weatherCity + ($("#convertFromMetric").is(":checked")?"&convert=1":""),
               function (weatherData) {
                  // Weather site has gotten back to us 
                  waitOnWeather = false;

                  // Parse weather data into array
                  var weatherArray = flatDataAsArray(weatherData, fieldDelim, keyValueDelim);

                  // Validate expected fields and update our status variables
                  weatherFieldsFromArray(weatherArray);

                  // Update our page
                  stateProcessor();
            })
         }
      } // end function stateProcessor()


      // Let Enter click the submit button
      $("#weatherCity").keyup(function(event){
          if(event.keyCode == 13){
              $("#findMyWeatherButton").click();
          }
      });

      // Handle weather request button click - 
      //   if not processing Find button click and not waiting to retrieve weather data
      $("#findMyWeatherButton").click(function() {
         if (!onFindClick && !waitOnWeather) {
            onFindClick = true;;
            weatherCity = sanitizeInput( $("#weatherCity").val() );
            weatherForecast = "";
            weatherErrorMessage = "";
            retrieveError = false;
            processingError = false;
            stateProcessor();
         }
      });


      // Parse input string as "key1=>value1<br>key2..."
      function flatDataAsArray(data, fieldDelim, keyValueDelim) {
         var fieldArr = data.split(fieldDelim);
         var valueArr, dataAsArray = [];
         for (var i = 0; i < fieldArr.length; i++) {
            valueArr = fieldArr[i].split(keyValueDelim);
            if (valueArr[0]) {
               dataAsArray[valueArr[0]] = valueArr[1];
            }
            
         };
         return dataAsArray;
      }


      // Get rid if spaces and defend against code injection with user input
      function sanitizeInput(text) {
         text = text.replace(" ", "");
         text = text.replace(".", "");
         text = text.replace("\\", "");
         text = text.replace("/", "");
         return escapeHtml(text);
      }

      // Copied from post on StackOverflow. 
      function escapeHtml(text) {
        var map = {
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#039;'
        };

        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
      }      

      // Extract and validate expected data coming back from our weather scraper page
      function weatherFieldsFromArray(weatherArray) {
         var errorLevelKey = expectedArrayFields[0];
         var errorLevelValue;
         var errorLevelPattern = /^[012]$/; // Expected values in the set of ["0", "1", "2"]
         var missingOrBadField = false;
         var cityCode;

         // Initialize status fields assuming bad data
         retrieveError = false;
         processingError = true;
         weatherErrorMessage = "Missing or invalid field(s) from weather site: ";
         weatherForecast = "";

         // Validate incoming data:
         // Must be a string value and errorLevel must be in expected range
         
         for (var i = 0; i < expectedArrayFields.length; i++) {
            if (
                  typeof weatherArray[expectedArrayFields[i]] != "string" ||
                  (
                     expectedArrayFields[i] == errorLevelKey  &&
                     !errorLevelPattern.test(weatherArray[expectedArrayFields[i]])
                  )
               ) {
               weatherErrorMessage += ((missingOrBadField)?", ":"") + expectedArrayFields[i];
               missingOrBadField = true;
            }
         }

         // If all good, update our status fields
         if (!missingOrBadField) {
            errorLevelValue = weatherArray[expectedArrayFields[0]];
            weatherErrorMessage = weatherArray[expectedArrayFields[1]];
            cityCode = weatherArray[expectedArrayFields[2]];
            weatherForecast = weatherArray[expectedArrayFields[3]];

            // We translate weather site's errorLevel to our status variables
            retrieveError = false;
            processingError = false;
            switch (errorLevelValue) {
               case "0": // (No error)
                  break;               
               case "1":
                  retrieveError = true;
                  break;
               default:
                  processingError = true;
                  break;
            }

            // If we received a city code back from the weather site, we'll replace our weather city with it
            if (cityCode) {
               weatherCity = cityCode;
            }
         } // end if (!missingOrBadField)
      } // end function parseWeatherData()


      // Test cases to exercise the alerts
      function testCity(weatherCity) {
         // If test city is in the format of "test[n]", then simulate the weather with the coresponding test case
         var re = /^test(\d+)$/i;
         var match = weatherCity.match(re);
         var testCase, i, weatherArray = [];

         var test = [];
         test[0] = ["0", "", "Test0", "Success! This is where we display weather for your city."];
         test[1] = ["1", "This is where we display that we could not lookup your city.", "", ""];
         test[2] = ["2", "This is where we display there was a processing error.", "", ""];
         test[3] = ["2", "This is where we display there was a processing error, but still managed to get a forecast", "", "Here's the forecast despite a processing error."];
         test[4] = ["Last test scenario has missing or bad fields and is the catch all test case."];

         // If a match
         if (match !== null) {
            // Get number of test case
            testCase = Number(match[1]);

            // If not in array range,
            if ( !(testCase >= 0 && testCase < test.length) ) {
               // .. then set test case to last entry in array
               testCase = test.length - 1;
            }

            // Build test data
            for (i = 0; i < test[testCase].length; i++) {
               weatherArray[expectedArrayFields[i]] = test[testCase][i];
            };
         }

         return weatherArray;
      } // end function testCity(weatherCity)

   </script>

</body>
</html>
