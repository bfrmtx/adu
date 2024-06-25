<?php
if (!isset($_SESSION)) session_start();
// kvs class
require_once(dirname(__FILE__) . '/adu.php');
require_once(dirname(__FILE__) . '/joblist.php');


class job_time extends adu {

  public $adu_timezones = array(
    '-12:00' => '[UTC - 12] Baker Island Time',
    '-11:00' => '[UTC - 11] Niue Time, Samoa Standard Time',
    '-10:00' => '[UTC - 10] Hawaii-Aleutian Standard Time, Cook Island Time',
    '-09:30' => '[UTC - 9:30] Marquesas Islands Time',
    '-09:00' => '[UTC - 9] Alaska Standard Time, Gambier Island Time',
    '-08:00' => '[UTC - 8] Pacific Standard Time',
    '-07:00' => '[UTC - 7] Mountain Standard Time',
    '-06:00' => '[UTC - 6] Central Standard Time',
    '-05:00' => '[UTC - 5] Eastern Standard Time',
    '-04:30' => '[UTC - 4:30] Venezuelan Standard Time',
    '-04:00' => '[UTC - 4] Atlantic Standard Time',
    '-03:30' => '[UTC - 3:30] Newfoundland Standard Time',
    '-03:00' => '[UTC - 3] Amazon Standard Time, Central Greenland Time',
    '-02:00' => '[UTC - 2] Fernando de Noronha Time, South Georgia Time',
    '-01:00' => '[UTC - 1] Azores Standard Time, Cape Verde, Eastern Greenland Time',
    '00:00' => '[UTC] UTC, Western European Time',
    '01:00' => '[UTC + 1] Central European Time, West African Time',
    '02:00' => '[UTC + 2] Eastern European Time, Central African Time',
    '03:00' => '[UTC + 3] Moscow Standard Time, Eastern African Time',
    '03:30' => '[UTC + 3:30] Iran Standard Time',
    '04:00' => '[UTC + 4] Gulf Standard Time, Samara Standard Time',
    '04:30' => '[UTC + 4:30] Afghanistan Time',
    '05:00' => '[UTC + 5] Pakistan Standard Time, Yekaterinburg Standard Time',
    '05:30' => '[UTC + 5:30] Indian Standard Time, Sri Lanka Time',
    '05:45' => '[UTC + 5:45] Nepal Time',
    '06:00' => '[UTC + 6] Bangladesh Time, Bhutan Time, Novosibirsk Standard Time',
    '06:30' => '[UTC + 6:30] Cocos Islands Time, Myanmar Time',
    '07:00' => '[UTC + 7] Indochina Time, Krasnoyarsk Standard Time',
    '08:00' => '[UTC + 8] Chinese Standard Time, AUS Western Standard Time, Irkutsk ST',
    '08:45' => '[UTC + 8:45] Southeastern Western Australia Standard Time',
    '09:00' => '[UTC + 9] Japan Standard Time, Korea Standard Time, Chita Standard Time',
    '09:30' => '[UTC + 9:30] Australian Central Standard Time',
    '10:00' => '[UTC + 10] Australian Eastern Standard Time, Vladivostok Standard Time',
    '10:30' => '[UTC + 10:30] Lord Howe Standard Time',
    '11:00' => '[UTC + 11] Solomon Island Time, Magadan Standard Time',
    '11:30' => '[UTC + 11:30] Norfolk Island Time',
    '12:00' => '[UTC + 12] New Zealand Time, Fiji Time, Kamchatka Standard Time',
    '12:45' => '[UTC + 12:45] Chatham Islands Time',
    '13:00' => '[UTC + 13] Tonga Time, Phoenix Islands Time',
    '14:00' => '[UTC + 14] Line Island Time'
  );

  use kvs;  //!< share all with kvs - the key value pair trait in database

  public $format = 'Y-m-d H:i:s'; //!< date format
  private $my_start_datetime;
  private $my_stop_datetime;
  private $my_utc_offset;         //!< seconds to SHIFT DISPLAY TIME

  public $job;                    //!< the joblist object

