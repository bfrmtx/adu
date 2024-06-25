<?php

/**
 * @mainpage
 * ADU-08e ADU-10e web interface for mobile platforms
 */
if (!isset($_SESSION)) session_start();
if (!empty($_POST)) {
  // go to position guess does not work
  header("location:index.php"); // your current page
}

$_SESSION["channel_debug"] = 0;
$_SESSION["adu_debug"] = 0;
$_SESSION["database_debug"] = 0;
$_SESSION["job_debug"] = 0;
$_SESSION["status_file"] = 0;

// ----------------- check the ADU session variable -> move into and take from database

// that comes later with a button
if (!isset($_SESSION["ADU"]))       $_SESSION["ADU"] = "ADU-10e";
require_once('system/php_functions.php');
require_once('system/job_time.php');
$job = new job_time($_SESSION["ADU"], "MT", dirname(__FILE__) . "/tmp/job.sql3");
print_header("Job List");
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

  <div class="w3-main" style="margin-left:50px">
    <div class="w3-row w3-padding-32">
      <div class="w3-full w3-container">
        <h2 class="w3-text-deep-orange"> <?php echo $job->select_sampling_rate(); ?>
        </h2>
      </div>
    </div>

    <div class="w3-row w3-padding-32">
      <!-- the start and stop time -->
      <div class="w3-half w3-container"> <!-- begin start time -->
        <h1 class="w3-text-deep-orange">Start</h1>
        <?php
        echo get_datepicker_value("start_date");
        echo $job->date_picker("start_date");
        slider_value_display("", "start_hours_slider");
        slider_value_display(":", "start_minutes_slider");
        slider_value_display(":", "start_seconds_slider");
        echo "<br>";
        echo "<br>";
        echo $job->select_start_time("start_hours");
        get_slider_innerHTML("start_hours", "start_hours_slider");
        echo "<br>";

        echo $job->select_start_time("start_minutes");
        get_slider_innerHTML("start_minutes", "start_minutes_slider");
        echo "<br>";

        echo $job->select_start_time("start_seconds");
        get_slider_innerHTML("start_seconds", "start_seconds_slider");
        ?>
      </div> <!-- end start time -->

      <div class="w3-half w3-container"> <!-- begin stop time -->
        <h1 class="w3-text-deep-orange">Stop</h1>
        <?php
        echo get_datepicker_value("stop_date");
        echo $job->date_picker("stop_date");
        slider_value_display("", "stop_hours_slider");
        slider_value_display(":", "stop_minutes_slider");
        slider_value_display(":", "stop_seconds_slider");
        echo "<br>";
        echo "<br>";

        echo $job->select_stop_time("stop_hours");
        get_slider_innerHTML("stop_hours", "stop_hours_slider");
        echo "<br>";

        echo $job->select_stop_time("stop_minutes");
        get_slider_innerHTML("stop_minutes", "stop_minutes_slider");
        echo "<br>";

        echo $job->select_stop_time("stop_seconds");
        get_slider_innerHTML("stop_seconds", "stop_seconds_slider");
        ?>

      </div> <!-- end stop time -->
    </div> <!-- end start and stop time -->
    <div class="w3-row w3-padding-32">
      <div class="w3-third w3-container">
        <h2 class="w3-text-deep-orange">RR Grid</h2>
        <?php echo $job->toggle_grid_mode_btn(); ?>
      </div>
      <div class="w3-twothird w3-container">
        <p class="w3-border w3-padding-large w3-padding-32 w3-center">Grid mode set start time to a 64s grid</p>
      </div>

      <div class="w3-third w3-container">
        <h2 class="w3-text-deep-orange">To Joblist</h2>
        <?php echo $job->job->submit_to_joblist_btn(); ?>
      </div>

      <div class="w3-third w3-container">
        <h2 class="w3-text-deep-orange">Update Joblist</h2>
        <?php echo $job->job->update_joblist_btn(); ?>
      </div>
    </div>


  </div>



  <div class="w3-row w3-padding-32">
    <div class="w3-row w3-padding-32">
      <div class="w3-full w3-container">
        <h2 class="w3-text-deep-orange"> UTC offset: <?php echo $job->kv['utc_offset']; ?> hrs </h2>
      </div>
    </div>

  </div>
  <br>


  </div> <!-- END MAIN div w3-main-->

</body>

</html>