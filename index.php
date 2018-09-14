<?php
include 'config.php';
global $db_host, $db_user, $db_name, $db_pass, $key, $tomKey;
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name) or die("Error " . mysqli_error($conn));
$conn->set_charset("latin1");
ini_set("allow_url_fopen", 1);
set_time_limit(500);
error_reporting(E_ALL ^ E_WARNING);

$url = 'https://maps.googleapis.com/maps/api/geocode/json?address=';
$urlKey = '&key='.$key;

$tomUrl = 'https://api.tomtom.com/search/2/geocode/';
$tomSuffix = '.json?countrySet=US&lat=37.337&lon=-121.89&topLeft=37.553%2C-122.453&btmRight=37.4%2C-122.55&extendedPostalCodesFor=Addr%2CPAD%2CPOI&key='.$tomKey;

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
            <h6>Click on values to open to coords on Google Maps, <b><i>bold</i></b> rows means they might be incorrect (Google and TomTom results are very different)</h6>
          </div>
          <div class="row">
            <h6>Verified row is comparing Google Geocoder result with TomTom Geocoder with a small margin of error</h6>
          </div>
          <div class="row">
            <h6>Click on values in the Tom Lat / Tom Lon columns to view the coordinates from TomTom in Google Maps</h6>
          </div>
          <div class="page-hedaer clearfix">
            <table class="table-striped table-hover table">
              <thead>
                <tr class='bg-dark text-white'>
                  <th scope="col">ID</th>
                  <th scope="col">Address 1</th>
                  <th scope="col">Address 2</th>
                  <th scope="col">City</th>
                  <th scope="col">Google Lat</th>
                  <th scope="col">Google Lon</th>
                  <th scope="col">Verified</th>
                  <th scope="col">Tom Lat</th>
                  <th scope="col">Tom Lon</th>
                  <th scope="col">URL</th>
                </tr>
              </thead>
              <tbody>
              <?php

