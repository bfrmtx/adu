<?php
//if (!isset($_SESSION)) session_start();

// change table from command line:
// sqlite3 jl.sql3 "UPDATE adu SET value = '2027-12-24' WHERE id = 2"

/*!
 * \brief trait for key value pairs used in many classes

 */
trait kvs {

  public $kv;       //!< key value pairs for item (ADU, channel and so on)
  public $kv_old;   //!< old key value pairs for item (ADU, channel and so on)
  // use $gains for sel and gain for $kv : plural and singular
  public $sel;      //!< multi dimensional array for selectors and multiple choices
  public $status;   //!< status of the item (ADU, channel and so on) as key value pairs

  public function show() {
    foreach ($this->kv as $key => &$value) {
      echo $key . " " . $value . "<br>";
    }
  }

  public function show_old() {
    foreach ($this->kv_old as $key => &$value) {
      echo $key . " " . $value . "<br>";
    }
  }

  public function updated_values() {
    if (count($this->kv) != count($this->kv_old)) {
      echo "<br><br>update error in trait kvs<br><br>";
      return array();
    }
    $updates = array();
    foreach ($this->kv as $key => &$value) {
      if ($value != $this->kv_old[$key]) {
        $updates[$key] = $value;
      }
    }
    return $updates;
  }
}


class database {

  public $pdo_name; //!< database name 
  public $pdo_file; //!< database file 
  public $db;       //!< the PDO database
  public $table;    //!< table to work on

  use kvs;

  /*!
   * \brief constructor - does NOT create a file! SQLite may create when ACCESSED
   * \param table_ table name e.g. adu or ch0, ch1, ... edi
   * \param pdo_file_ database file - e.g. .sql3
   */
  function __construct($table_, $pdo_file_) {
    $this->table = $table_;
    $this->pdo_file = $pdo_file_;
    $this->pdo_name = 'sqlite:' . $this->pdo_file;
  }

  function __destruct() {
    $this->db = null;
    if ((isset($_SESSION["database_debug"])) && ($_SESSION["database_debug"] == 1)) {
      echo '<br> ->>>>DB closed   <br>';
    }
    // if POST is GO_MAIN_PAGE then to to index.php
    if (isset($_POST["GO_MAIN_PAGE"])) {
      unset($_SESSION["GO_MAIN_PAGE"]);
      header("location:index.php"); // your next page
    }
  }

