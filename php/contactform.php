<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8" />
   <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1" />
   <meta name="description" content="Contact Form" />
   <title>Contact Form</title>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">




   <style>
      .contactForm {
         margin-top:  20px;
         border: 1px solid grey;
         border-radius: 10px;
      }

      textarea {
         min-height: 150px;
      }


   </style>
</head>
<body>

   <?php
      // testMode allows us to test the logic without sending an actual 
      // contact message every time a valid message is submitted
      // If $testMode and $testSendFailure, then simulates a failure to the function mail(...).
      // Example: ../contactform.php?test=1&testsendfail=1
      $testMode = $_GET['test'];
      $testSendFailure = $_GET['testsendfail']; 

      // These are the fields we are expecting to be posted to the contact form
      $field = array("name", "email", "message");

      // Copy of $_POST because if our form is complete and message is sent successfully,
      // we will clear our copy so that it does not repopulate the user's form.
      // If there is an error sending or with validation, we will repopulate the 
      // user's form with the values they last submitted.
      $postCopy = $_POST;


      // Contact form variables
      $noneGiven = "(None Given)";
      $newLine = chr(13).chr(10);
      $contactMessage = "";
      $errorMessage = array();
      $userEnteredData = false;

      // Email variables:
      $emailError = false;
      $emailSent = false;
      $emailTo = file_get_contents("contact-email.private"); // file just with e-mail address
      $emailSubject="Seaver99.com Contact Form";
      $emailHeaders="From: " . $emailTo;
      $emailHeaders="";  // Trying with no From:

      // Construct message based upon the fields we are expecting
      foreach ($field as $value) {
         // If we already have text in our contact message Add a line feed to separate new field data
         if ($contactMessage) {
            $contactMessage .= $newLine;
         }

         // Add a line to our message with the field name (first letter upper cased) + colon + new line like "Name:"
         $contactMessage .= ucfirst($value) . ": ";

         // Name and Email can be on same line as label, but put contens of message under label
         if ($value == "message") {
            $contactMessage .= $newLine;
         }

         // Add Posted data (or None Given statement) to our message
         $postCopy[$value] = trim($postCopy[$value]);
         if ($postCopy[$value]) {
            $userEnteredData = true;
            $contactMessage .= $postCopy[$value];
         } else {
            $contactMessage .= $noneGiven;
         }
         
         // Field validation:

         // Does the email field have valid content?
         if ($value == "email") {
            if (!filter_var($postCopy[$value], FILTER_VALIDATE_EMAIL)) {
               array_push($errorMessage, "Please enter a valid E-mail address.");
            }                       
         }

         // Are the non email fields filled in?
         if ($value != "email") {
            if (!$postCopy[$value]) {
               array_push($errorMessage, "Please fill in the " . ucfirst($value) . " field.");
            }                       
         }
      }

      // Send the contact message (if no error with fields and if not in test mode)
      if (!$errorMessage AND !$testMode) {
         $emailSent = mail($emailTo, $emailSubject, $contactMessage, $emailHeaders);
         $emailError = !$emailSent;
      }

      // If no error with fields and if in test mode, pretend we sent the message or failed trying
      if (!$errorMessage AND $testMode) {
         $emailSent = !$testSendFailure;
         $emailError = !$emailSent;         
      }

      // If we "sent" the contact info, then leave the form clear
      if ($emailSent) {
         $postCopy = array();
      }
   ?>

          

   <div class="container">
      <div class="row">
         <div class="col col-md-6 col-md-offset-3 contactForm">
            <form method="post">
               <h2 style="text-align: center">
                  <?php 
                     // Show if we are in test mode
                     if ($testMode) {
                        echo '<span style="background-color: yellow">Testing</span>';
                     }
                  ?>
                  Seaver99 Contact Form
               </h2>


               <?php
                  // Put up alerts depending on status
                  if ($emailError) {
                     echo '<div class="alert alert-danger" role="alert">';
                     echo '  <strong>Sorry!</strong> There was an error on our server and your message was not sent.<br>';
                     echo '  <strong>Please try again later.</strong>';
                     echo '</div>';
                  }

                  if ($userEnteredData AND $errorMessage) {
                     echo '<div class="alert alert-warning" role="alert">';
                     echo '  <strong>There was an error in the form:</strong><br>';
                     foreach ($errorMessage as $value) {
                        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $value . '<br>';
                     }
                     echo '</div>';
                  }

                  if (!$userEnteredData) {
                     echo '<div class="alert alert-info" role="alert">';
                     echo '  Please fill in the form and click Submit.';
                     echo '</div>';
                  }

                  if ($emailSent) {
                     echo '<div class="alert alert-success" role="alert">';
                     echo '  <strong>Thank you!</strong> Your message has been sent.  We will reply as soon as we can.';
                     echo '</div>';
                  }

                  if ($testMode AND $emailSent) {
                     echo '<div class="alert alert-info" role="alert">';
                     echo '  <p><strong>Test Mode:</strong> The following is the constructed message:</p>';
                     echo '  <textarea class="form-control">' . $contactMessage . '</textarea>';
                     echo '</div>';
                  }
               ?>


               <label for="name">Name:</label>
               <input type="text" name="name" class="form-control" value="<?php echo $postCopy['name']; ?>"><br>

               <label for="email">E-Mail:</label>
               <input type="email" name="email" class="form-control" value="<?php echo $postCopy['email']; ?>"><br>

               <label for="message">Message:</label>
               <textarea name="message" class="form-control"><?php echo $postCopy['message']; ?></textarea><br>
               
               <input type="submit" name="submitButton" value="Submit" class="form-control btn btn-primary" style="margin-bottom:20px;">
            </form>

         </div> <!-- class="col col-md-6 col-md-offset-3 contactForm" -->
      </div> <!-- class="row" -->
   </div> <!-- class="container"-->

   
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

</body>
</html>
