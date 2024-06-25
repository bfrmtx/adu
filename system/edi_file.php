  <?php

  require_once(dirname(__FILE__) . '/database.php');
  require_once(dirname(__FILE__) . '/php_functions.php');


  if (!isset($_SESSION)) session_start();

  class edi extends database {


    function __construct($name_, $pdo_file_) {

      parent::__construct("edi", $pdo_file_);
      $this->read_table_content();
    }

    function __destruct() {
      // can not call a destructor on a newly created database
      $this->get_post();
      if ($this->kv_old != null) $this->update_table();
      // echo " < br > ----- ch saved " . $this->kv['chopper'] . "<br>";
    }

    function create_fields() {
      foreach ($this->kv as $key => &$val) {
        $form = '<div class="w3-third w3-container">' . PHP_EOL;
        $beauty = str_replace("-", " ", $key);
        $form .= '<h2 class="w3-text-deep-orange">' . $beauty . '</h2>' . PHP_EOL;
        $form .= post_string_onblur("", $key, $val, 18);
        $form .= '</div>' . PHP_EOL;
        echo $form;
      }
    }

    function get_post() {
      if (empty($_POST)) return;
      $result = array_intersect_key($_POST, $this->kv);
      if (empty($result)) return;
      $firstkey = array_key_first($result);
      $firstval = array_values($result)[0];
      $this->kv[$firstkey] = $firstval;
    }
  } // EO class
