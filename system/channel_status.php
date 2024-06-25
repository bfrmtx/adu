  <?php
  require_once(dirname(__FILE__) . '/php_functions.php');
  require_once(dirname(__FILE__) . '/measurement.php');
  require_once(dirname(__FILE__) . '/database.php');


  /*!
  \brief show status of the channels of the RUNNING / measuring ADU system
  */
  class channel_status extends database {
    public $no;       //!< channel number

    public $on;       //!< controls if channel is on or off
    public $meas;     //!< MT, AMT, airborne with 6 channels and 6 magnetic
    public $name;     //!< system name like ADU-10e - do some specific for the system in case


    function __construct($no_, $meas_,  $name_, $pdo_file_) {
      $this->no = $no_;
      $this->on = true;
      $this->meas = $meas_;
      $this->name = $name_;
      parent::__construct('ch' . $no_, $pdo_file_);
      // do not read the table content if the database is not there yet
      // will be created by the ADU system
      if (check_pdo($pdo_file_)) {
        $this->read_table_content();
      }
    }


    function __destruct() {
      // can not call $kv_old on a newly created database
      if ($this->kv_old != null) $this->update_table();
    }



    function defaults($sampling_rate) {
      $this->kv['channel'] = default_channel_name($this->meas, $this->no);
      $this->kv['sensor'] = default_sensor_name($this->meas, $this->no);
      $this->kv['chopper'] = default_chopper($sampling_rate);
      $this->kv['angle'] = default_angle($this->no);
      $this->kv['serial'] = 0;
      $this->kv['dip'] = default_dip($this->no);
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


    function channel_container() {
      $val = "";
      $form = '<div class="w3-half w3-container">' . PHP_EOL;
      $form .= '  <h3 class="w3-text-deep-orange">Channel ' . $this->no . '</h3>' . PHP_EOL;
      $form .= '<table align="left" style="width:100%">' . PHP_EOL;
      $form .= '<col style="width:40%">' . PHP_EOL;
      $form .= '<col style="width:60%"> ' . PHP_EOL;
      $form .= "<tr><td>channel type: </td><td><b>" . $this->kv['channel'] . "</b></td>  </tr>" . PHP_EOL;
      $form .= "<tr><td>chopper</td><td>" . $this->kv['chopper'] . "</td> </tr>" . PHP_EOL;
      $form .= "<tr><td>sensor</td><td>" . $this->kv['sensor'] . "</td></tr>" . PHP_EOL;

      $form .= '</table>' . PHP_EOL;

      $form .= '</div>' . PHP_EOL;

      return $form;
    }
  } // EO class
  ?>