while ($row = mysqli_fetch_array($result)) {
  if ($row['address_2'] == '') {
    $address = $row['address_1'] . "," . $row['city'] . "," . $row['state'] . "," . $row['zip_code'];
    $tomSearch = urlencode($row['address_1']) . ','. urlencode($row['city']) . ',' . 'CA';
    // $street = 'street='.urlencode($row['address_1']);
    // $city = '&city='.$row['city'];
    if ($row['city'] == "Redwood Shores") {
      $city = 'Redwood City';
      $tomSearch = urlencode($row['address_1']) . ',' . urlencode($city) . ',' . 'CA';
    }
    $addr = 1;
  }
  else {
    $address = $row['address_2'] . "," . $row['city'] . "," . $row['state'] . "," . $row['zip_code'];
    $addr = 2;
    $tomSearch = urlencode($row['address_2']) . ','. urlencode($row['city']) . ',' . 'CA';
    // $street = 'street='.urlencode($row['address_2']);
    // $city = '&city='.$row['city'];
    if ($row['city'] == "Redwood Shores") {
      $city = 'Redwood City';
      $tomSearch = urlencode($row['address_2']) . ',' . urlencode($city) . ',' . 'CA';
    }
  }
  $tomAddress = $tomUrl . $tomSearch . $tomSuffix;
  // $tomFull = urlencode($tomAddress);
  $urlAddress = urlencode($address);
  // $urlAddress = $address;
  $urlFull = $url . $urlAddress . $urlKey;
  $json = file_get_contents($urlFull);
  $obj = json_decode($json, true);
  $tomJson = file_get_contents($tomAddress);
  $tomObj = json_decode($tomJson, true);
  if (isset($tomObj['results'][0]['position']['lat'])) {
    $tomLng = $tomObj['results'][0]['position']['lon'];
    $tomLat = $tomObj['results'][0]['position']['lat'];
  }

  if ($obj['results'][0]['address_components'][0]['types'][0] == 'locality' || $obj['results'][0]['address_components'][0]['types'][0] == 'postal_code') {
    if ($addr == 1) {
      $address = $row['address_2'] . "," . $row['city'] . "," . $row['state'] . "," . $row['zip_code'];
      $tomSearch = urlencode($row['address_2']) . ','. urlencode($row['city']) . ',' . 'CA';
      // $street = 'street='.urlencode($row['address_2']);
      // $city = '&city='.$row['city'];
      if ($row['city'] == "Redwood Shores") {
        $city = 'Redwood City';
        $tomSearch = urlencode($row['address_2']) . ',' . urlencode($city) . ',' . 'CA';
      }
    }
    else {
      $address = $row['address_1'] . "," . $row['city'] . "," . $row['state'] . "," . $row['zip_code'];
      $tomSearch = urlencode($row['address_1']) . ','. urlencode($row['city']) . ',' . 'CA';
      // $street = 'street='.urlencode($row['address_1']);
      // $city = '&city='.$row['city'];
      if ($row['city'] == "Redwood Shores") {
        $city = 'Redwood City';
        $tomSearch = urlencode($row['address_1']) . ',' . urlencode($city) . ',' . 'CA';
      }
    }
    $tomAddress = $tomUrl . $tomSearch . $tomSuffix;
    $urlAddress = urlencode($address);
    // $urlAddress = $address;
    $urlFull = $url . $urlAddress . $urlKey;
    $json = file_get_contents($urlFull);
    $obj = json_decode($json, true);
    // if ($street != 'street=') {
    $tomJson = file_get_contents($tomAddress);
    $tomObj = json_decode($tomJson, true);
    // }
    if (isset($tomObj['results'][0]['position']['lon'])) {
      $tomLng = $tomObj['results'][0]['position']['lon'];
      $tomLat = $tomObj['results'][0]['position']['lat'];
    }
    if ($obj['results'][0]['address_components'][0]['types'][0] == 'locality' || $obj['results'][0]['address_components'][0]['types'][0] == 'postal_code') {
      continue;
    }
  }
  $ver = 'No';
  $lat = $obj['results'][0]['geometry']['location']['lat'];
  $lng = $obj['results'][0]['geometry']['location']['lng'];
  $latDiff = abs($lat - $tomLat);
  $lngDiff = abs($lng - $tomLng);
  if (($latDiff <= 0.01) && ($lngDiff <= 0.01)) {
    $ver = 'Yes';
  }
  // if (($latDiff >= 0.02) || ($lngDiff >= 0.02)) {
  //   // $lat = $tomLat;
  //   // $lng = $tomLng;
  // }
  $link = 'https://www.google.com/maps/search/?api=1&query='.$lat.','.$lng;
  $tomLink = 'https://www.google.com/maps/search/?api=1&query='.$tomLat.','.$tomLng;

  $urlAddress = urlencode($address);
  // $urlAddress = $address;
  $urlFull = $url . $urlAddress . $urlKey;
  $json = file_get_contents($urlFull);
  $obj = json_decode($json, true);

  // (isset($obj['results'][0]['partial_match']))
  // && $obj['results'][0]['partial_match'] == true ||

  if ($obj['status'] == "OK") {
    if ($ver == 'No') {
      $lat = $obj['results'][0]['geometry']['location']['lat'];
      $lng = $obj['results'][0]['geometry']['location']['lng'];
      $tomLng = $tomObj['results'][0]['position']['lon'];
      $tomLat = $tomObj['results'][0]['position']['lat'];
      $latDiff = abs($lat - $tomLat);
      $lngDiff = abs($lng - $tomLng);
      $ver = 'No';
      if (($latDiff <= 0.01) && ($lngDiff <= 0.01)) {
        $ver = 'Yes';
      }
      // if (($latDiff >= 0.02) || ($lngDiff >= 0.02)) {
      //   // $lat = $tomLat;
      //   // $lng = $tomLng;
      // }
      echo "<tr>";
      echo "<td><b><i>";
      // echo "<a href=$link>".$row['id']."</a>";
      echo $row['id'];
      echo "</b></i></td>";
      echo "<td><b><i>";
      // echo "<a href=$link>".$row['address_1']."</a>";
      echo $row['address_1'];
      echo "</b></i></td>";
      echo "<td><b><i>";
      // echo "<a href=$link>".$row['address_2']."</a>";
      echo $row['address_2'];
      echo "</b></i></td>";
      echo "<td><b><i>";
      // echo "<a href=$link>".$row['city']."</a>";
      echo $row['city'];
      echo "</b></i></td>";
      echo "<td><b><i>";
      echo "<a href=$link>".$lat."</a>";
      echo "</b></i></td>";
      echo "<td><b><i>";
      echo "<a href=$link>".$lng."</a>";
      echo "</b></i></td>";
      echo "<td><b><i>";
      // echo "<a href=$link>".$ver."</a>";
      echo $ver;
      echo "</b></i></td>";
      echo "<td><b><i>";
      echo "<a href=$tomLink>".$tomLat."</a>";
      echo "</b></i></td>";
      echo "<td><b><i>";
      echo "<a href=$tomLink>".$tomLng."</a>";
      echo "</b></i></td>";
    }
    else {
      $lat = $obj['results'][0]['geometry']['location']['lat'];
      $lng = $obj['results'][0]['geometry']['location']['lng'];
      $latDiff = abs($lat - $tomLat);
      $lngDiff = abs($lng - $tomLng);
      $tomLng = $tomObj['results'][0]['position']['lon'];
      $tomLat = $tomObj['results'][0]['position']['lat'];
      $ver = 'No';
      if (($latDiff <= 0.01) && ($lngDiff <= 0.01)) {
        $ver = 'Yes';
      }
      // if (($latDiff >= 0.02) || ($lngDiff >= 0.02)) {
      //   // $lat = $tomLat;
      //   // $lng = $tomLng;
      // }
      echo "<tr>";
      echo "<td>";
      // echo "<a href=$link>".$row['id']."</a>";
      echo $row['id'];
      echo "</td>";
      echo "<td>";
      // echo "<a href=$link>".$row['address_1']."</a>";
      echo $row['address_1'];
      echo "</td>";
      echo "<td>";
      // echo "<a href=$link>".$row['address_2']."</a>";
      echo $row['address_2'];
      echo "</td>";
      echo "<td>";
      // echo "<a href=$link>".$row['city']."</a>";
      echo $row['city'];
      echo "</td>";
      echo "<td>";
      echo "<a href=$link>".$lat."</a>";
      echo "</td>";
      echo "<td>";
      echo "<a href=$link>".$lng."</a>";
      echo "</td>";
      echo "<td>";
      // echo "<a href=$link>".$ver."</a>";
      echo $ver;
      echo "</td>";
      echo "<td>";
      echo "<a href=$tomLink>".$tomLat."</a>";
      echo "</td>";
      echo "<td>";
      echo "<a href=$tomLink>".$tomLng."</a>";
      echo "</td>";
    }

  }
  else {
    echo "<td>----</td>";
    echo "<td>----</td>";
  }

  echo "<td>".$tomAddress."</td>";
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
