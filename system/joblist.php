<?php
if (!isset($_SESSION)) session_start();

require_once(dirname(__FILE__) . '/database.php');

// class containing a list of job_time objects
// CREATE TABLE IF NOT EXISTS "jobs" ("id" INTEGER UNIQUE, "start_date" TEXT, "start_time" TEXT, "duration" TEXT, "sampling_rate" TEXT, "digital_filter" TEXT, "cycle" TEXT, "sub_id_of" TEXT,	PRIMARY KEY("id" AUTOINCREMENT));

class db_jobs {
  public $pdo_name; //!< database name 
  public $pdo_file; //!< database file 
  public $db;       //!< the PDO database
  public $table;    //!< table to work on

  use kvs;
  public $id = 0;                //!< id of the job, NOT part of kv; we have auto increment so we trat separately

  private $include_in_select_view = array();
  private $table_header_in_select_view = array();


  function __construct($table_, $pdo_file_) {
    $this->table = $table_;
    $this->pdo_file = $pdo_file_;
    $this->id = 0;                                  //!< id of the job - and $this->kv['job_id'] in class job_time
    //
    // make sure that these keys are the same as in job_time and channel !!!!!!!!!!!!!!!!!
    // this is a subset of job_time and channels and contains selected keys only !! - not all keys
    //
    // column names in database jobs
    // id is <unique> and <primary key> and <autoincrement>
    $this->kv['start_date'] = '1970-01-01';         //!< start date of the job
    $this->kv['start_time'] = '00:00:00';           //!< start time of the job
    $this->kv['duration'] = 0;                      //!< duration of the job
    $this->kv['sampling_rate'] = 0;                 //!< sampling rate of the job
    $this->kv['digital_filter'] = "off";            //!< digital filter of the job - must be something like 4x or 32x in case you want to create a sub job; this will be the enclosing job
    $this->kv['cycle'] = 0;                         //!< cycle of the job - repeat the job every x seconds
    $this->kv['sub_id_of'] = 0;                     //!< sub id of the job - if this is a sub job, this is the id of the enclosing job; e.g. this is a cyclic job without filter
    $this->kv['wait_for_fix'] = "true";             //!< wait for G4 fix
    // 
    $this->kv['cal_mode'] = "off";                  //!< calibration mode
    $this->kv['cal_freq'] = "off";                  //!< calibration frequency
    $this->kv['choppers'] = array();                //!< array of chopper auto on off for each channel - comma separated
    $this->kv['gains'] = array();                   //!< array of gain for each channel - comma separated

    $this->include_in_select_view = array('start_date', 'start_time', 'duration', 'sampling_rate', 'digital_filter', 'cycle', 'sub_id_of');
    $this->table_header_in_select_view = array('id', 'Start Date', 'Time', 'Duration', 'f', 'Filter', 'Cycle', 'Sub Id Of');


    $this->pdo_name = 'sqlite:' . $this->pdo_file;
    $this->create_table();
  }

