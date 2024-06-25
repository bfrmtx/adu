  <?php
  require_once(dirname(__FILE__) . '/php_functions.php');
  require_once(dirname(__FILE__) . '/measurement.php');
  require_once(dirname(__FILE__) . '/database.php');


  if (!isset($_SESSION)) session_start();

  /*!
  \brief channel(s) are created inside the ADU class
  */
  class channel extends database {
    public $no;       //!< channel number

    public $on;       //!< controls if channel is on or off
    public $meas;     //!< MT, AMT, airborne with 6 channels and 6 magnetic
    public $name;     //!< system name like ADU-10e - do some specific for the system in case


    function __construct($no_, $meas_, &$sampling_rates_, $name_, $pdo_file_) {
      $this->no = $no_;
      $this->on = true;
      $this->meas = $meas_;
      $this->name = $name_;
      $this->sel['sampling_rates'] = $sampling_rates_;
      parent::__construct('ch' . $no_, $pdo_file_);
      // do not read the table content if the database is not there yet
      // will be created by the ADU system
      if (check_pdo($pdo_file_)) {
        $this->read_table_content();
      }
      // on / after creation safe mode is on
      // ADU will set the SESSION variable to on
      if ((isset($_SESSION['safe_mode'])) && ($_SESSION['safe_mode'] == "on")) {
        // in safe mode the sensors are set to auto; the ADU detects the sensor and decides the chopper
        $this->kv['chopper'] = "auto";
        if (isset($this->kv['channel'])) {
          if (str_starts_with($this->kv['channel'], "H")) {
            $this->kv['sensor'] = "auto";
          }
        }
      }
    }


    function __destruct() {
      $this->toggle_chopper();
      $this->set_sensor();
      $this->set_pos();
      $this->set_from_slider("azimuth");
      // can not call $kv_old on a newly created database
      if ($this->kv_old != null) $this->update_table();
      if ((isset($_SESSION["channel_debug"])) && ($_SESSION["channel_debug"] == 1)) {
        echo " channel closed " . $this->no . " chopper:" . $this->kv['chopper'] . "<br>";
      }
    }



    function defaults($sampling_rate) {
      $this->kv['channel'] = default_channel_name($this->meas, $this->no);
      $this->kv['sensor'] = default_sensor_name($this->meas, $this->no);
      $this->kv['chopper'] = default_chopper($sampling_rate);
      $this->kv['azimuth'] = default_angle($this->no);
      $this->kv['serial'] = 0;
      $this->kv['tilt'] = default_dip($this->no);
    }



    function toggle_chopper_btn() {
      $str = "chopper is " . $this->kv['chopper'];
      $str . "&nbsp &nbsp &nbsp &nbsp";
      // that is doing the trick here: in safe mode we supply an invalid value
      $no = $this->no;
      if ($_SESSION['safe_mode'] == "on") {
        $no = 999; // will never be a channel number
        $str = "chopper is auto";
      }
      $form = '<form method="POST" action="" >';
      $form .= "    <input  type=\"submit\"   name=\"toggle_chopper_btn\" id=\"toggle_chopper_btn\" value=\"" . $str . "\" />";
      $form .= "    <input type=\"hidden\"    name=\"chan\"                                         value=\"" . $no . "\" />";
      $form .= '</form>';
      return $form;
    }

    function toggle_chopper() {
      if ((isset($_POST['toggle_chopper_btn'])) && (isset($_POST['chan']))) {
        if (intval($_POST['chan']) == $this->no) {
          if ($this->kv['chopper'] == "off") $this->kv['chopper'] = "on";
          else if ($this->kv['chopper'] == "on") $this->kv['chopper'] = "auto";
          else if ($this->kv['chopper'] == "auto") $this->kv['chopper'] = "off";
          unset($_POST['chan']);
          unset($_POST['toggle_chopper_btn']);
        }
      }
    }

    function select_sensor() {
      $form = PHP_EOL . '<form method="POST" action="" >' . PHP_EOL;
      $form .= '<select name="sensor" id="sensor" onchange="this.form.submit()">' . PHP_EOL;
      $form .= '<option selected="selected">' . $this->kv["sensor"] . '</option>' . PHP_EOL;
      // E-Sensors are only available for E-channels
      if (str_starts_with($this->kv['channel'], "E")) {
        foreach ($this->sel['e-sensors'] as $sensor) {
          if ($sensor != $this->kv["sensor"]) {;
            $form .= '<option value="' . $sensor . '" >' . $sensor . '</option>' . PHP_EOL;
          }
        }
        $form .= '</select>' . PHP_EOL;
        $form .= "    <input type=\"hidden\"    name=\"chan\"  value=\"" . $this->no . "\" />";
        $form .= '</form>';
        return $form;
        // H-Sensors are only available for H-channels; in safe mode off all sensors are available
      } else if (str_starts_with($this->kv['channel'], "H") && ($_SESSION['safe_mode'] == "off")) {
        foreach ($this->sel['h-sensors'] as $sensor) {
          if ($sensor != $this->kv["sensor"]) {;
            $form .= '<option value="' . $sensor . '" >' . $sensor . '</option>' . PHP_EOL;
          }
        }
        $form .= '</select>' . PHP_EOL;
        $form .= "    <input type=\"hidden\"    name=\"chan\"  value=\"" . $this->no . "\" />";
        $form .= '</form>';
        return $form;
        // H-Sensors are only available for H-channels; in safe mode only auto is available
      } else if (str_starts_with($this->kv['channel'], "H") && ($_SESSION['safe_mode'] == "on")) {
        $form .= '<option value="' . $this->kv["sensor"] . '" >' . $this->kv["sensor"] . '</option>' . PHP_EOL;
        $form .= '</select>' . PHP_EOL;
        $form .= "    <input type=\"hidden\"    name=\"chan\"  value=\"" . 999 . "\" />";
        $form .= '</form>';
        return $form;
      }
    }

    function get_status($key, &$value) {
      $value = "";
      if (!isset($this->status)) return false;
      if (isset($this->status[$key])) {
        $value = $this->status[$key];
        return true;
      }
      return false;
    }




    function set_sensor() {
      if ((isset($_POST['sensor'])) && (isset($_POST['chan']))) {
        if (intval($_POST['chan']) == $this->no) {
          $this->kv['sensor'] = $_POST['sensor'];
          unset($_POST['chan']);
          unset($_POST['sensor']);
        }
      }
    }

    function channel_container() {
      $val = "";
      $form = '<div class="w3-half w3-container">' . PHP_EOL;
      $form .= '  <h3 class="w3-text-deep-orange">Channel ' . $this->no . '</h3>' . PHP_EOL;
      $form .= '<table align="left" style="width:90%">' . PHP_EOL;
      $form .= '<col style="width:30%">' . PHP_EOL;
      $form .= '<col style="width:40%"> ' . PHP_EOL;
      $form .= '<col style="width:30%">' . PHP_EOL;
      $this->get_status("channel", $val);
      $form .= "<tr><td>channel type: </td><td><b>" . $this->kv['channel'] . "</b></td> <td><i>" . $val . "</i></td> </tr>" . PHP_EOL;

      $this->get_status("chopper", $val);
      $form .= "<tr><td>toggle chopper</td><td>" . $this->toggle_chopper_btn() . "</td><td><i>" . $val . "</i></td> </tr>" . PHP_EOL;

      $this->get_status("sensor", $val);
      $form .= "<tr><td>set sensor</td><td>" . $this->select_sensor() . "</td><td><i>" . $val . "</i></td></tr>" . PHP_EOL;

      $form .= '</table>' . PHP_EOL;

      $form .= '</div>' . PHP_EOL;

      return $form;
    }

    function pos_container() {
      $form = '<div class="w3-col w3-container">' . PHP_EOL;
      $form .= '  <h3 class="w3-text-deep-orange">Position ' . $this->kv['channel'] . '</h3>' . PHP_EOL;
      $form .= '<table align="left" style="width:90%">' . PHP_EOL;
      $form .= '<col style="width:30%">' . PHP_EOL;
      $form .= '<col style="width:30%">' . PHP_EOL;
      $form .= '<col style="width:40%">' . PHP_EOL;
      $sum = floatval($this->kv['ne_pos']) + floatval($this->kv['sw_pos']);
      $label_1 = "";
      $label_2 = "";
      if (is_x($this->kv['channel'])) {
        $label_1 = "N";
        $label_2 = "S";
      } elseif (is_y($this->kv['channel'])) {
        $label_1 = "E";
        $label_2 = "W";
      }
      $form .= '<tr><td>' . post_string_onblur_chan($label_1, "ne_pos", $this->kv['ne_pos'], 12, $this->no)
        . '</td><td>' . post_string_onblur_chan($label_2, "sw_pos", $this->kv['sw_pos'], 12, $this->no)
        . '</td> <td  style="text-align: left; vertical-align: bottom;"> ' . $sum . ' [m]</td> </tr>' . PHP_EOL;
      $form .= '</table>' . PHP_EOL;
      $form .= "    <input type=\"hidden\"    name=\"chan\"  value=\"" . $this->no . "\" />";

      $form .= '</div>' . PHP_EOL;
      return $form;
    }

    function set_pos() {
      if ((isset($_POST['ne_pos'])) && (isset($_POST['chan']))) {
        if (intval($_POST['chan']) == $this->no) {
          $this->kv['ne_pos'] = abs(floatval($_POST['ne_pos']));
          unset($_POST['chan']);
          unset($_POST['ne_pos']);
        }
      } else if ((isset($_POST['sw_pos'])) && (isset($_POST['chan']))) {
        if (intval($_POST['chan']) == $this->no) {
          $this->kv['sw_pos'] = abs(floatval($_POST['sw_pos']));
          unset($_POST['chan']);
          unset($_POST['sw_pos']);
        }
      }
    }



    // function rot_container() {
    //   $form = '<div class="w3-col w3-container">' . PHP_EOL;
    //   $form .= '  <h3 class="w3-text-deep-orange">Rotation ' . $this->kv['channel'] . '</h3>' . PHP_EOL;
    //   $form .= '<table align="left" style="width:90%">' . PHP_EOL;
    //   $form .= '<col style="width:30%">' . PHP_EOL;
    //   $form .= '<col style="width:40%"> ' . PHP_EOL;
    //   $form .= '<col style="width:30%">' . PHP_EOL;
    //   $form .= "<tr><td>azimuth: </td><td><b>" . $this->kv['azimuth'] . "</b></td> <td></td> </tr>" . PHP_EOL;
    //   $form .= "<tr><td>tilt: </td><td><b>" . $this->kv['tilt'] . "</b></td> <td></td> </tr>" . PHP_EOL;
    //   $form .= '</table>' . PHP_EOL;
    //   $form .= '</div>' . PHP_EOL;
    //   return $form;
    // }



    function rot_container() {
      $form = '<div class="w3-col w3-container">' . PHP_EOL;
      $form .= '  <h3 class="w3-text-deep-orange">Rotation ' . $this->kv['channel'] . '</h3>' . PHP_EOL;
      $form .= '<table align="left" style="width:90%">' . PHP_EOL;
      $form .= '<col style="width:40%">' . PHP_EOL;
      $form .= '<col style="width:10%"> ' . PHP_EOL;
      $form .= '<col style="width:40%">' . PHP_EOL;
      $form .= '<col style="width:10%">' . PHP_EOL;
      $form .= '<tr><td>' . $this->select_slider("azimuth") . '</td><td>' . $this->slider_value_display("deg:", "azimuth") . '</td> <td>tilt: </td><td>' . $this->kv['tilt'] . '</td> </tr>' . PHP_EOL;
      $form .= '</table>' . PHP_EOL;
      $form .= '</div>' . PHP_EOL;
      return $form;
    }


    function select_slider($what) {

      $value = $this->kv[$what];
      $min = 0;
      $max = 0;
      if ($what == "azimuth") {
        $min = 0;
        $max = 89;
      } else if ($what == "tilt") {
        $min = 0;
        $max = 89;
      }
      $step = 1;
      $post_name = $what . '_' . $this->no;
      $form  = PHP_EOL . '<form method="POST" action="" > Azimuth: ' . PHP_EOL;
      $form .= '<input class="slider" width="100" type="range" name="' . $post_name . '" id="' . $post_name . '" value="' . $value;
      $form .= '" min="' . $min . '" max="' . $max . '" step="' . $step . '" data-show-value="true" onchange="this.form.submit()">' . PHP_EOL;
      $form .= '</form>' . PHP_EOL;
      return $form;
    }

    function set_from_slider($what) {
      $post_name = $what . '_' . $this->no;
      if (isset($_POST[$post_name])) {
        // split string at _ and take the second part
        $this->kv[$what] = intval($_POST[$post_name]);
        unset($_POST[$post_name]);
      }
    }

    function slider_value_display($prefix, $what) {
      $post_name = $what . '_' . $this->no;
      return $prefix . '<span id="' . $post_name . '_slider"></span>' . PHP_EOL;
    }



    function get_slider_innerHTML($what) {
      $post_name = $what . '_' . $this->no;
      $slider_display = $post_name . '_slider';
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
  } // EO class
  ?>
