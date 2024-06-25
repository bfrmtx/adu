 <?php

  require_once(dirname(__FILE__) . '/channel.php');




  if (!isset($_SESSION)) session_start();

  class adu extends database {

    // use job_time;     //!< aka lifetime; share all with job_time -> and kvs
    public $meas;     //!< MT (5 channels), AMT, airborne with 6 channels and 6 magnetic or 3 H
    public $name;     //!< system name like ADU-10e, ADU-11e, ADU-12e, ADU-08e
    public $channels = array(); //!< the channels itself
    public $pdo_file; //!< the database file


    /*!
  * \brief create the database and fill it with the values from the json files
  * \details
  * \param[in] $name_ name of the system ADU-10e, ADU-11e, ADU-12e, ADU-08e
  * \param[in] $meas_ measurement type
  * \param[in] $pdo_file_ database file : specify the filename to open!; if not exits, create a new one from templates using $name_
  */
    function __construct($name_, $meas_, $pdo_file_) {
      $create_new = false;
      $this->meas = $meas_;
      $this->name = $name_;
      $this->pdo_file = $pdo_file_;

      // create a new database file in case before we set up a database connection
      if (!file_exists($pdo_file_)) {
        $create_new = true;
        // echo  $pdo_file_ . " no sql file<br>";
      } else if ((file_exists($pdo_file_)) && (filesize($pdo_file_) < 5)) {
        $create_new = true;
        unlink($pdo_file_);
      }


      // parse the json files and create the database in case
      if ($create_new) {
        //  adu specific
        $this->pdo_file = $_SERVER['DOCUMENT_ROOT'] . '/tmp/job.sql3';
        parent::__construct("adu", $this->pdo_file);

        $json_file = file_get_contents(dirname(__FILE__) . '/json/' . $this->name . '/system.json');
        $this->kv = json_decode($json_file, true);
        // fetch the array - that is defined in system.json
        $this->sel['sampling_rates'] = $this->kv['sampling_rates'];
        $this->kv['sampling_rate'] = default_frequency($this->meas, $this->sel['sampling_rates']);

        $this->sel['digital_filters'] = $this->kv['digital_filters'];
        $this->kv['digital_filter'] = "off";

        $this->sel['cal_modes'] = $this->kv['cal_modes'];
        $this->kv['cal_mode'] = "off";

        $this->sel['cal_freqs'] = $this->kv['cal_freqs'];
        $this->kv['cal_freq'] = "off";

        // channel specific
        $json_file = file_get_contents(dirname(__FILE__) . '/json/' . $this->name . '/channel.json');

        $kv = json_decode($json_file, true);
        for ($i = 0; $i < $this->kv['channels']; $i++) {
          array_push($this->channels, new channel($i, $meas_, $this->sel['sampling_rates'], $name_, $this->pdo_file));
        }
        // copy the values from the json file into the new database
        foreach ($this->channels as &$channel) {
          $channel->kv = $kv;
        }

        // create the database finally
        $this->create_table_and_contents();
        // clear the tmp values
        unset($this->channels);
        unset($this->kv);
        unset($this->sel);
        // make sure that we get the array types again
        $this->channels = array();
        $this->sel['sampling_rates'] = array();
        $this->sel['digital_filters'] = array();
        $this->sel['cal_modes'] = array();
        $this->sel['cal_freqs'] = array();
      } else {
        parent::__construct("adu", $this->pdo_file);
      }
      //
      //
      // read database always; for me and my sub classes or associated classes
      $this->read_table_contents();

      if ($this->kv['sampling_rate'] == 0) {
        $this->kv['sampling_rate'] = default_frequency($this->meas, $this->sel['sampling_rates']);
      }
      // $this->show();
    }

    function __destruct() {

      $this->set_sampling_rate();
      $this->toggle_safe_mode();
      $this->toggle_lab_mode();

      // // can not call a destructor on a newly created database
      if ($this->kv_old != null) $this->update_table();
      if ((isset($_SESSION["adu_debug"])) && ($_SESSION["adu_debug"] == 1)) {
        echo '<br> ->>>>ADU closed   <br>';
        echo "<br> ->>>>ADU closed " .  $this->kv['start_date'] . " " . $this->kv['start_time'] . "<br>";
      }
    }


    function create_table_and_contents() {
      // try catch inside
      foreach ($this->channels as &$channel) {
        $channel->defaults($this->kv["sampling_rate"]);
        $channel->create_table_and_content();
      }
      $this->create_table_and_content();

      $json_file = file_get_contents(dirname(__FILE__) . '/json/edi/edi.json');
      $kv_edi = json_decode($json_file, true);
      $this->create_table_and_content_external("edi", $kv_edi);
    }

    function read_table_contents() {
      // generates key values for adu and time
      $this->read_table_content();
      // put some vars into the session as global var
      $_SESSION['safe_mode'] = $this->kv['safe_mode'];
      $_SESSION['lab_mode'] = $this->kv['lab_mode'];

      if (!str_contains($this->kv['utc_offset'], ":")) {
        $this->kv['utc_offset'] = "00:00";
      } else {
        $hh = "00";
        $mm = "00";
        $hs = explode(":", $this->kv['utc_offset']);
        if (isset($hs[0])) $hh = $hs[0];
        if (isset($hs[1])) $mm = $hs[1];
        else $mm = "00";
        $hh = intval($hh) * 60 * 60;  // hours to seconds
        $mm = intval($mm) * 60;  // minutes to seconds
        if ($hh < 0) $hh = $hh - $mm;
        else $hh = $hh + $mm;
      }


      // after ADU is constructed, we do the rest - which may use the session vars in their constructors and destructors

      // generate the channels according to channels
      for ($i = 0; $i < $this->kv['channels']; $i++) {
        array_push($this->channels, new channel($i, $this->meas, $this->sel['sampling_rates'], $this->name, $this->pdo_file));
      }
      // foreach ($this->channels as $channel) {
      //   $channel->read_table_content();
      // }
    }



    function toggle_lab_mode_btn() {
      $str = "lab mode is " . $this->kv['lab_mode'];
      $form = '<form method="POST" action="" >';
      $form .= "    <input  type=\"submit\"   name=\"toggle_lab_mode\" id=\"toggle_lab_mode\" value=\"" . $str . "\" />";
      $form .= '</form>';
      return $form;
    }

    function toggle_safe_mode_btn() {
      $str = "safe mode is " . $this->kv['safe_mode'];
      $form = '<form method="POST" action="" >';
      $form .= "    <input  type=\"submit\"   name=\"toggle_safe_mode\" id=\"toggle_safe_mode\" value=\"" . $str . "\" />";
      $form .= '</form>';
      return $form;
    }

    function toggle_safe_mode() {
      if (isset($_POST['toggle_safe_mode'])) {
        if ($this->kv['safe_mode'] == "off") $this->kv['safe_mode'] = "on";
        else if ($this->kv['safe_mode'] == "on") $this->kv['safe_mode'] = "off";
        unset($_POST['safe_mode']);
      }
    }

    function safe_mode_on() {
      if ($this->kv['safe_mode'] == "off") $this->kv['safe_mode'] = "on";
    }

    function is_safe_mode() {
      if (($this->kv['safe_mode'] == "on") && ($this->kv['lab_mode'] == "off")) return true;
    }


    function toggle_lab_mode() {
      if (isset($_POST['toggle_lab_mode'])) {
        if ($this->kv['lab_mode'] == "off") $this->kv['lab_mode'] = "on";
        else if ($this->kv['lab_mode'] == "on") $this->kv['lab_mode'] = "off";
        unset($_POST['lab_mode']);
      }
    }

    function lab_mode_off() {
      if ($this->kv['lab_mode'] == "on") $this->kv['lab_mode'] = "off";
    }


    function select_sampling_rate() {
      $form = PHP_EOL . '<form method="POST" action="" > Sampling Rate: ' . PHP_EOL;
      $form .= '<select name="sampling_rate" id="sampling_rate" onchange="this.form.submit()">' . PHP_EOL;
      $form .= '<option selected="selected">' . $this->kv["sampling_rate"] . ' Hz</option>' . PHP_EOL;
      foreach ($this->sel['sampling_rates'] as $rate) {
        if ($rate != $this->kv["sampling_rate"]) {;
          $form .= '<option value="' . $rate . '" >' . $rate . ' Hz</option>' . PHP_EOL;
        }
      }
      $form .= '</select>' . PHP_EOL;
      $form .= '</form>' . PHP_EOL;
      return $form;
    }

    function set_sampling_rate() {
      if (isset($_POST['sampling_rate'])) {
        $this->kv['sampling_rate'] = $_POST['sampling_rate'];
        unset($_POST['sampling_rate']);
      }
    }

    function pos_containers() {
      if ($this->kv['safe_mode'] == "on") {
        foreach ($this->channels as $channel) {
          if (is_E($channel->kv['channel'])) echo $channel->pos_container();
        }
        return;
      }
      foreach ($this->channels as $channel) {
        echo $channel->pos_container();
      }
    }

    function rot_containers($all = 0) {
      if ($this->kv['safe_mode'] == "off") {
        foreach ($this->channels as $channel) {
          echo $channel->rot_container();
          echo $channel->get_slider_innerHTML("azimuth");
        }
        return;
      }
    }
  } // EO class
  ?>