  function __destruct() {


    if ((isset($_SESSION["database_debug"])) && ($_SESSION["database_debug"] == 1)) {
      echo "<br>DB closed (jdb_jobs)<br>";
    }
    $this->db = null;
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

  public function create_table() {
    try {
      $this->connect();
      // $cmd = 'CREATE TABLE IF NOT EXISTS ' . $this->table . ' (id INTEGER, start_date TEXT, start_time TEXT, duration TEXT, sampling_rate TEXT, digital_filter TEXT, cycle TEXT, sub_id_of TEXT, PRIMARY KEY(id AUTOINCREMENT) )';
      $cmd = 'CREATE TABLE IF NOT EXISTS ' . $this->table . ' (id INTEGER, ';
      foreach ($this->kv as $key => &$value) {
        $cmd .= $key . ' TEXT, ';
      }
      $cmd .= 'PRIMARY KEY(id AUTOINCREMENT) )';

      $this->db->exec($cmd);
      // echo "Table " . $this->table . " created successfully<br>";
    } catch (PDOException $e) {
      echo $cmd . "<br>" . $e->getMessage();
    }
    $this->db = null;
  }

  // bool function to check if table is empty
  public function is_table_empty() {
    $this->connect();
    $cmd = 'SELECT * FROM ' . $this->table;
    $stmt = $this->db->prepare($cmd);
    $stmt->execute();
    $result = $stmt->fetchAll();
    $this->db = null;
    if (count($result) == 0) {
      return true;
    } else {
      return false;
    }
  }

  // function update from job_time object
  public function update_from_job_time(&$job_time) {
    // for all existing keys in joblist class fetch the values from job_time
    foreach ($this->kv as $key => &$value) {
      // if key exists
      if (array_key_exists($key, $job_time->kv)) $this->kv[$key] = $job_time->kv[$key];
    }
    // clear choppers and gains
    $this->kv['choppers'] = array();
    $this->kv['gains'] = array();
    // for each channel in job_time fetch the chopper and gain values
    foreach ($job_time->channels as &$channel) {
      array_push($this->kv['choppers'], $channel->kv['chopper']);
      array_push($this->kv['gains'], $channel->kv['gain']);
    }
  }

  public function update_from_job_from_joblist($id) {
    $this->connect();
    $cmd = 'SELECT * FROM ' . $this->table . ' WHERE id = ' . $id;
    $stmt = $this->db->prepare($cmd);
    $stmt->execute();
    $result = $stmt->fetchAll();
    $this->db = null;
    // for all existing keys in joblist class fetch the values from job_time
    foreach ($this->kv as $key => &$value) {
      // if key exists
      if (array_key_exists($key, $result[0])) $this->kv[$key] = $result[0][$key];
    }
    // clear choppers and gains
    $this->kv['choppers'] = array();
    $this->kv['gains'] = array();
    // for each channel in job_time fetch the chopper and gain values
    $choppers = explode(',', $result[0]['choppers']);
    $gains = explode(',', $result[0]['gains']);
    foreach ($choppers as &$chopper) {
      array_push($this->kv['choppers'], $chopper);
    }
    foreach ($gains as &$gain) {
      array_push($this->kv['gains'], $gain);
    }
  }

  // function to insert a new job
  public function insert_job() {
    $this->connect();
    // $cmd = 'INSERT INTO ' . $this->table . ' (start_date, start_time, duration, sampling_rate, digital_filter, cycle, sub_id_of) VALUES ("' . $start_date_ . '", "' . $start_time_ . '", "' . $duration_ . '", "' . $sampling_rate_ . '", "' . $digital_filter_ . '", "' . $cycle_ . '", "' . $sub_id_of_ . '")';
    $cmd = 'INSERT INTO ' . $this->table . '(';
    foreach ($this->kv as $key => &$value) {
      $cmd .= ' ' . $key . ',';
    }
    $cmd = rtrim($cmd, ',');
    $cmd .= ') VALUES (';
    $tmp_choppers = $this->kv['choppers'];
    $tmp_gains = $this->kv['gains'];
    $this->kv['choppers'] = implode(',', $this->kv['choppers']);
    $this->kv['gains'] = implode(',', $this->kv['gains']);
    foreach ($this->kv as $key => &$value) {
      $cmd .= ' "' . $value . '",';
    }
    $cmd = rtrim($cmd, ',');
    $cmd .= ')';

    $this->kv['choppers'] = $tmp_choppers;
    $this->kv['gains'] = $tmp_gains;

    // execute the command and get the id of the inserted job
    $this->db->exec($cmd);
    $this->id = $this->db->lastInsertId();
    $this->db = null;
    return $this->id;
  }


  public function submit_to_joblist(&$job_time) {
    if (isset($_POST['submit_to_joblist'])) {
      $this->id = $this->insert_job();
      unset($_POST['submit_to_joblist']);
      if (($this->id == "") || ($this->id == null) ||  ($this->id == -1)) $this->id = 0; // no job id
      if ($this->id != 0) $job_time->kv['job_id'] = $this->id;
      return $this->id;
    }
    // need to know that the post was not submitted
    return 0;
  }

  public function update_joblist(&$job_time) {
    if (isset($_POST['update_joblist'])) {
      unset($_POST['update_joblist']);
      if (($job_time->kv['job_id'] == "") || ($job_time->kv['job_id'] == null) ||  ($job_time->kv['job_id'] == -1) || ($job_time->kv['job_id'] == 0)) {
        // create a super global variable to tell the job_time object that the job is not yet in the joblist
        $_SESSION["job_not_in_joblist"] = 1;
      }
    }
  }

  public function submit_to_joblist_btn() {
    $form = '<form method="POST" action="" >';
    $form .= "    <input  type=\"submit\"   name=\"submit_to_joblist\" id=\"submit_to_joblist\" value=\"submit_to_joblist\" />";
    $form .= '</form>';
    return $form;
  }

  public function update_joblist_btn() {
    $form = '<form method="POST" action="" >';
    $form .= "    <input  type=\"submit\"   name=\"update_joblist\" id=\"update_joblist\" value=\"update_joblist\" />";
    $form .= '</form>';
    // fetch the $_SESSION["job_not_in_joblist"] variable
    if (isset($_SESSION["job_not_in_joblist"])) {
      $message = "this job is not yet in the joblist";
      echo '<body onload="showMessage(\'' . $message . '\')">' . PHP_EOL;
      unset($_SESSION["job_not_in_joblist"]);
    }

    return $form;
  }


  public function show_joblist_as_table() {
    $this->connect();
    // order by start_date, start_time
    $cmd = 'SELECT * FROM ' . $this->table . ' ORDER BY start_date, start_time';
    //$cmd = 'SELECT * FROM ' . $this->table;
    $stmt = $this->db->prepare($cmd);
    $stmt->execute();
    $result = $stmt->fetchAll();
    $this->db = null;
    echo "<table>";
    echo "<tr>";
    // table header
    foreach ($this->table_header_in_select_view as &$value) {
      echo "<th>" . $value . "</th>";
    }
    echo "</tr>";

    foreach ($result as $row) {
      echo "<tr>";
      // get the id of the job from result
      $this->id = $row['id'];
      echo "<td>" . $this->edit_job_id_button($this->id) . "</td>";
      foreach ($this->kv as $key => &$value) {
        if (in_array($key, $this->include_in_select_view))

          // if key is sampling_rate append Hz
          if ($key == "sampling_rate") echo "<td>" . $row[$key] . " Hz</td>";
          else if ($key == "duration") {
            echo "<td>" . seconds_to_time($row[$key]) . "</td>";
            // convert to dd:hh:mm:ss
            $duration = $row[$key];
            $days = floor($duration / 86400);
            $duration -= $days * 86400;
            $hours = floor($duration / 3600);
            $duration -= $hours * 3600;
            $minutes = floor($duration / 60);
            $seconds = $duration - $minutes * 60;
            if ($days == 0) echo "<td>" . $hours . ":" . $minutes . ":" . $seconds . "</td>";
            else echo "<td>" . $days . "days " . $hours . ":" . $minutes . ":" . $seconds . "</td>";
          } else echo "<td>" . $row[$key] . "</td>";
      }
      echo "</tr>";
    }
    echo "</table>";
  }

  public function edit_job_id_button($id) {
    $form = '<form method="POST" action="" >';
    // place lable edit in front of id
    $form .= "    <input  type=\"submit\"   name=\"edit_job_id\" id=\"edit_job_id\" value=\"" . $id . " edit " . "\" />";
    $form .= '</form>';
    return $form;
  }

  public function delete_job_id_button($id) {
    $form = '<form method="POST" action="" >';
    // place lable edit in front of id
    $form .= "    <input  type=\"submit\"   name=\"delete_job_id\" id=\"delete_job_id\" value=\"" . $id . " delete " . "\" />";
    $form .= '</form>';
    return $form;
  }


  public function delete_job_id(&$job_time) {
    if (isset($_POST['delete_job_id'])) {
      $this->id = $_POST['delete_job_id'];
      $this->connect();
      $cmd = 'DELETE FROM ' . $this->table . ' WHERE id = ' . $this->id;
      $stmt = $this->db->prepare($cmd);
      $stmt->execute();
      unset($_POST['delete_job_id']);

      // if job_id of job_time is the same as the id of the deleted job, set job_id to 0
      if ($job_time->kv['job_id'] == $this->id) $job_time->kv['job_id'] = 0;
      // re-number the index on jobs table from the beginning




    }
  }
} // EO class
