<?php

require_once('../system/php_functions.php');
require_once('../system/adu_status.php');

$status = new adu_status("ADU-10e", "MT", sys_get_temp_dir() . '/status.sql3');

$status->set_chan_val(2, 'serial', 12);
$status->set_chan_val(3, 'serial', 34);
$status->set_chan_val(4, 'serial', 56);

//$status->set_chan_val(1, 'angle', 90);
//$status->set_chan_val(3, 'angle', 90);



for ($i = 0; $i < 2; $i++) {
  $status->set_chan_val($i, 'sensor', "EFP-06");
}
for ($i = 2; $i < 5; $i++) {
  $status->set_chan_val($i, 'sensor', "MFS-07e");
  $status->set_chan_val($i, 'chopper', "on");
}
