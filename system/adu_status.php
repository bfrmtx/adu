 <?php

  require_once(dirname(__FILE__) . '/channel_status.php');
  require_once(dirname(__FILE__) . '/gps_status.php');


  class adu_status extends database {

    // use job_time;     //!< aka lifetime; share all with job_time -> and kvs
    public $meas;     //!< MT, AMT, airborne with 6 channels and 6 magnetic
    public $name;     //!< system name like ADU-10e
    public $channels = array(); //!< the channels itself
    public $gps;      //!< the gps status
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
      if (!file_exists($this->pdo_file)) {
        $create_new = true;
        // echo  $pdo_file_ . " no sql file<br>";
      } else if ((file_exists($this->pdo_file)) && (filesize($this->pdo_file) < 5)) {
        $create_new = true;
        unlink($this->pdo_file);
      }


      // parse the json files and create the database in case
      if ($create_new) {
        //  adu specific
        //$this->pdo_file = sys_get_temp_dir() . '/status.sql3';
        // $this->pdo_file = $_SERVER['DOCUMENT_ROOT'] . '/tmp/job.sql3';
        parent::__construct("adu", $this->pdo_file);

        // $nerd = dirname(__FILE__) . '/json/' . $this->name . '/status/system.json';
        // $json_file = file_get_contents(dirname(__FILE__) . '/json/' . $this->name . '/status/channel.json');
        // $this->kv = json_decode($json_file, true);

        $json_file = file_get_contents(dirname(__FILE__) . '/json/' . $this->name . '/status/system.json');
        $this->kv = json_decode($json_file, true);
        // channel specific
        $json_file = file_get_contents(dirname(__FILE__) . '/json/' . $this->name . '/status/channel.json');

        $kv = json_decode($json_file, true);
        for ($i = 0; $i < $this->kv['channels']; $i++) {
          array_push($this->channels, new channel_status($i, $meas_, $name_, $this->pdo_file));
        }
        // copy the values from the json file into the new database
        foreach ($this->channels as &$channel) {
          $channel->kv = $kv;
        }

        $json_file = file_get_contents(dirname(__FILE__) . '/json/' . $this->name . '/status/gps.json');
        $kv = json_decode($json_file, true);
        $this->gps = new gps_status($this->name, $this->pdo_file);
        $this->gps->kv = $kv;

        // create the database finally
        $this->create_table_and_contents();
        // clear the tmp values
        unset($this->channels);
        unset($this->kv);
        unset($this->sel);
        // make sure that we get the array types again
        $this->channels = array();
        $this->sel['sampling_rates'] = array();
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



      // // can not call a destructor on a newly created database
      if ($this->kv_old != null) $this->update_table();
    }


    function create_table_and_contents() {
      // try catch inside
      foreach ($this->channels as &$channel) {
        $channel->defaults($this->kv["sampling_rate"]);
        $channel->create_table_and_content();
      }
      $this->create_table_and_content();

      $this->gps->create_table_and_content();

      $json_file = file_get_contents(dirname(__FILE__) . '/json/edi/edi.json');
      $kv_edi = json_decode($json_file, true);
      $this->create_table_and_content_external("edi", $kv_edi);
    }

    function read_table_contents() {
      // generates key values for adu and time
      $this->read_table_content();
      // put some vars into the session as global var


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
        array_push($this->channels, new channel_status($i, $this->meas, $this->name, $this->pdo_file));
      }
      // foreach ($this->channels as $channel) {
      //   $channel->read_table_content();
      // }
    }

    function set_adu_val($key, $val) {
      $this->kv[$key] = $val;
    }



    function set_chan_val($chan, $key, $val) {
      if ($chan > ($this->kv['channels'] - 1)) return;
      $this->channels[$chan]->kv[$key] = $val;
    }

    function is_safe_mode() {
      if (($this->kv['safe_mode'] == "on") && ($this->kv['lab_mode'] == "off")) return true;
    }

    function base_status() {
      $items = array(
        "Sampling Rate" => $this->kv['sampling_rate'] . " Hz",
        "Start" => $this->kv['start_date'] . " " . $this->kv['start_time'],
        "UTC Offset" => $this->kv['utc_offset'] . " DST " . $this->kv['dst']
      );

      $form =  '<div class="w3-col w3-container">';
      $form .= '<table align="left" style="width:100%">' . PHP_EOL;
      $form .= '<col style="width:40%">' . PHP_EOL;
      $form .= '<col style="width:60%"> ' . PHP_EOL;

      foreach ($items as $key => $value) {
        $form .= '<tr><td><h3 class="w3-text-deep-orange"; display: inline-block;>' . $key . '</td></h3>';
        $form .= '<td><h3 class="w3-text-black"; display: inline-block;>' . $value . '</td></tr></h3>' . PHP_EOL;
      }

      $form .= '</table>';
      $form .=  '</div>';
      return $form;
    }
  } // EO class
  ?>
