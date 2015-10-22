<?php
// Provide capability of retrieving test cases
// Grab text and return an XML doc
// http://stackoverflow.com/questions/13471014/sending-xml-response-when-a-url-is-hit-in-php

function sendResponse($testFile) {
   $response = '<?xml version="1.0" encoding="utf-8"?>';
   if (file_exists($testFile)) {
      $response = $response.file_get_contents($testFile);;
   } else {
      $response = $response."<GeocodeResponse><status>Test file not found.</status></GeocodeResponse>";
   }

   return $response;
}

header("Content-type: text/xml; charset=utf-8");
echo sendResponse("gg-test/gg-test" . $_GET['fileno'] . ".txt");
