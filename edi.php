<?php

/**
 * @system EDI
 * ADU-08e ADU-10e web interface for mobile platforms
 */
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION["ADU"]))       $_SESSION["ADU"] = "ADU-10e";

if (!empty($_POST)) {
  // go to position guess does not work
  header("location:edi.php"); // your current page
}

echo " <br>";
echo " <br>";
echo " <br>";
require_once('system/php_functions.php');
require_once('system/edi_file.php');

$sysedi = new edi($_SESSION["ADU"], dirname(__FILE__) . "/tmp/job.sql3");
?>

<!DOCTYPE html>
<html lang="en">
<title>ADU EDI</title>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="./css/w3.css">
  <link rel="stylesheet" href="./css/w3-theme-black.css">
  <link rel="stylesheet" href="./css/nav.css">

  <script src="js/datepicker/jquery.min.js"></script>
  <script src="js/datepicker/jquery-ui.min.js"></script>
  <link rel="stylesheet" href="./js/datepicker/jquery-ui.min.css">


  <style type="text/css">
    .input-group {
      width: 110px;
      margin-bottom: 10px;
    }

    .pull-center {
      margin-left: auto;
      margin-right: auto;
    }

    @media (min-width: 768px) {
      .container {
        max-width: 730px;
      }
    }

    @media (max-width: 767px) {
      .pull-center {
        float: right;
      }
    }
  </style>
  <style>
    h6.hidden {
      visibility: hidden;
    }
  </style>
  <link rel="icon" type="image/png" href="./logo.png" />

  <style>
    html,
    body,
    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
      font-family: sans-serif;
    }
  </style>

</head>

<body>
  <!-- TOP Navbar -->
  <?php
  if ((isset($_SESSION['safe_mode'])) && ($_SESSION['safe_mode'] == "on")) {
    include  dirname(__FILE__) . "/css/html/top_navbar.html";
    echo '<body style="background-color:WhiteSmoke;">';
  } else {
    echo '<body style="background-color:LightBlue;">';
    include  dirname(__FILE__) . "/css/html/top_navbar_lab.html";
  }

  //if ($sysedi->is_safe_mode()) {
  include  dirname(__FILE__) . "/css/html/top_navbar.html";
  echo '<body style="background-color:WhiteSmoke;">';
  //} else {
  //  echo '<body style="background-color:LightBlue;">';
  //  include  dirname(__FILE__) . "/css/html/top_navbar_lab.html";
  //}
  ?>

  <!-- take this to move the page down below the navbar when accessing the timings which would be covered by the navbar otherwise -->
  <h6 id="supertimings" class="hidden">This is a hidden heading</h6>


  <div class="w3-main" style="margin-left:50px">
    <?php $sysedi->create_fields(); ?>
  </div>

</body>

</html>