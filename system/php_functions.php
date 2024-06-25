 <?php
  // SLIDERS *********************************************************

  function get_slider_innerHTML($post_name, $slider_display) {
    $str = PHP_EOL . '<script>' . PHP_EOL;
    $str .= 'var sliders_' . $post_name . ' = document.getElementById("' . $post_name . '");' . PHP_EOL;
    $str .= 'var outputs_' . $post_name . ' = document.getElementById("' . $slider_display . '");' . PHP_EOL;
    $str .= 'outputs_' . $post_name . '.innerHTML = sliders_' . $post_name . '.value;' . PHP_EOL;
    $str .= 'sliders_' . $post_name . '.oninput = function() {' . PHP_EOL;
    $str .= 'outputs_' .  $post_name . '.innerHTML = this.value;' . PHP_EOL;
    $str .= '}' . PHP_EOL;
    $str .= '</script>' . PHP_EOL;
    echo $str;
  }

  function slider_value_display($prefix, $slider_display) {
    echo $prefix . '<span id="' . $slider_display . '"></span>' . PHP_EOL;
  }

  // function get_datepicker_value($which_date) {
  //   $str = PHP_EOL . '<script>' . PHP_EOL;
  //   $str .= '$(function() { ' . PHP_EOL;
  //   $str .= '  let $datepicker = $("#' . $which_date . '");' . PHP_EOL;
  //   $str .= '  $datepicker.datepicker({' . PHP_EOL;
  //   $str .=  ' dateFormat: "yy-mm-dd"' . PHP_EOL;
  //   $str .= '});' . PHP_EOL;

  //   $str .= '  $format.on("change", function() { ' . PHP_EOL;
  //   $str .= '  $datepicker.datepicker("option", "dateFormat", this.value);' . PHP_EOL;
  //   $str .= '  });' . PHP_EOL;
  //   $str .= '});' . PHP_EOL;
  //   $str .= '</script>' . PHP_EOL;
  //   return $str;
  // }
  function get_datepicker_value($which_date) {
    $str = PHP_EOL . '<script>' . PHP_EOL;
    $str .= '$(function() { ' . PHP_EOL;
    $str .= '  let $datepicker = $("#' . $which_date . '");' . PHP_EOL;
    $str .= '  $datepicker.datepicker({' . PHP_EOL;
    $str .=  ' dateFormat: "yy-mm-dd"' . PHP_EOL;
    $str .= '});' . PHP_EOL;

    $str .= '  $datepicker.on("change", function() { ' . PHP_EOL; // Fix: replaced $format with $datepicker
    $str .= '  $datepicker.datepicker("option", "dateFormat", this.value);' . PHP_EOL;
    $str .= '  });' . PHP_EOL;
    $str .= '});' . PHP_EOL;
    $str .= '</script>' . PHP_EOL;
    return $str;
  }

  function post_string_onkeyup($label, $post_name, $value, $size) {
    $form = '<form  style="display: inline;" method="post" action="">' . PHP_EOL;
    $form .= ' ' . $label . ' ' . PHP_EOL;
    $form .= ' <input  type="text" name="' . $post_name . '" id="' . $post_name . '" size="' . $size . '" value="' . $value . '"';
    $form .= ' onkeyup="this.form.submit()">'   . PHP_EOL;
    $form .= '</form>' . PHP_EOL;
    return $form;
  }
  function post_number_onkeyup($label, $post_name, $value, $size) {
    $form = '<form  style="display: inline;" method="post" action="">' . PHP_EOL;
    $form .= ' ' . $label . ' ' . PHP_EOL;
    $form .= ' <input  type="number" name="' . $post_name . '" id="' . $post_name . '" size="' . $size . '" value="' . $value . '"';
    $form .= ' onkeyup="this.form.submit()">'   . PHP_EOL;
    $form .= '</form>' . PHP_EOL;
    return $form;
  }


  function post_string_onblur($label, $post_name, $value, $size) {
    $form = '<form  style="display: inline;" method="post" action="">' . PHP_EOL;
    $form .= ' ' . $label . ' ' . PHP_EOL;
    $form .= ' <input  type="text" name="' . $post_name . '" id="' . $post_name . '" size="' . $size . '" value="' . $value . '"';
    $form .= ' onblur="this.form.submit()">'   . PHP_EOL;
    $form .= '</form>' . PHP_EOL;
    return $form;
  }

  function post_string_onblur_chan($label, $post_name, $value, $size, $no) {
    $form = '<form  style="display: inline;" method="post" action="">' . PHP_EOL;
    $form .= ' ' . $label . ' ' . PHP_EOL;
    $form .= ' <input  type="text" name="' . $post_name . '" id="' . $post_name . '" size="' . $size . '" value="' . $value . '"';
    $form .= ' onblur="this.form.submit()">'   . PHP_EOL;
    $form .= "    <input type=\"hidden\"    name=\"chan\"  value=\"" . $no . "\" />";
    $form .= '</form>' . PHP_EOL;
    return $form;
  }

  function post_number_onblur($label, $post_name, $value, $size) {
    $form = '<form  style="display: inline;" method="post" action="">' . PHP_EOL;
    $form .= ' ' . $label . ' ' . PHP_EOL;
    $form .= ' <input  type="number" name="' . $post_name . '" id="' . $post_name . '" size="' . $size . '" value="' . $value . '"';
    $form .= ' onblur="this.form.submit()">'   . PHP_EOL;
    $form .= '</form>' . PHP_EOL;
    return $form;
  }

  function post_number_change($label, $post_name, $value, $size) {
    $form = '<form  style="display: inline;" method="post" action="">' . PHP_EOL;
    $form .= ' ' . $label . ' ' . PHP_EOL;
    $form .= ' <input  type="number" name="' . $post_name . '" id="' . $post_name . '" size="' . $size . '" value="' . $value . '"';
    $form .= ' onkeyup="this.form.submit()">'   . PHP_EOL;
    $form .= '</form>' . PHP_EOL;
    return $form;
  }


  function check_pdo($pdo_file_) {
    $ok = true;
    if (!file_exists($pdo_file_)) {
      $ok = false;
    } else if ((file_exists($pdo_file_)) && (filesize($pdo_file_) < 5)) {
      $ok = false;
    }
    return $ok;
  }

  function seconds_to_time($secs, $always_show_days = false) {
    $duration = $secs;
    $days = floor($duration / (24 * 60 * 60));
    $duration = $duration - ($days * 24 * 60 * 60);
    $hours = floor($duration / (60 * 60));
    $duration = $duration - ($hours * 60 * 60);
    $minutes = floor($duration / 60);
    $duration = $duration - ($minutes * 60);
    $seconds = floor($duration);
    if (($days > 0) || ($always_show_days)) {
      return $days . " days " . $hours . ":" . $minutes . ":" . $seconds;
    } else {
      return $hours . ":" . $minutes . ":" . $seconds;
    }
  }

  function is_E($channel) {
    if (str_starts_with($channel, "E")) return true;
    return false;
  }

  function is_H($channel) {
    if (str_starts_with($channel, "H")) return true;
    return false;
  }

  function is_x($channel) {
    if (str_ends_with($channel, "x")) return true;
    return false;
  }

  function is_y($channel) {
    if (str_ends_with($channel, "y")) return true;
    return false;
  }

  function is_z($channel) {
    if (str_ends_with($channel, "z")) return true;
    return false;
  }

  function print_header($page_title) {
    echo '<!DOCTYPE html>' . PHP_EOL;
    echo '<html lang="en">' . PHP_EOL;
    echo '<title>' . $page_title . '</title>' . PHP_EOL;
    echo '<head>' . PHP_EOL;
    echo '  <meta charset="UTF-8">' . PHP_EOL;
    echo '  <meta name="viewport" content="width=device-width, initial-scale=1">' . PHP_EOL;
    echo '  <link rel="stylesheet" href="./css/w3.css">' . PHP_EOL;
    echo '  <link rel="stylesheet" href="./css/w3-theme-black.css">' . PHP_EOL;
    echo '  <link rel="stylesheet" href="./css/nav.css">' . PHP_EOL;
    echo '  <script src="./js/datepicker/jquery.min.js"></script>' . PHP_EOL;
    echo '  <script src="./js/datepicker/jquery-ui.min.js"></script>' . PHP_EOL;
    echo '  <link rel="stylesheet" href="./js/datepicker/jquery-ui.min.css">' . PHP_EOL;
    echo '  <link rel="icon" type="image/png" href="./logo.png" />' . PHP_EOL;
    echo '  <style type="text/css">' . PHP_EOL;
    echo '    .input-group {' . PHP_EOL;
    echo '      width: 110px;' . PHP_EOL;
    echo '      margin-bottom: 10px;' . PHP_EOL;
    echo '    }' . PHP_EOL;
    echo '    .pull-center {' . PHP_EOL;
    echo '      margin-left: auto;' . PHP_EOL;
    echo '      margin-right: auto;' . PHP_EOL;
    echo '    }' . PHP_EOL;
    echo '    @media (min-width: 768px) {' . PHP_EOL;
    echo '      .container {' . PHP_EOL;
    echo '        max-width: 730px;' . PHP_EOL;
    echo '      }' . PHP_EOL;
    echo '    }' . PHP_EOL;
    echo '    @media (max-width: 767px) {' . PHP_EOL;
    echo '      .pull-center {' . PHP_EOL;
    echo '        float: right;' . PHP_EOL;
    echo '      }' . PHP_EOL;
    echo '    }' . PHP_EOL;
    echo '  </style>' . PHP_EOL;
    echo '  <style>' . PHP_EOL;
    echo '    h6.hidden {' . PHP_EOL;
    echo '      visibility: hidden;' . PHP_EOL;
    echo '    }' . PHP_EOL;
    echo '  </style>' . PHP_EOL;
    echo '  <style>' . PHP_EOL;
    echo '    html,' . PHP_EOL;
    echo '    body,' . PHP_EOL;
    echo '    h1,' . PHP_EOL;
    echo '    h2,' . PHP_EOL;
    echo '    h3,' . PHP_EOL;
    echo '    h4,' . PHP_EOL;
    echo '    h5,' . PHP_EOL;
    echo '    h6 {' . PHP_EOL;
    echo '      font-family: sans-serif;' . PHP_EOL;
    echo '    }' . PHP_EOL;
    echo '  </style>' . PHP_EOL;
    // implement a function taking a string and display a popup
    echo '  <script>' . PHP_EOL;
    echo '    function showMessage(message) {' . PHP_EOL;
    echo '      if (message != "") {' . PHP_EOL;
    echo '        alert(message);' . PHP_EOL;
    echo '      }' . PHP_EOL;
    echo '    }' . PHP_EOL;
    echo '  </script>' . PHP_EOL;
    echo '</head>' . PHP_EOL;
  }
