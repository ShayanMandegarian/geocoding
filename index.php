<?php
include 'config.php';
global $db_host, $db_user, $db_name, $db_pass, $key;
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name) or die("Error " . mysqli_error($conn));
$conn->set_charset("latin1");
ini_set("allow_url_fopen", 1);
set_time_limit(500);
error_reporting(E_ALL ^ E_WARNING);

$url = 'https://maps.googleapis.com/maps/api/geocode/json?address=';
$urlKey = '&key='.$key;

$govUrl = 'https://geocoding.geo.census.gov/geocoder/locations/address?';
$govSuffix = '&state=CA&benchmark=Public_AR_Current&format=json';

$query = "SELECT id,address_1, address_2, city, state, zip_code FROM t_regist ORDER BY id ASC";
$result = mysqli_query($conn, $query);
?>
<html>
  <head>
    <title>Coordinates</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

    <style>
    .table-hover tbody tr:hover td {
    background: #E8B4FE;
    }
    a {
      color: black;
    }
    a:hover {
      color: #8f61e5;
    }
    </style>
  </head>

  <body>
    <div class="wrapper">
      <div class="container-fluid">
        <div class="col-sm">
          <div class="row">
            <h2>Coordinates</h2>
          </div>
          <div class="row">
            <h5>Click on values to open to coords on Google Maps, <b>bold</b> coords means they might be incorrect (partial_match == true, or address isn't in gov census)</h5>
          </div>
          <div class="row">
            <h5>Verified row is comparing Google Maps result with Government Geocoding service with a small margin of error</h5>
          </div>
          <div class="page-hedaer clearfix">
            <table class="table-striped table-hover table">
              <thead>
                <tr class='bg-dark text-white'>
                  <th scope="col">ID</th>
                  <th scope="col">Address 1</th>
                  <th scope="col">Address 2</th>
                  <th scope="col">City</th>
                  <th scope="col">Latitude</th>
                  <th scope="col">Longitude</th>
                  <th scope="col">Verified</th>
                  <!-- <th scope="col">URL</th> -->
                </tr>
              </thead>
              <tbody>
              <?php

while ($row = mysqli_fetch_array($result)) {
  if ($row['address_2'] == '') {
    $address = $row['address_1'] . "," . $row['city'] . "," . $row['state'] . "," . $row['zip_code'];
    $street = 'street='.urlencode($row['address_1']);
    $city = '&city='.$row['city'];
    if ($row['city'] == "Redwood Shores") {
      $city = '&city=Redwood City';
    }
    $addr = 1;
  }
  else {
    $address = $row['address_2'] . "," . $row['city'] . "," . $row['state'] . "," . $row['zip_code'];
    $addr = 2;
    $street = 'street='.urlencode($row['address_2']);
    $city = '&city='.$row['city'];
    if ($row['city'] == "Redwood Shores") {
      $city = '&city=Redwood City';
    }
  }
  $govAddress = $govUrl . $street . $city . $govSuffix;
  // $govFull = urlencode($govAddress);
  $urlAddress = urlencode($address);
  // $urlAddress = $address;
  $urlFull = $url . $urlAddress . $urlKey;
  $json = file_get_contents($urlFull);
  $obj = json_decode($json, true);
  $govJson = file_get_contents($govAddress);
  $govObj = json_decode($govJson, true);
  if (isset($govObj['result']['addressMatches'][0]['coordinates']['x'])) {
    $govLng = $govObj['result']['addressMatches'][0]['coordinates']['x'];
    $govLat = $govObj['result']['addressMatches'][0]['coordinates']['y'];
  }

  if ($obj['results'][0]['address_components'][0]['types'][0] == 'locality' || $obj['results'][0]['address_components'][0]['types'][0] == 'postal_code') {
    if ($addr == 1) {
      $address = $row['address_2'] . "," . $row['city'] . "," . $row['state'] . "," . $row['zip_code'];
      $street = 'street='.urlencode($row['address_2']);
      $city = '&city='.$row['city'];
      if ($row['city'] == "Redwood Shores") {
        $city = '&city=Redwood City';
      }
    }
    else {
      $address = $row['address_1'] . "," . $row['city'] . "," . $row['state'] . "," . $row['zip_code'];
      $street = 'street='.urlencode($row['address_1']);
      $city = '&city='.$row['city'];
      if ($row['city'] == "Redwood Shores") {
        $city = '&city=Redwood City';
      }
    }
    $govAddress = $govUrl . $street . $city . $govSuffix;
    $urlAddress = urlencode($address);
    // $urlAddress = $address;
    $urlFull = $url . $urlAddress . $urlKey;
    $json = file_get_contents($urlFull);
    $obj = json_decode($json, true);
    if ($street != 'street=') {
      $govJson = file_get_contents($govAddress);
      $govObj = json_decode($govJson, true);
    }
    if (isset($govObj['result']['addressMatches'][0]['coordinates']['x'])) {
      $govLng = $govObj['result']['addressMatches'][0]['coordinates']['x'];
      $govLat = $govObj['result']['addressMatches'][0]['coordinates']['y'];
    }
    if ($obj['results'][0]['address_components'][0]['types'][0] == 'locality' || $obj['results'][0]['address_components'][0]['types'][0] == 'postal_code') {
      continue;
    }
  }
  $ver = 'No';
  $lat = $obj['results'][0]['geometry']['location']['lat'];
  $lng = $obj['results'][0]['geometry']['location']['lng'];
  $latDiff = abs($lat - $govLat);
  $lngDiff = abs($lng - $govLng);
  if (($latDiff <= 0.01) && ($lngDiff <= 0.01)) {
    $ver = 'Yes';
  }
  // if (($latDiff >= 0.02) || ($lngDiff >= 0.02)) {
  //   // $lat = $govLat;
  //   // $lng = $govLng;
  // }
  $link = 'https://www.google.com/maps/search/?api=1&query='.$lat.','.$lng;
  echo "<tr>";
  echo "<td>";
  echo "<a href=$link>".$row['id']."</a>";
  echo "</td>";
  echo "<td>";
  echo "<a href=$link>".$row['address_1']."</a>";
  echo "</td>";
  echo "<td>";
  echo "<a href=$link>".$row['address_2']."</a>";
  echo "</td>";
  echo "<td>";
  echo "<a href=$link>".$row['city']."</a>";
  echo "</td>";

  $urlAddress = urlencode($address);
  // $urlAddress = $address;
  $urlFull = $url . $urlAddress . $urlKey;
  $json = file_get_contents($urlFull);
  $obj = json_decode($json, true);

  if ($obj['status'] == "OK") {
    if ((isset($obj['results'][0]['partial_match']))
    && $obj['results'][0]['partial_match'] == true || $ver == 'No') {
      $lat = $obj['results'][0]['geometry']['location']['lat'];
      $lng = $obj['results'][0]['geometry']['location']['lng'];
      $latDiff = abs($lat - $govLat);
      $lngDiff = abs($lng - $govLng);
      $ver = 'No';
      if (($latDiff <= 0.01) && ($lngDiff <= 0.01)) {
        $ver = 'Yes';
      }
      // if (($latDiff >= 0.02) || ($lngDiff >= 0.02)) {
      //   // $lat = $govLat;
      //   // $lng = $govLng;
      // }
      echo "<td><b><i>";
      echo "<a href=$link>".$lat."</a>";
      echo "</b></i></td>";
      echo "<td><b><i>";
      echo "<a href=$link>".$lng."</a>";
      echo "</b></i></td>";
      echo "<td><b><i>";
      echo "<a href=$link>".$ver."</a>";
      echo "</b></i></td>";
    }
    else {
      $lat = $obj['results'][0]['geometry']['location']['lat'];
      $lng = $obj['results'][0]['geometry']['location']['lng'];
      $latDiff = abs($lat - $govLat);
      $lngDiff = abs($lng - $govLng);
      $ver = 'No';
      if (($latDiff <= 0.01) && ($lngDiff <= 0.01)) {
        $ver = 'Yes';
      }
      // if (($latDiff >= 0.02) || ($lngDiff >= 0.02)) {
      //   // $lat = $govLat;
      //   // $lng = $govLng;
      // }
      echo "<td>";
      echo "<a href=$link>".$lat."</a>";
      echo "</td>";
      echo "<td>";
      echo "<a href=$link>".$lng."</a>";
      echo "</td>";
      echo "<td>";
      echo "<a href=$link>".$ver."</a>";
      echo "</td>";
    }

  }
  else {
    echo "<td>----</td>";
    echo "<td>----</td>";
  }

  // echo "<td>".$urlFull."</td>";
  echo "</tr>";
} ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
<?php
$conn->close();
 ?>
