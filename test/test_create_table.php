<!DOCTYPE html>
<html>

<head>
  <title Create ADU Hardware & Job Table</title>
</head>

<body>

  <?php
  if (!isset($_SESSION)) session_start();
  if (!isset($_SESSION["lab_mode"]))       $_SESSION["lab_mode"] = 0;
  if (!isset($_SESSION["channel_debug"]))       $_SESSION["channel_debug"] = 1;
  if (!isset($_SESSION["adu_debug"]))       $_SESSION["adu_debug"] = 1;
  if (!isset($_SESSION["database_debug"]))       $_SESSION["database_debug"] = 1;
  if (!isset($_SESSION["ADU"]))       $_SESSION["ADU"] = "ADU-10e";
  echo "hello <br>";
  require_once('../adu/adu.php');

  $sys = new adu("ADU-10e", "MT");

  echo "world <br>";

  ?>
</body>

</html>