  public function connect() {
    // echo "connect -> " . $this->pdo_name . "<br>";
    $this->db  = new PDO($this->pdo_name);
    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  /*!
    * \brief create a table and insert content INSIDE a class where "$this->table" is defined
    */

  public function create_table_and_content() {
    try {
      $this->connect();
      $cmd = 'CREATE TABLE ' . $this->table . ' (id INTEGER, key	TEXT, value TEXT, PRIMARY KEY(id AUTOINCREMENT) )';
      $this->db->exec($cmd);
    } catch (PDOException $e) {
      echo "There was an error while trying to create table " . $this->table . "<br>";
      $err_msg = $e->getMessage();
      if (strlen($err_msg) > 0) {
        echo "<div>$err_msg</div>";
      }
    }
    try {
      $this->connect();
      $this->db->beginTransaction();
      foreach ($this->kv as $key => &$val) {
        if (is_array($val)) {
          // 4096, 1024, 512, 256 would be an array for selection later
          $arr =  implode(', ', $val);
          $cmd = 'INSERT INTO ' .  $this->table .  " VALUES (NULL, \"$key\", \"$arr\")";
        } else {
          $cmd = 'INSERT INTO ' .  $this->table .  " VALUES (NULL, \"$key\", \"$val\")";
        }
        $this->db->exec($cmd);
      }
      $this->db->commit();
    } catch (PDOException $e) {
      echo "There was an error while trying to insert into " . $this->table . "<br>";
      $err_msg = $e->getMessage();
      if (strlen($err_msg) > 0) {
        echo "<div>$err_msg</div>";
      }
    }
  }

  /*!
    * \brief create a table and insert content OUTSIDE a class where a NEW table is defined for an EXISTING connection
    * \param table_ table name e.g. edi ... BUT NOT ch0, ch1 ... adu - these are created in the class
    * \param kv_ key value pairs
    */
  public function create_table_and_content_external($table_, $kv_) {
    try {
      $this->connect();
      $cmd = 'CREATE TABLE ' . $table_ . ' (id INTEGER, key	TEXT, value TEXT, PRIMARY KEY(id AUTOINCREMENT) )';
      $this->db->exec($cmd);
    } catch (PDOException $e) {
      echo "There was an error while trying to create table " . $table_ . "<br>";
      $err_msg = $e->getMessage();
      if (strlen($err_msg) > 0) {
        echo "<div>$err_msg</div>";
      }
    }
    try {
      $this->connect();
      $this->db->beginTransaction();
      foreach ($kv_ as $key => &$val) {
        if (is_array($val)) {
          $arr =  implode(', ', $val);
          $cmd = 'INSERT INTO ' .  $table_ .  " VALUES (NULL, \"$key\", \"$arr\")";
        } else {
          $cmd = 'INSERT INTO ' .  $table_ .  " VALUES (NULL, \"$key\", \"$val\")";
        }
        $this->db->exec($cmd);
      }
      $this->db->commit();
    } catch (PDOException $e) {
      echo "There was an error while trying to insert into " . $table_ . "<br>";
      $err_msg = $e->getMessage();
      if (strlen($err_msg) > 0) {
        echo "<div>$err_msg</div>";
      }
    }
  }

  public function update_table() {
    if (count($this->kv) != count($this->kv_old)) {
      echo "<br><br>update table error<br><br>";
      $this->show();
      $this->show_old();
      return;
    }
    try {
      $this->connect();
      $this->db->beginTransaction();
      foreach ($this->kv as $key => &$val) {
        // echo "key->: " . $key .  " val->:" .  $val . " old value: " . $this->kv_old[$key] . "<br>";
        if ($val != $this->kv_old[$key]) {
          $cmd = 'UPDATE ' .  $this->table .  " SET \"value\" = \"$val\" WHERE \"key\" = \"$key\" ";
          $this->db->exec($cmd);
        }
      }
      $this->db->commit();
    } catch (PDOException $e) {
      echo "There was an error while trying to update the table " . $this->table . "<br>";
      $err_msg = $e->getMessage();
      if (strlen($err_msg) > 0) {
        echo "<div>$err_msg</div>";
        $err_msg = "";
      }
    }
  }



  function read_table_content() {
    try {
      $this->connect();
      $cmd = 'SELECT key, value from ' . $this->table;
      $query = $this->db->prepare($cmd);
      $query->execute();
      // the key value pairs are generated here!
      $this->kv = $query->fetchAll(PDO::FETCH_KEY_PAIR);
      // when updating the table we only want to write changes
      //
      // items with plural names are arrays; put the into sel array, and take a singular the selected item
      if (array_key_exists('sampling_rates', $this->kv)) {
        $this->sel['sampling_rates'] = explode(",", $this->kv['sampling_rates']);
        // remove spaces from sel array
        foreach ($this->sel['sampling_rates'] as &$value) {
          $value = trim($value);
        }
        unset($this->kv['sampling_rates']);
      }
      if (array_key_exists('digital_filters', $this->kv)) {
        $this->sel['digital_filters'] = explode(",", $this->kv['digital_filters']);
        foreach ($this->sel['digital_filters'] as &$value) {
          $value = trim($value);
        }
        unset($this->kv['digital_filters']);
      }
      if (array_key_exists('cal_modes', $this->kv)) {
        $this->sel['cal_modes'] = explode(",", $this->kv['cal_modes']);
        foreach ($this->sel['cal_modes'] as &$value) {
          $value = trim($value);
        }
        unset($this->kv['cal_modes']);
      }
      if (array_key_exists('cal_freqs', $this->kv)) {
        $this->sel['cal_freqs'] = explode(",", $this->kv['cal_freqs']);
        foreach ($this->sel['cal_freqs'] as &$value) {
          $value = trim($value);
        }
        unset($this->kv['cal_freqs']);
      }

      if (array_key_exists('e-sensors', $this->kv)) {
        $this->sel['e-sensors'] = explode(",", $this->kv['e-sensors']);
        foreach ($this->sel['e-sensors'] as &$value) {
          $value = trim($value);
        }
        unset($this->kv['e-sensors']);
      }
      if (array_key_exists('h-sensors', $this->kv)) {
        $this->sel['h-sensors'] = explode(",", $this->kv['h-sensors']);
        foreach ($this->sel['h-sensors'] as &$value) {
          $value = trim($value);
        }
        unset($this->kv['h-sensors']);
      }
      if (array_key_exists('gains', $this->kv)) {
        $this->sel['gains'] = explode(",", $this->kv['gains']);
        foreach ($this->sel['gains'] as &$value) {
          $value = trim($value);
        }
        unset($this->kv[' gains']);
      }
      $this->kv_old = $this->kv;
    } catch (PDOException $e) {
      echo "There was an error while trying to reading the tables" . PHP_EOL;
      $err_msg = $e->getMessage();
      if (strlen($err_msg) > 0) {
        echo "<div>$err_msg</div>";
        $err_msg = "";
      }
    }
  }
} // EO class


// UPDATE "ch2" SET value="999" WHERE key='serial number'
