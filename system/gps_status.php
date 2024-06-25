  <?php
  require_once(dirname(__FILE__) . '/php_functions.php');
  require_once(dirname(__FILE__) . '/database.php');


  /*!
  \brief show status of the GPS
  */
  class gps_status extends database {
    public $name;     //!< system name like ADU-10e - do some specific for the system in case


    function __construct($name_, $pdo_file_) {
      $this->name = $name_;
      parent::__construct("gps", $pdo_file_);
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



    function defaults() {
      $this->kv['gps'] = 6;
      $this->kv['galileo'] = 8;
      $this->kv['glonass'] = 0;
      $this->kv['beidou'] = 0;
      $this->kv['in_view'] = 12;
      $this->kv['fix'] = "G4";
      $this->kv['moving_mode'] = "off";
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


    function gps_container() {
      $val = "";
      $form = '<div class="w3-half w3-container">' . PHP_EOL;
      $form .= '  <h3 class="w3-text-deep-orange">GPS: ' . $this->kv['fix'] . '</h3>' . PHP_EOL;
      $form .= '<table align="left" style="width:100%">' . PHP_EOL;
      $form .= '<col style="width:40%">' . PHP_EOL;
      $form .= '<col style="width:60%"> ' . PHP_EOL;
      $form .= "<tr><td>GPS: </td><td><b>" . $this->kv['gps'] . "</b></td>  </tr>" . PHP_EOL;
      $form .= "<tr><td>Galileo</td><td>" . $this->kv['galileo'] . "</td> </tr>" . PHP_EOL;
      $form .= "<tr><td>In View:</td><td>" . $this->kv['in_view'] . "</td></tr>" . PHP_EOL;
      $form .= "<tr><td>Moving Mode:</td><td>" . $this->kv['moving_mode'] . "</td></tr>" . PHP_EOL;

      $form .= '</table>' . PHP_EOL;

      $form .= '</div>' . PHP_EOL;

      return $form;
    }
  } // EO class
  ?>