  public function utc_offset_to_seconds($hh_colon_mm, $is_dst) {
    $hh = "00";
    $mm = "00";
    $hs = explode(":", $hh_colon_mm);
    if (isset($hs[0])) $hh = $hs[0];
    else return 0;
    if (isset($hs[1])) $mm = $hs[1];
    else $mm = "00";
    $hh = intval($hh) * 60 * 60;  // hours to seconds
    $mm = intval($mm) * 60;  // minutes to seconds
    if ($hh < 0) $hh = $hh - $mm;
    else $hh = $hh + $mm;
    if (($is_dst != "on") && ($is_dst != "off")) $is_dst = "off";
    if ($is_dst == "on") $hh = $hh + 60 * 60;
    return intval($hh);
  }

  public function utc_offset_to_hh_colon_mm($seconds) {
    $hh = intval($seconds / 60 / 60);
    $mm = intval(($seconds - $hh * 60 * 60) / 60);
    if ($hh < 0) $hh = $hh + 24;
    if ($mm < 0) $mm = $mm + 60;
    return sprintf("%02d:%02d", $hh, $mm);
  }


  public function __construct($name_, $meas_, $pdo_file_) {

    parent::__construct($name_, $meas_, $pdo_file_);  // construct the ADU system
    $this->my_utc_offset = $this->utc_offset_to_seconds($this->kv['utc_offset'], $this->kv['dst']);
    // start_date and start_time are in UTC
    $this->my_start_datetime = DateTime::createFromFormat($this->format, $this->kv['start_date'] . " " . $this->kv['start_time'], new DateTimeZone('UTC'));
    $this->act_time();          // adjust time to now or later
    $this->lc_start_datetime(); // put the start time into the local time zone
    $this->lc_stop_datetime();  // put the stop time into the local time zone
    $this->job = new db_jobs("jobs", $this->pdo_file);  // also creates table jobs if not exists

    $this->job->update_from_job_time($this);  // update the joblist from the job_time object
  }

  public function lc_start_datetime() {
    $this->my_start_datetime = DateTime::createFromFormat($this->format, $this->kv['start_date'] . " " . $this->kv['start_time'], new DateTimeZone('UTC'));
    $this->my_start_datetime->modify($this->my_utc_offset . " seconds");
  }

  public function lc_stop_datetime() {
    if ($this->kv['duration'] < 0) {
      $this->kv['duration'] = 0;
    }
    $this->my_stop_datetime = clone $this->my_start_datetime;  // contains potentially offset
    $this->my_stop_datetime->modify($this->kv['duration'] . " seconds");
  }

  public function back_to_utc_start_datetime() {
    // during startup we converted UTC to local time zone
    // undo this now
    $tmp_duration = -1 *  intval($this->my_utc_offset);
    $this->my_start_datetime->modify($tmp_duration . " seconds");
    $this->kv['start_date'] = $this->my_start_datetime->format("Y-m-d");
    $this->kv['start_time'] = $this->my_start_datetime->format("H:i:s");  // ISO 8601 time in UTC
  }

  public function __destruct() {
    $this->set_adu_offset();
    $this->toggle_dst();
    $this->set_start_seconds();
    $this->set_start_minutes();
    $this->set_start_hours();
    $this->set_start_date();
    $this->set_stop_seconds();
    $this->set_stop_minutes();
    $this->set_stop_hours();
    $this->set_stop_date();
    $this->back_to_utc_start_datetime();    // convert back to UTC 
    $this->toggle_grid_mode();
    $this->calc_grid_time();                // calculate the grid time again

    $this->edit_post();                     // edit the post data

    $this->job->update_from_job_time($this);      // update the joblist from the job_time object if button was pressed
    $this->job->submit_to_joblist($this);         // submit to joblist if button was pressed
    $this->job->update_joblist($this);           // update the joblist if button was pressed

    // can not call a destructor on a newly created database
    if ($this->kv_old != null) $this->update_table();
    if ((isset($_SESSION["job_debug"])) && ($_SESSION["job_debug"] == 1)) {
      echo '<br> ->>>>JOB closed ';
      echo "<br> ->>>> " .  $this->kv['start_date'] . " " . $this->kv['start_time'] . " duration: " .
        seconds_to_time($this->kv['duration']) . "<br>";
    }
    parent::__destruct();                 // destruct the ADU system
  }

