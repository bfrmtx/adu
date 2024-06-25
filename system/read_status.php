<?php
if (!isset($_SESSION)) session_start();
$ex_pdo_file = sys_get_temp_dir() . '/status.sql3';

if (!file_exists(sys_get_temp_dir() . '/status.sql3')) {
  $_SESSION["status_file"] = 0;
  return;
} else {
  $_SESSION["status_file"] = 0;
}
