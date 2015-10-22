<?php
// Call Google API to search for an address
// Need to sign up and aquire a Username from Google API to use below.
// Primary purpose for doing this in PHP instead of in JQuery is to hide the Google ID

$url = "https://maps.googleapis.com/maps/api/geocode/xml";

// This file is not in GitHub on purpose
$googleId = file_get_contents("googleid.private");

// Use $_GET['fields'] so this script is easy to test by itself
// rawurlencode() appropriately converts spaces to %20
$input_xml =
   'address=' . urlencode($_GET['address']) .
   '&key=' . $googleId;

$data = file_get_contents($url . "?" . $input_xml);

header("Content-type: text/xml; charset=utf-8");
echo $data;

?>