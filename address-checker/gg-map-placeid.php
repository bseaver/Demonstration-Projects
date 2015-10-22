<?php
  // Call this PHP page to show place on map.
  // GET Parameters: ?auto=1&placeid=<your Google place Id>
  // Adapted from:
  // https://developers.google.com/maps/documentation/javascript/examples/geocoding-place-id
  $myGoogleAPIKey = file_get_contents("googleid.private");
  $placeId = $_GET["placeid"]; // Use this place Id if set.
  $autoLookup = $_GET["auto"]; // If set, don't ask user for place, just perform lookup and display
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>Map of Address</title>
    <style>
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      #map {
        height: 100%;
      }
#floating-panel {
  position: absolute;
  top: 10px;
  left: 25%;
  z-index: 5;
  background-color: #fff;
  padding: 5px;
  border: 1px solid #999;
  text-align: center;
  font-family: 'Roboto','sans-serif';
  line-height: 30px;
  padding-left: 10px;
}

    </style>
    <style>
      #floating-panel {
        width: 440px;
        <?php 
          if ($autoLookup) {
            echo "display: none;";
          }
        ?>
      }
      #place-id {
        width: 250px;
      }
    </style>
  </head>
  <body>
    <div id="floating-panel">
      <!-- Supply a default place ID for a place in Brooklyn, New York. -->
      <input id="place-id" type="text" value=
        <?php
          echo ($placeId) ? '"' . $placeId . '"' : '"ChIJd8BlQ2BZwokRAFUEcm_qrcA"';
        ?>
      >
      <input id="submit" type="button" value="Reverse Geocode by Place ID">
    </div>
    <div id="map"></div>
    <script>
// Initialize the map.
function initMap() {
  var map = new google.maps.Map(document.getElementById('map'), {
    <?php
      if (!$autoLookup) {
        echo "zoom: 8,";
        echo "center: {lat: 40.72, lng: -73.96}";
      }
    ?>
  });
  var geocoder = new google.maps.Geocoder;
  var infowindow = new google.maps.InfoWindow;

  document.getElementById('submit').addEventListener('click', function() {
    geocodePlaceId(geocoder, map, infowindow);
  });

  <?php
    if ($autoLookup) {
      echo "geocodePlaceId(geocoder, map, infowindow);";
    }
  ?>
}

// This function is called when the user clicks the UI button requesting
// a reverse geocode.
function geocodePlaceId(geocoder, map, infowindow) {
  var placeId = document.getElementById('place-id').value;
  geocoder.geocode({'placeId': placeId}, function(results, status) {
    if (status === google.maps.GeocoderStatus.OK) {
      if (results[0]) {
        map.setZoom(11);
        map.setCenter(results[0].geometry.location);
        var marker = new google.maps.Marker({
          map: map,
          position: results[0].geometry.location
        });
        infowindow.setContent(results[0].formatted_address);
        infowindow.open(map, marker);
      } else {
        window.alert('No results found');
      }
    } else {
      window.alert('Geocoder failed due to: ' + status);
    }
  });
}

    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $myGoogleAPIKey;?>&signed_in=true&callback=initMap"
        async defer></script>
  </body>
</html>