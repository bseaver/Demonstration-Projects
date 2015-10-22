<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8" />
   <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1" />
   <meta name="description" content="Address Validation via USPS API" />
   <title>USPS Address</title>

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

      .addressContainer {
      }

      .required {
         border-color:red;
      }

      .alert {
         Xmargin-top: 10px;
         display: none;
      }

   </style>
</head>
<body>          

   <div class="container addressContainer">
      <div class="jumbotron col-md-6 col-md-offset-3">
      <div class="row">
         <div class="col">


            <!-- USPS Address Validation Form -->
            <div class="form">
               <h2>U.S. Postal Service Address Validation</h2>
               <p>
                  Information provided by  
                  <a href="http://www.usps.com" target="_blank">www.usps.com</a>
               <p>
               
               <input type="text" id="Address1"  name="Address1" class="form-control" 
                  placeholder="Street address e.g. 123 Main St" value=""
               >

               <input type="text" id="Address2"  name="Address2" class="form-control" 
                  placeholder="Second line of street address (when applicable)" value=""
               >

               <input type="text" id="City"  name="City" class="form-control" 
                  placeholder="City e.g. Albany" value=""
               >

               <input type="text" id="State"  name="State" class="form-control" 
                  placeholder="2 letter State e.g. NY" value=""
               >

               <input type="text" id="Zip5"  name="Zip5" class="form-control" 
                  placeholder="5 digit ZipCode" value=""
               >

               <br>
               <button type="submit" id="validateAddressButton" class="btn btn-info btn-lg">Validate Address</button>
            </div> <!-- class="form" -->


            <!-- USPS: ADDRESS FOUND -->
            <div id="goodAddress" class="alert alert-success" role="alert">
               <p>U.S. Postal Service matched that address:</p><br>
               <p id="matchedAddress"></p>
            </div>

            <!-- USPS: NO ADDRESS FOUND OR UNDELIVERABLE-->

            <div id="badAddress" class="alert alert-warning" role="alert">
               <p id="errorDescription"></p><br>
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
      // Address fields we may send and receive from USPS address validation
      var addressFields = ["Address1", "Urbanization", "Address2", "City", "State", "Zip5", "Zip4", "ReturnText"];
      var addressFormatRule = ["Line", "Line", "Line", "City", "ST", "Zip5", "None", "Down2"];

      // If we find these fields in the result from USPS, we got an error
      var errorFields = ["Error", "Description"]

      // State variables: 
      var onFindClick = false, waitOnService = false, matchedAddress = "";
      var processingError = false, errorMessage = "", Zip5 = "", re = /^test(\d+)$/i, inputs = "", addressData = "";

      // Test cases
      testData = [
         '<textarea><?xml version="1.0" encoding="UTF-8"?><AddressValidateResponse><Address ID="0"><Error><Number>-2147219401</Number><Source>clsAMS</Source><Description>Address Not Found.  </Description><HelpFile/><HelpContext/></Error></Address></AddressValidateResponse></textarea>',
         '<textarea><?xml version="1.0" encoding="UTF-8"?><AddressValidateResponse><Address ID="0"><Address2>106 MAIN ST</Address2><City>TRUMANSBURG</City><State>NY</State><Zip5>14886</Zip5><Zip4>3215</Zip4><ReturnText>Default address: The address you entered was found but more information is needed (such as an apartment, suite, or box number) to match to a specific address.</ReturnText></Address></AddressValidateResponse></textarea>',
         '<textarea style="height:300px;width:500px"><?xml version="1.0" encoding="UTF-8"?><AddressValidateResponse><Address ID="0"><Address1>ISLA VERDE</Address1><Address2>4800 AVE ISLA VERDE</Address2><City>CAROLINA</City><State>PR</State><Zip5>00979</Zip5><Zip4>5441</Zip4></Address></AddressValidateResponse></textarea>',
         '<textarea style="height:300px;width:500px"><?xml version="1.0" encoding="UTF-8"?><AddressValidateResponse><Address ID="0"><Address2>5192 N COULTER ST</Address2><City>PHILADELPHIA</City><State>PA</State><Zip5>13216</Zip5><Zip4>4207</Zip4></Address></AddressValidateResponse></textarea>'
      ];



      //stateProcessor();

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
         // For example: "test2" entered in Zip 5 (ZIP code) field
         match = Zip5.match(re);
         if (onFindClick && !waitOnService && match) {
            // Button click is now handled by test data
            onFindClick = false;

            // User entered "test" followed by a number which is the test index
            testIndex = Number(match[1]);

            // If the index is in the range of the test data, process that data,
            // else supply the empty result
            addressData = "";
            if (testIndex >= 0 && testIndex < testData.length) {
               addressData = testData[testIndex];
            }

            // Extract any error message
            extractErrorMessage(addressData);

            // Extract any matched address
            extractMatchedAddress(addressData);

            // If we found nothing, this is a processing error
            processingError = (!errorMessage && !matchedAddress);

            // Update our page
            stateProcessor();
            return;
         }

         // Inputs are valid and no test data
         if (onFindClick && !waitOnService) {
            // Button click is now handled
            onFindClick = false;

            // We are now waiting for the USPS site to get back to us
            waitOnService = true;

           $.get("uspsaddressval.php?" + inputs,
               function (addressData) {

                  // USPS site has gotten back to us 
                  waitOnService = false;

                  // Extract any error message
                  extractErrorMessage(addressData);

                  // Extract any matched address
                  extractMatchedAddress(addressData);

                  // If we found nothing, this is a processing error
                  processingError = (!errorMessage && !matchedAddress);

                  // Update our page
                  stateProcessor();
                  return;

            })  

         } // end if inputs are valid
      } // end function stateProcessor()


      function extractMatchedAddress(addressData) {
         var i = 0, j = 0;
         // Lose any textarea wrapper
         var str = addressData.replace(/<\/?textarea.*?>/g, "");
         // Convert string to xml
         var xml = $.parseXML(str);

         // Gather up the values for the address fields
         var addressVal = [];
         for (i = 0; i < addressFields.length; i++) {
            addressVal[i] = "";
            $(xml).find(addressFields[i]).each( 
               function() {
                  // Extract address field value
                  addressVal[i] = $(this).text();
               }
            );
         };

         // Format Address
         matchedAddress = "";
         for (i = 0; i < addressFields.length; i++) {
            switch (addressFormatRule[i]) {
               case "None":
                  break;
               case "City":
                  if (addressVal[i]) {
                     matchedAddress += addressVal[i];
                  } else {
                     // Put in a place holder if there is some address
                     if (!matchedAddress) {
                        break;
                     }
                     matchedAddress += "??CITY??"
                  }
                     matchedAddress += "&nbsp;&nbsp;"
                  break;

               case "ST":
                  if (addressVal[i]) {
                     matchedAddress += addressVal[i];
                  } else {
                     // Put in a place holder if there is some address
                     if (!matchedAddress) {
                        break;
                     }
                     matchedAddress += "??"
                  }
                     matchedAddress += "&nbsp;&nbsp;"
                  break;

               case "Zip5":
                  if (addressVal[i]) {
                     matchedAddress += addressVal[i]
                     j = addressFields.indexOf("Zip4");
                     if (addressVal[j]) {
                        matchedAddress += "-" + addressVal[j]
                     }
                  }
                  break;

               case "Down2":
                  if (addressVal[i]) {
                     matchedAddress += "<br><br>" + addressVal[i];
                  }
                  break;

               default:
                  if (addressVal[i]) {
                     matchedAddress += addressVal[i] + "<br>";
                  }
                  break;
            } // end switch
         } // for all address fields

         // Detect undeliverable address by finding Zip5 but not Zip4
         if (addressVal[addressFields.indexOf("Zip5")] && !addressVal[addressFields.indexOf("Zip4")]) {
            // Move matchedAddress data to the error message
            errorMessage += matchedAddress + "<br><br>";
            errorMessage += "This address is not recognized by the U.S. Postal Service as an address they serve." +
             " Mail sent to this address may be returned.<br>";
            matchedAddress = "";
         }

         
      }


      function extractErrorMessage(addressData) {
         // Lose any textarea wrapper
         var str = addressData.replace(/<\/?textarea.*?>/g, "");
         // Convert string to xml
         var xml = $.parseXML(str);
         $(xml).find(errorFields[0]).each( 
            function() {
               // Extract error description
               errorMessage += $(this).find(errorFields[1]).text() + "<br><br>";
            }
         );
      }

      // Handle address validation request button click - 
      //   if not processing button click 
      //   and not waiting to retrieve data
      $("#validateAddressButton").click(function() {
         if (!onFindClick && !waitOnService) {
            onFindClick = true;
            matchedAddress = "";
            errorMessage = "";
            processingError = false;
            Zip5 = "";
            stateProcessor();
         }
      });


      // Builds list of GET fields and makes sure inputs are a reasonable minimum for the USPS address lookup
      function validateInputs() {
         var thisField, thisValue, hasAddress = false, hasCity = false, hasState = false;
         // Validate and build list of arguments:
         inputs = "";
         for (var i = 0; i < addressFields.length; i++) {
            thisField = addressFields[i];
            thisValue = $("#" + thisField).val();

            // If address field is not on form or is empty, then skip
            if (!thisValue) {
               continue;
            }

            // Validation
            switch (thisField) {
               case "Address1":
                  hasAddress = true; break;
               case "Address2":
                  hasAddress = true; break;
               case "City":
                  hasCity = true; break;
               case "State":
                  hasState = true; break
               case "Zip5":
                  hasCity = true; hasState = true; Zip5 = thisValue; break;
            }


            // Separate args with &
            if (inputs) {
               inputs += "&";
            }
            inputs += thisField + "=" + escapeHtml( thisValue.trim() );
         } // end for all addressFields

         // complete validation
         errorMessage = "";
         if (!hasAddress) {
            errorMessage += "<br>Missing Street Address.";
         }
         if (!hasCity) {
            errorMessage += "<br>Missing City (or ZIP code).";
         }
         if (!hasState) {
            errorMessage += "<br>Missing State (or ZIP code).";
         }
         if (errorMessage) {
            errorMessage = "Need to fill in some field(s):" + errorMessage + "<br><br>";
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


   </script>

</body>
</html>