  /*!
     * \brief update_from_start shifts the stop time after modification of start time AND sets the duration
     * 
  */
  function update_from_start() {
    $tmp_duration = $this->my_stop_datetime->getTimestamp() - $this->my_start_datetime->getTimestamp();
    if ($tmp_duration > 0) {
      $this->my_stop_datetime = clone $this->my_start_datetime;
      $this->kv['duration'] = $tmp_duration;
      $this->my_stop_datetime->modify($this->kv['duration'] . "seconds");
    } else {
      $this->my_stop_datetime = clone $this->my_start_datetime;
      $this->kv['duration'] = 0;
    }
  }

  function act_time() {
    $xdate = gmdate("Y-m-d");
    $xtime = gmdate("H:i");
    $xtime = $xtime . ":00";
    // time now in UTC
    $tmp_time = DateTime::createFromFormat($this->format, $xdate . " " . $xtime, new DateTimeZone('UTC'));
    // time from database - UTC
    $db_start_datetime = DateTime::createFromFormat($this->format, $this->kv['start_date'] . " " . $this->kv['start_time'], new DateTimeZone('UTC'));
    // next possible start time

    // update the data
    // case one: invalid date time, take the current UTC time
    if ($db_start_datetime === false) {
      $db_start_datetime = clone $tmp_time;
    }
    // case two: old start time has already passed
    else if ($db_start_datetime < $tmp_time) {
      $db_start_datetime = clone $tmp_time;
    }
    $this->my_start_datetime = clone $db_start_datetime;    // have a valid start time, now or FUTURE

    // that is the next possible start date from now
    if ($this->kv['grid_mode'] == "on") {
      $this->calc_grid_time();                              // uses $this->my_start_datetime
      // integer division rounds down; we may have to add 64 seconds
      if ($tmp_time > $this->my_start_datetime) {
        $this->my_start_datetime->modify(64 . "seconds");
      }
    } else {
      // no grid time; but start time is too close; set to next full minute
      if ($tmp_time > $this->my_start_datetime) {
        $this->my_start_datetime->modify(60 . "seconds");
      }
    }
    // finally make a valid start time; we may have added 64 or 60 seconds
    $this->kv['start_date'] = $this->my_start_datetime->format('Y-m-d');
    $this->kv['start_time'] = $this->my_start_datetime->format('H:i:s');
    // and shift the stop time accordingly to the duration
    $this->my_stop_datetime = clone $this->my_start_datetime;
    $this->my_stop_datetime->modify($this->kv['duration'] . "seconds");
  }


  function calc_grid_time() {
    if ($this->kv['grid_mode'] == "off") return;
    $grd = 64;
    $tmps = intval($this->my_start_datetime->getTimestamp());
    $tmps = intval(intval($tmps) / intval($grd));
    $tmps = intval(intval($tmps) * intval($grd));
    $udate = new DateTime('2000-01-01', new DateTimeZone('UTC')); // ensure UTC
    $udate->setTimestamp($tmps);
    if ($udate < $this->my_start_datetime) {
      $udate->modify($grd . "seconds");
    }
    $this->my_start_datetime = clone $udate;
    $this->kv['start_date'] = $this->my_start_datetime->format('Y-m-d');
    $this->kv['start_time'] = $this->my_start_datetime->format('H:i:s');
  }

  // SLIDERS  *********************************************************


  function select_start_time($post_name) {
    $value = 0;
    $min = 0;
    $max = 0;
    $label = "";
    if ($post_name == "start_hours") {
      $value = $this->my_start_datetime->format('H');
      $min = 0;
      $max = 23;
      $label = "set hours";
    }
    if ($post_name == "start_minutes") {
      $value = $this->my_start_datetime->format('i');
      $min = 0;
      $max = 59;
      $label = "set mins";
    }
    if ($post_name == "start_seconds") {
      $value = $this->my_start_datetime->format('s');
      $min = 0;
      $max = 59;
      if ($this->kv['grid_mode'] == "on") {
        $label = "info secs";
      } else $label = "set secs";
      $min = 0;
      $max = 63;
    }

    $form = '<form  method="post" action="">' . PHP_EOL;
    $form .= ' ' . $label . PHP_EOL;
    if (($post_name == "start_seconds") && ($this->kv['grid_mode'] == "on")) {
      $form .= '<input disabled class="slider" type="range" name="' . $post_name . '" id="' . $post_name . '" value="'  . $value . '" ';
    } else {
      $form .= '<input class="slider" type="range" name="' . $post_name . '" id="' . $post_name . '" value="'  . $value . '" ';
    }
    $form .= 'min="' . $min . '" max="' . $max . '" data-show-value="true" step="1" onchange="this.form.submit()">' . PHP_EOL;
    $form .= '</form>' . PHP_EOL;
    return $form;
  }

