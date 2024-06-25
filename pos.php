<?php

/**
 * @mainpage
 * ADU-08e ADU-10e web interface for mobile platforms
 */
if (!isset($_SESSION)) session_start();
if (!empty($_POST)) {
  // go to position guess does not work
  header("location:pos.php"); // your current page
}

// if (!isset($_SESSION["channel_debug"]))       $_SESSION["channel_debug"] = 1;
// if (!isset($_SESSION["adu_debug"]))       $_SESSION["adu_debug"] = 1;
// if (!isset($_SESSION["database_debug"]))       $_SESSION["database_debug"] = 1;
// if (!isset($_SESSION["ADU"]))       $_SESSION["ADU"] = "ADU-10e";
require_once('system/php_functions.php');
require_once('system/job_time.php');
$job = new job_time($_SESSION["ADU"], "MT", dirname(__FILE__) . "/tmp/job.sql3");
print_header("Positioning");
?>

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
  <h6 id="supertimings" class="hidden">This is a hidden heading</h6>


  <div class="w3-main" style="margin-left:50px">

    <?php
    echo $job->pos_containers(0);


    // echo $job->channels[0]->select_slider("azimuth");
    //echo $job->channels[0]->slider_value_display("deg:", "azimuth");


    //echo $job->channels[0]->get_slider_innerHTML("azimuth");

    // echo $job->channels[0]->kv["azimuth"];

    echo $job->rot_containers(0)


    ?>


  </div> <!-- END MAIN div w3-main-->

</body>

</html>