<?php
function default_channel_name($meas, $no) {

  if (($meas == "MT") || ($meas == "AMT")) {
    if ($no == 0) return "Ex";
    else if ($no == 1) return "Ey";
    else if ($no == 2) return "Hx";
    else if ($no == 3) return "Hy";
    else if ($no == 4) return "Hz";
  }
  if (($meas == "airborne")) {
    if ($no == 0) return "Hx";
    else if ($no == 1) return "Hy";
    else if ($no == 2) return "Hz";
    else if ($no == 3) return "Hx";
    else if ($no == 4) return "Hy";
    else if ($no == 5) return "Hz";
  }
}


function default_chopper($sampling_rate) {
  if ($sampling_rate < 1024) return "on";
  else return "off";
}

function default_frequency($meas, $rates) {
  if ($meas == "MT") {
    if (in_array(512, $rates)) {
      return 512;
    }
  } else return end($rates);
}

function default_angle($no) {
  if ($no == 0) return 0;
  else if ($no == 1) return 90;
  else if ($no == 2) return 0;
  else if ($no == 3) return 90;
  else if ($no == 4) return 0;
}

function default_dip($no) {
  if ($no == 0) return 0;
  else if ($no == 1) return 0;
  else if ($no == 2) return 0;
  else if ($no == 3) return 0;
  else if ($no == 4) return 90;
}

function default_sensor_name($meas, $no) {

  if ($meas == "MT") {
    if ($no == 0) return "EFP-06";
    else if ($no == 1) return "EFP-06";
    else if ($no == 2) return "auto";
    else if ($no == 3) return "auto";
    else if ($no == 4) return "auto";
  }

  if ($meas == "AMT") {
    if ($no == 0) return "EFP-06";
    else if ($no == 1) return "EFP-06";
    else if ($no == 2) return "auto";
    else if ($no == 3) return "auto";
    else if ($no == 4) return "auto";
  }

  if (($meas == "airborne")) {
    if ($no == 0) return "auto";
    else if ($no == 1) return "auto";
    else if ($no == 2) return "auto";
    else if ($no == 3) return "auto";
    else if ($no == 4) return "auto";
    else if ($no == 5) return "auto";
  }
}
