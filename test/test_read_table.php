 <?php
  if (!isset($_SESSION)) session_start();
  if (!isset($_SESSION["lab_mode"]))       $_SESSION["lab_mode"] = 0;
  if (!isset($_SESSION["channel_debug"]))       $_SESSION["channel_debug"] = 1;
  if (!isset($_SESSION["adu_debug"]))       $_SESSION["adu_debug"] = 1;
  if (!isset($_SESSION["database_debug"]))       $_SESSION["database_debug"] = 1;
  if (!isset($_SESSION["ADU"]))       $_SESSION["ADU"] = "ADU-10e";
  ?>
 <!DOCTYPE html>
 <html>

 <head>
   <title>Embed PHP in a .html File</title>
 </head>

 <body>

   <?php


    echo "hello <br>";
    require_once(dirname(__FILE__) . '/../database/database.php');

    $db = new database("ADU-10e", "MT");
    $db->read_table_contents();

    echo "world <br>";

    ?>
 </body>

 </html>