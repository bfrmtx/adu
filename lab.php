<?php

/**
 * @mainpage
 * ADU-08e ADU-10e web interface for mobile platforms
 */
if (!isset($_SESSION)) session_start();
if (!empty($_POST)) {
  // go to position guess does not work
  header("location:lab.php"); // your current page
}

// if (!isset($_SESSION["channel_debug"]))       $_SESSION["channel_debug"] = 1;
// if (!isset($_SESSION["adu_debug"]))       $_SESSION["adu_debug"] = 1;
// if (!isset($_SESSION["database_debug"]))       $_SESSION["database_debug"] = 1;
// if (!isset($_SESSION["ADU"]))       $_SESSION["ADU"] = "ADU-10e";
require_once('system/php_functions.php');
require_once('system/job_time.php');
$job = new job_time($_SESSION["ADU"], "MT", dirname(__FILE__) . "/tmp/job.sql3");

?>

<!DOCTYPE html>
<html lang="en">
<title>Lab Mode DANGER</title>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="./css/w3.css">
  <link rel="stylesheet" href="./css/w3-theme-black.css">
  <link rel="stylesheet" href="./css/nav.css">

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
  if ($job->is_safe_mode()) {
    include  dirname(__FILE__) . "/css/html/top_navbar.html";
    echo '<body style="background-color:WhiteSmoke;">';
  } else {
    echo '<body style="background-color:LightBlue;">';
    include  dirname(__FILE__) . "/css/html/top_navbar_lab.html";
  }
  ?>

  <!-- take this to move the page down below the navbar when accessing the timings which would be covered by the navbar otherwise -->
  <h6 id="supertimings" class="hidden">This is a hidden heading</h6>


  <div class="w3-main" style="margin-left:50px">
    <div class="w3-row w3-padding-32">
      <div class="w3-third w3-container">
        <h2 class="w3-text-deep-orange">Safe Mode</h2>
        <?php echo $job->toggle_safe_mode_btn(); ?>
      </div>
      <div class="w3-twothird w3-container">
        <p class="w3-border w3-padding-large w3-padding-32 w3-center">Let the ADU decide for most settings</p>
      </div>

      <div class="w3-third w3-container">
        <h2 class="w3-text-deep-orange">Lab Mode</h2>
        <?php echo $job->toggle_lab_mode_btn(); ?>
      </div>
      <div class="w3-twothird w3-container">
        <p class="w3-border w3-padding-large w3-padding-32 w3-center">Allow Testing</p>
      </div>
    </div>
    <!-- force new boxes  -->
    <div class="w3-row w3-padding-32">
      <p> </p>
      <?php echo $job->tz_dst_container(); ?>
    </div>


  </div> <!-- END MAIN div w3-main-->

</body>

</html>