  function select_stop_time($post_name) {
    $value = 0;
    $min = 0;
    $max = 0;
    $label = "";
    if ($post_name == "stop_hours") {
      $value = $this->my_stop_datetime->format('H');
      $min = 0;
      $max = 23;
      $label = "set hours";
    }
    if ($post_name == "stop_minutes") {
      $value = $this->my_stop_datetime->format('i');
      $min = 0;
      $max = 59;
      $label = "set mins";
    }
    if ($post_name == "stop_seconds") {
      $value = $this->my_stop_datetime->format('s');
      $min = 0;
      $max = 59;
      $label = "set secs";
    }

    $form = '<form  method="post" action="">' . PHP_EOL;
    $form .= '    set ' . $label . PHP_EOL;
    $form .= '<input class="slider" type="range" name="' . $post_name . '" id="' . $post_name . '" value="'  . $value . '" ';
    $form .= 'min="' . $min . '" max="' . $max . '" data-show-value="true" step="1" onchange="this.form.submit()">' . PHP_EOL;
    $form .= '</form>' . PHP_EOL;
    return $form;
  }

  /*
        <p>
          Start Date: <input type="text" id="datepicker" value="2023-08-12" size="30">
        </p>
*/
  function date_picker($which_date) {
    $label = "";
    $value = 0;
    if ($which_date == "start_date") {
      $label = "start date";
      $value = $this->my_start_datetime->format('Y-m-d');
    } elseif ($which_date == "stop_date") {
      $label = "stop date";
      $value = $this->my_stop_datetime->format('Y-m-d');
    }
    $form = '<form  style="display: inline;" method="post" action="">' . PHP_EOL;
    $form .= '    set ' . $label . PHP_EOL;
    $form .= ' <input  type="text" name="' . $which_date . '" id="' . $which_date . '" size="10" value="' . $value . '"';
    $form .= ' onchange="this.form.submit()">'   . PHP_EOL;
    $form .= '</form>' . PHP_EOL;
    return $form;
  }

  // *********************************  S T A R T ***********************************************

  function set_start_seconds() {
    if (!isset($_POST["start_seconds"])) return;
    $mtime = $this->my_start_datetime->format('H:i') . ':00';
    $mdate = $this->my_start_datetime->format('Y-m-d');
    $this->my_start_datetime = DateTime::createFromFormat($this->format, $mdate . " " . $mtime, new DateTimeZone('UTC'));
    $this->my_start_datetime->modify("+" . $_POST["start_seconds"] . "seconds");
    $this->kv['start_time'] = $this->my_start_datetime->format('H:i:s');
    unset($_POST["start_seconds"]);
  }

  function set_start_minutes() {
    if (!isset($_POST["start_minutes"])) return;
    $mtime = $this->my_start_datetime->format('H') . ':00:' . $this->my_start_datetime->format('s');
    $mdate = $this->my_start_datetime->format('Y-m-d');
    $this->my_start_datetime = DateTime::createFromFormat($this->format, $mdate . " " . $mtime, new DateTimeZone('UTC'));
    $this->my_start_datetime->modify("+" . $_POST["start_minutes"] . "minutes");
    $this->kv['start_time'] = $this->my_start_datetime->format('H:i:s');
    unset($_POST["start_minutes"]);
  }

  function set_start_hours() {
    if (!isset($_POST["start_hours"])) return;
    $mtime = '00:' . $this->my_start_datetime->format('i') . ':' . $this->my_start_datetime->format('s');
    $mdate = $this->my_start_datetime->format('Y-m-d');
    $this->my_start_datetime = DateTime::createFromFormat($this->format, $mdate . " " . $mtime, new DateTimeZone('UTC'));
    $this->my_start_datetime->modify("+" . $_POST["start_hours"] . "hours");
    $this->kv['start_time'] = $this->my_start_datetime->format('H:i:s');
    unset($_POST["start_hours"]);
  }

