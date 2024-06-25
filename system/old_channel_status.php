  <?php
  require_once(dirname(__FILE__) . '/php_functions.php');
  require_once(dirname(__FILE__) . '/measurement.php');
  require_once(dirname(__FILE__) . '/database.php');


  if (!isset($_SESSION)) session_start();


  class channel_status extends database {
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
      $this->kv['serial'] = 0;
      $this->kv['angle'] = 0.0;
      $this->kv['dip'] = 0.0;
    }




    function set_sensor($sensor) {
      $this->kv['sensor'] = $sensor;
    }
  } // EO class
  ?>
