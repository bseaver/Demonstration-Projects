<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8" />
   <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1" />
   <meta name="description" content="Address Search via Google API" />
   <title>Address Search</title>

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

      body {
         background-image: url("background2b.jpg");
         background-size: cover;
         background-position: center;
         height: 100%;
         width: 100%;
      }

      hr {
         // Wanted a thicker Bootstrap horizontal rule line
         // http://stackoverflow.com/questions/11577712/hr-tag-in-twitter-bootstrap-not-functioning-correctly

         -moz-border-bottom-colors: none;
         -moz-border-image: none;
         -moz-border-left-colors: none;
         -moz-border-right-colors: none;
         -moz-border-top-colors: none;
         border-color: #EEEEEE -moz-use-text-color #FFFFFF;
         border-style: solid none;
         border-width: 1px 0;
         margin: 18px 0;
      }

      .alert {
         display: none;
      }

   </style>
</head>
<body>          

   <div class="container addressContainer">
      <div class="jumbotron col-md-8 col-md-offset-2">
      <div class="row">
         <div class="col">


            <!-- Google Address Search Form -->
            <div class="form">
               <h2>Address Search</h2>
               <p>
                  Information provided by  
                  <a href="http://www.google.com" target="_blank">www.google.com</a>
               <p>
               
               <input type="text" id="searchAddress"  name="searchAddress" class="form-control" 
                  placeholder="Address e.g. 123 Main St, Plymouth, MA" value=""
               >

               <br>
               <button type="submit" id="searchAddressButton" class="btn btn-info btn-lg">Search Address</button>
            </div> <!-- class="form" -->


            <!-- Google: ADDRESS FOUND -->
            <div id="goodAddress" class="alert alert-success" role="alert">
               <p>Google matched that address:</p>
               <p id="matchedAddress"></p>
            </div>

            <!-- Google: NO ADDRESS FOUND-->

            <div id="badAddress" class="alert alert-warning" role="alert">
               <p id="errorDescription"></p>
            </div>

            <!-- WEBSITE FAILURE ALERT -->
            <div id="processingError" class="alert alert-danger" role="alert">
               <p>Sorry! We had an error on our site.  Please try again later.</p>
            </div>


         </div> <!-- class="col col-md-6 col-md-offset-3 contactForm" -->
      </div> <!-- class="row" -->
      </div> <!-- class="jumbotron" -->
   </div> <!-- class="container"-->

   
   <!-- Latest compiled and minified JavaScript -->
   <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
   <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

   <!-- Page Specific JavaScript Section -->
   <script type="text/javascript">
      // State variables: 
      var onFindClick = false, waitOnService = false, matchedAddress = "";
      var processingError = false, errorMessage = "", re = /^test(\d+)$/i, inputs = "", addressData = "";



      // State Processing:
      function stateProcessor() {
         var match, testIndex

         // Clicked Find button, hide messages
         if (onFindClick) {
            $(".alert").hide();
         }

         // Not processing, show messages from top down
         if (!onFindClick && !waitOnService) {
            // Update system messages
            $("#errorDescription").html(errorMessage);
            $("#matchedAddress").html(matchedAddress);

            // Show alerts
            if (matchedAddress) {
               $("#goodAddress").fadeIn();
            }
            if (errorMessage) {
               $("#badAddress").fadeIn();
            }
            if (processingError) {
               $("#processingError").fadeIn();
            }
         }

         // Clicked Find button, and not already waiting to retrieve data:
         // Validate inputs
         if (onFindClick && !waitOnService) {
            // Continue if valid
            onFindClick = validateInputs();

            // Show any message
            if (!onFindClick) {
               stateProcessor();
            }
         }

         // Inputs are valid - do we have test data? 
         // For example: "test2" entered in address search field
         match = inputs.match(re);

         if (onFindClick && !waitOnService) {
            var fetchPage = "gg-address-search.php?address=" + inputs;

            // If user entered test0 or test2, try fetch a test file
            if (match) {
               fetchPage = "gg-test-data.php?fileno=" + match[1];
            }

            // Button click is now handled
            onFindClick = false;

            // We are now waiting for the USPS site to get back to us
            waitOnService = true;

            $.get(fetchPage,
               function (xml) {

                  // Google (or our test file) site has gotten back to us 
                  waitOnService = false;

                  // Extract any error message
                  extractErrorMessage(xml);

                  // Extract found address data
                  extractMatchedAddress(xml);

                  // If we found nothing, this is a processing error
                  processingError = (!errorMessage && !matchedAddress);

                  // Update our page
                  stateProcessor();
                  return;

            })  

         } // end if inputs are valid
      } // end function stateProcessor()


      function extractMatchedAddress(xml) {
         var haveUSPSUndeliverableAddress = false;

         $(xml).find("result").each( function () {
            var formatted_address = "", place_id = "",
               locale = "", localePart = "", nextAddress = "",
               Zip5 = "", Zip4 = "", ZipPlus4 = "",
               i = 0, addrShort = [], addrLong = [], addrType = ""
               typeList = ["locality","administrative_area_level_2", 
                  "administrative_area_level_1","country",
                  "postal_code","postal_code_suffix"];

            // Get formatted address
            $(this).find("formatted_address").each( function() {
               formatted_address = $(this).text();
            });

            // Get place Id
            $(this).find("place_id").each( function() {
               place_id = $(this).text();
            });

            // Grab address components into arrays
            $(this).find("address_component").each( function() {
               $(this).find("type").each( function() {
                  addrType = $(this).text();
                  i = typeList.indexOf(addrType);

                  // break out if we found an entry in typeList
                  if (i >= 0) return false;
               });

               if (i >= 0) {
                  $(this).find("short_name").each( function() {
                     addrShort[i] = $(this).text();
                  });

                  $(this).find("long_name").each( function() {
                     addrLong[i] = $(this).text();
                  });
               }
            }); // find("address_component")

            // Is this a US address with an postal code suffix?
            // If so, append that suffix to the postal (ZIP) code 
            // (Use do loop as convenient logic structure to exit)
            do {
               // Is this a US address? (continue if yes)
               if (addrShort[typeList.indexOf("country")] != "US") break;

               // Assuming we have a formatted address and a postal code...
               if (!formatted_address) break;
               Zip5 = addrShort[typeList.indexOf("postal_code")];
               if (!Zip5) break;

               // Do we have a postal code suffix (Zip4)
               Zip4 = addrShort[typeList.indexOf("postal_code_suffix")];

               // If we have gotten this far and don't have a Zip4, 
               // or can't find the Zip5 in the formatted address,
               // then we may not have a U.S. Postal Service
               // deliverable address.

               i = formatted_address.lastIndexOf(Zip5);
               if (!Zip4 || i < 0) {
                  haveUSPSUndeliverableAddress = true;
                  formatted_address += " **";
                  break;
               }

               // If we can already find Zip5-Zip4 in the formatted address we are done
               ZipPlus4 = Zip5 + "-" + Zip4
               if (formatted_address.indexOf(ZipPlus4) >= 0) break;

               // Now replace the last Zip5 in the formatted address with ZipPlus4
               formatted_address = 
                  formatted_address.substring(0, i) +
                  ZipPlus4 +
                  formatted_address.substr(i + Zip5.length);
            } while (false);

            // Construct region info from the first 4
            // address components in typeList[]
            for (i = 0; i < 4; i++) {
               localePart = addrLong[i];
               if (localePart) {
                 locale += (locale)? ", " : "";
                 locale += localePart;
               }
            };
            
            // Construct address line as link to map of address (via Google place_id)
            // and with Bootstrap tool tip showing names of the locale.
            // If no place Id, leave the link empty and place notice of this condition in the tool tip. 
            nextAddress = '';
            nextAddress += '<hr>';
            nextAddress += '<p>';
            nextAddress += '<a';

            // Link to PHP map page
            if (place_id) {
               nextAddress += ' href="gg-map-placeid.php?auto=1&placeid=' + place_id + '"';
               nextAddress += ' target="_blank"';
            } else {
               nextAddress += ' href="#"';
            }

            nextAddress += ' data-toggle="tooltip"';

            nextAddress += ' title="';

            // Tool tip of locale or explanation if no place Id
            if (place_id) {
               nextAddress += (locale) ? locale : '(Locale information not received with this address)';
            } else {
               nextAddress += 'Cannot link to map since we did not receive a Place ID with this address';
            }

            nextAddress += '"">';

            nextAddress += (formatted_address) ? formatted_address : '(Did not receive a formatted address)';

            nextAddress += '</a>';

            nextAddress += '</p>';
            
            matchedAddress += nextAddress;
         });

         if (haveUSPSUndeliverableAddress) {
            matchedAddress += '<hr>** No "ZIP Plus 4" returned by Google for this address.  It might not be deliverable by the U.S. Postal Service.';
         }
      }


      function extractErrorMessage(xml) {
         var status = "";
         $(xml).find("status").each( function() {
            status += (status)?", ":"" + $(this).text();
         });
         if (status != "OK") {
            errorMessage = "Search Status: " + status;
         }
      }

      // Handle address validation request button click - 
      //   if not processing button click 
      //   and not waiting to retrieve data
      $("#searchAddressButton").click(function() {
         if (!onFindClick && !waitOnService) {
            onFindClick = true;
            matchedAddress = "";
            errorMessage = "";
            processingError = false;
            stateProcessor();
         }
      });


      // Get inputs and make sure not empty
      function validateInputs() {
         // Validate and build list of arguments:
         inputs = $("#searchAddress").val();

         // complete validation
         errorMessage = "";
         if (!inputs) {
            errorMessage += "Please enter Address for search.";
         }
         return (!errorMessage);
      } // end function validateInputs()





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


      // Enable Bootstrap tool tips via a tag attributes
      // data-toggle="tooltip" and title="<the tool tip>"
      $(document).ready(function(){
          $('[data-toggle="tooltip"]').tooltip(); 
      });
   </script>

</body>
</html>