  function set_start_date() {
    if (!isset($_POST["start_date"])) return;
    $this->my_start_datetime = DateTime::createFromFormat($this->format, $_POST["start_date"] . " " . $this->my_start_datetime->format('H:i:s'), new DateTimeZone('UTC'));
    $this->kv['start_date'] = $this->my_start_datetime->format('Y-m-d');
    unset($_POST["start_date"]);
  }


  // *********************************  S T O P   ***********************************************

  function set_stop_seconds() {
    if (!isset($_POST["stop_seconds"])) return;
    $mtime = $this->my_stop_datetime->format('H:i') . ':00';
    $mdate = $this->my_stop_datetime->format('Y-m-d');
    $this->my_stop_datetime = DateTime::createFromFormat($this->format, $mdate . " " . $mtime, new DateTimeZone('UTC'));
    $this->my_stop_datetime->modify("+" . $_POST["stop_seconds"] . "seconds");
    unset($_POST["stop_seconds"]);
    $this->update_from_start();
  }
  function set_stop_minutes() {
    if (!isset($_POST["stop_minutes"])) return;
    $mtime = $this->my_stop_datetime->format('H') . ':00:' . $this->my_stop_datetime->format('s');
    $mdate = $this->my_stop_datetime->format('Y-m-d');
    $this->my_stop_datetime = DateTime::createFromFormat($this->format, $mdate . " " . $mtime, new DateTimeZone('UTC'));
    $this->my_stop_datetime->modify("+" . $_POST["stop_minutes"] . "minutes");
    unset($_POST["stop_minutes"]);
    $this->update_from_start();
  }

  function set_stop_hours() {
    if (!isset($_POST["stop_hours"])) return;
    $mtime = '00:' . $this->my_stop_datetime->format('i') . ':' . $this->my_stop_datetime->format('s');
    $mdate = $this->my_stop_datetime->format('Y-m-d');
    $this->my_stop_datetime = DateTime::createFromFormat($this->format, $mdate . " " . $mtime, new DateTimeZone('UTC'));
    $this->my_stop_datetime->modify("+" . $_POST["stop_hours"] . "hours");
    unset($_POST["stop_hours"]);
    $this->update_from_start();
  }

  function set_stop_date() {
    if (!isset($_POST["stop_date"])) return;
    $this->my_stop_datetime = DateTime::createFromFormat($this->format, $_POST["stop_date"] . " " . $this->my_stop_datetime->format('H:i:s'), new DateTimeZone('UTC'));
    unset($_POST["stop_date"]);
    $this->update_from_start();
  }

  // string output for date and time  ********************************************************
  // round to minute


  function start_date_time() {
    return $this->my_start_datetime->format($this->format);
  }

  function mday_start() {
    return $this->my_start_datetime->format('Y-m-d');
  }

  function htime_start() {
    return $this->my_start_datetime->format('H');
  }

  function mtime_start() {
    return $this->my_start_datetime->format('i');
  }

  function stime_start() {
    return $this->my_start_datetime->format('s');
  }


  function htime_stop() {
    $this->update_from_start();
    return $this->my_start_datetime->format('H');
  }


  function mtime_stop() {
    $this->update_from_start();
    return $this->my_stop_datetime->format('i');
  }

  // get the seconds
  function stime_stop() {
    $this->update_from_start();
    return $this->my_stop_datetime->format('s');
  }

  function stop_date_time() {
    $this->update_from_start();
    return $this->my_stop_datetime->format($this->format);
  }

  /*!
   *  \brief  select timezone key from adu_timezones array
   */
  function select_adu_timezones($label = "") {
    $form = PHP_EOL . '<form method="POST" action="" >' . $label  . PHP_EOL;
    $form .= '<select name="utc_offset" id="utc_offset" onchange="this.form.submit()">' . PHP_EOL;
    $form .= '<option selected="selected">' . $this->adu_timezones[$this->kv['utc_offset']] . '</option>' . PHP_EOL;
    foreach ($this->adu_timezones as $key => $value) {
      $form .= '<option value="' . $key . '">' . $value . '</option>' . PHP_EOL;
    }
    $form .= '</select>' . PHP_EOL;
    $form .= '</form>' . PHP_EOL;
    return $form;
  }

