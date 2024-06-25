<?php

if (!isset($_SESSION)) session_start();
if (!empty($_POST)) {
  // go to position guess does not work
  header("location:jl.php"); // your current page
}
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
  <h6 id="supertimings" class="hidden">This is a hidden heading</h6>
  <h6 id="supertimings" class="hidden">This is a hidden heading</h6>

  <?php
  $job->job->show_joblist_as_table();

  ?>

</body>

</html>