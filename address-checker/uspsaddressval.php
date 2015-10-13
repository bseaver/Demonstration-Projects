<?php
// Call USPS API to verify an address
// Need to sign up and aquire a Username from USPS to use below.

// Logic absconded from:
// http://stackoverflow.com/questions/16298065/how-to-fetch-dynamical-xml-data-from-a-url

$timeout = 10; // seconds chosen arbitrarily
$url = "http://production.shippingapis.com/ShippingAPI.dll";
$initialPostfields = "API=Verify&XML=";

// This file is not in GitHub on purpose
$USPS_Username = file_get_contents("uspsid.private");

// Use $_GET['fields'] so this script is easy to test by itself
// rawurlencode() appropriately converts spaces to %20
$input_xml =
   '<AddressValidateRequest USERID="' . $USPS_Username . '"><Address ID="0">' .
   '<Address1>' . rawurlencode($_GET['Address1']) . '</Address1>' .
   '<Address2>' . rawurlencode($_GET['Address2']) . '</Address2>' .
   '<City>' . rawurlencode($_GET['City']) . '</City>' .
   '<State>' . rawurlencode($_GET['State']) . '</State>' . 
   '<Zip5>' . rawurlencode($_GET['Zip5']) . '</Zip5>' . 
   '<Zip4>' . rawurlencode($_GET['Zip4']) . '</Zip4>' . 
   '</Address></AddressValidateRequest>';


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POSTFIELDS,
            $initialPostfields . $input_xml);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
$data = curl_exec($ch);
curl_close($ch);

// Output result with surrounding <textarea></textarea> tags 
//so it is easy to view the result when testing this script.
echo '<textarea style="height:300px;width:500px">';
echo $data;
echo "</textarea>";
?>