  function set_adu_offset() {
    if (!isset($_POST["utc_offset"])) return;
    $this->kv["utc_offset"] = $_POST["utc_offset"];
    $this->my_utc_offset = $this->utc_offset_to_seconds($this->kv['utc_offset'], $this->kv['dst']);
    $this->lc_start_datetime();
    $this->lc_stop_datetime();
    unset($_POST["utc_offset"]);
  }


  function toggle_dst_btn($label = "") {
    $str = "DST is " . $this->kv['dst'];
    $form = '<form method="POST" action="" > <b>' . $label . '</b>';
    $form .= "    <input  type=\"submit\"   name=\"toggle_dst\" id=\"toggle_dst\" value=\"" . $str . "\" />";
    $form .= '</form>';
    return $form;
  }

  function toggle_dst() {
    if (isset($_POST['toggle_dst'])) {
      if ($this->kv['dst'] == "off") $this->kv['dst'] = "on";
      else if ($this->kv['dst'] == "on") $this->kv['dst'] = "off";
      $this->my_utc_offset = $this->utc_offset_to_seconds($this->kv['utc_offset'], $this->kv['dst']);
      $this->lc_start_datetime();
      $this->lc_stop_datetime();
      unset($_POST['toggle_dst']);
    }
  }

  function tz_dst_container() {

    $form = '<div class="w3-col w3-container">' . PHP_EOL;
    $form .= '  <h2 class="w3-text-deep-orange">UTC </h2>' . PHP_EOL;
    $form .= '<table align="left" style="width:90%">' . PHP_EOL;
    $form .= '<col style="width:30%">' . PHP_EOL;
    $form .= '<col style="width:70%"> ' . PHP_EOL;
    $form .= '<tr><td><b>UTC Offset</b></td><td>' . $this->select_adu_timezones() . '</td></tr>' . PHP_EOL;
    $form .= '<tr><td><b>DST</b></td><td>' . $this->toggle_dst_btn() . '</td></tr>' . PHP_EOL;
    $form .= '</table>' . PHP_EOL;
    $form .= '</div>' . PHP_EOL;
    return $form;
  }

  function toggle_grid_mode_btn() {
    $str = "grid mode is " . $this->kv['grid_mode'];
    $form = '<form method="POST" action="" >';
    $form .= "    <input  type=\"submit\"   name=\"toggle_grid_mode\" id=\"toggle_grid_mode\" value=\"" . $str . "\" />";
    $form .= '</form>';
    return $form;
  }

  function toggle_grid_mode() {
    if (isset($_POST['toggle_grid_mode'])) {
      if ($this->kv['grid_mode'] == "off") $this->kv['grid_mode'] = "on";
      else if ($this->kv['grid_mode'] == "on") $this->kv['grid_mode'] = "off";
      unset($_POST['grid_mode']);
    }
  }

  // get the edit post from joblist
  function edit_post() {
    if (!isset($_POST["edit_job_id"])) return 0;
    // split edit_job_id into job_id and text
    $tmp = explode(" ", $_POST["edit_job_id"]);
    if (isset($tmp[0])) $this->kv['job_id'] = $tmp[0];
    else return 0;
    $this->kv['job_id'] = $tmp[0];
    unset($_POST["edit_job_id"]);

    // get the joblist entry
    $this->job->update_from_job_from_joblist($this->kv['job_id']);
    // update the job_time object
    // for all keys in job_time object and same keys in joblist object update the job_time object
    foreach ($this->kv as $key => $value) {
      if (isset($this->job->kv[$key])) $this->kv[$key] = $this->job->kv[$key];
    }
    // job choppers and gains are arrays and same size of this->kv['channels'] update each channel
    // size of channels
    $size = $this->kv['channels'];
    if ($size == 0) return 0;
    // size of choppers
    $size_choppers = count($this->job->kv['choppers']);
    // size of gains  
    $size_gains = count($this->job->kv['gains']);
    // if size of choppers and gains is not the same as channels set to default
    // check for auto!
    // if (($size_choppers == $size) && ($size_gains == $size)) {
    //   foreach ($this->channels as $channel) {
    //     $channel->kv['chopper'] = $this->job->kv['choppers'][$channel];
    //     $channel->kv['gain'] = $this->job->kv['gains'][$channel];
    //   }
    // }

    // set POST to value to GO_MAIN_PAGE
    $_POST["GO_MAIN_PAGE"] = 1;
    return $this->kv['job_id'];
  }
} // EO class
