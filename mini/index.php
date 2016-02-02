<?php
// Small, single page Data Driven website
// Create Update Delete key-value pairs from a form
// List existing key value pairs


// Database connection
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "scotchbox";


// Create connection
$link = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (mysqli_connect_errno()) {
  exit("Connection failed: " . mysqli_connect_errno());
} 


// Handle posted data:
// Define variables and set to empty values
// If posted, capture safe data with test_input() function
$key = $value = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $key = test_input($link, $_POST["key"]);
  $value = test_input($link, $_POST["value"]);
}


// Sanitize data before imbedding in SQL
function test_input($link, $data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  $data = mysqli_real_escape_string($link, $data);
  return $data;
}


// Special Case - Drop Table
if ($key == "drop" && $value == "table") {
  $query = "DROP TABLE `mykeyvalue`";
  $result = mysqli_query($link, $query);
  if ($result) {
    exit("Dropped table.");
  } else {
    exit("Error dropping table: " . mysqli_error($link));
  }
}


// Create key-value table if not yet done
$query = 
  "CREATE TABLE IF NOT EXISTS `mykeyvalue` (" .
  "`key` VARCHAR(50) PRIMARY KEY, " .
  "`value` VARCHAR(255) NOT NULL)";

$result = mysqli_query($link, $query);
if (!$result) {
  exit("Error creating table: " . mysqli_error($link));
}



// Create appropriate SQL for:
// Key with no value is a delete
// Key and value is insert or update (if key already exists)
$query = "";
if ($key) {
  if ($value) {
    // Do we already have a record with this key
    $query = "SELECT `key` FROM `mykeyvalue` WHERE `key` = '" . $key . "'";
    $row = false;
    $result = mysqli_query($link, $query);
    $query = "";
    if ($result) {
      $row = mysqli_fetch_row($result);
      mysqli_free_result($result);
    } else {
      exit("Error checking for key: " . mysqli_error($link) );
    }

    if ($row) {
      // Yes, we do - so update
      $query = "UPDATE `mykeyvalue` SET `value` = '" . $value . "' WHERE `key` = '" . $key . "'";
    } else {
      // No, we don't - so insert
      $query = "INSERT INTO `mykeyvalue` (`key`, `value`) VALUES ('" . $key . "', '" . $value . "') " ;
    }

  } else {
    // Because we have a key with no value, that means we do a delete
    $query = "DELETE FROM `mykeyvalue` WHERE `key` = '" . $key . "'";
  }
}

// If we have an Insert, Update or Delete then do it 
if ($query) {
  $result = mysqli_query($link, $query);
  if (!$result) {
    exit("Error on insert, update or delete: " . mysqli_error($link));
  }
}


// Grab set of key value pairs to list
$keyValueSet = [];
$query = "SELECT `key`, `value` FROM `mykeyvalue` ORDER BY `key`";
if ($result = mysqli_query($link, $query)) {
  while ($row = mysqli_fetch_row($result)) {
    $keyValueSet[] = $row;
  }
  mysqli_free_result($result);
} else {
  exit("Retrieving key-value pairs: " . mysqli_error($link) );
}


// Close connection
mysqli_close($link);
?>


<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>Key-Value</title>

  <style type="text/css">
    table {
      width: 100%; 
      border-color: grey;
      border-style: solid;
      border-collapse: collapse;
    }

    th, tr, td {
      border-style:solid;
      border-color: grey;
    }

    input {
      width: 100%;
    }


  </style>
</head>
<body>
  <h2>Maintain key value pairs</h2>
  <!-- htmlspecialchars() to defend against a URL hack -->
  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
    <label for="key">Key</label>
    <input type="text" name="key"><br>
    <label for="value">Value</label>
    <input type="text" name="value"><br>
    <input type="submit" value="Submit Update" style="width: 100px; margin-top: 5px;">
  </form>

  <hr>

  <table>
    <tr>
      <th>Key</th>
      <th>Value</th>
    </tr>

<?php
  $count = count($keyValueSet);
  for ($kvIndex = 0; $kvIndex < $count; $kvIndex++) {
    echo "<tr>";
    echo "<td>" . $keyValueSet[$kvIndex][0] . "</td>";
    echo "<td>" . $keyValueSet[$kvIndex][1] . "</td>";
    echo "</tr>";
  }
?>

  </table>  

<?php
  if (!count($keyValueSet)) {
    echo "<p>(No key values on file)</p>";
  }
?>

</body>
</html>