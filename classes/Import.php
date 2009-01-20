<?php
/*
 * file: Import.php 
 * project: csvtosql
 * Created on 30/07/2007 at 10:26:22 PM
 *
 * @author Tim-Hinnerk Heuer (tim@ihostnz.com)
 * Copyright 2007 iHostNZ - Tim-Hinnerk Heuer
 * 
 * Todo:
 * TODO
 */
require_once(dirname(__FILE__) . '/../includes.php');

class Import {
  
  var $prefix;
  var $tables;
  var $fks;
  
  function Import($prefix = null, $tables = null, $fks = null) {
  	$this->prefix = $prefix;
    $this->tables = $tables;
    $this->fks = $fks;
  }
  
  function getSql($method = "insert") {
    global $debug;
    if ($method == "insert") {
      $action = "INSERT INTO ";
    }
    if ($method == "update") {
      $action = "UPDATE ";
    }
    $queries = array();
    foreach ($this->tables as $table) {
      $cols = $this->getColumns($table);
      $queries[] = $action . $this->prefix . $table . " (";
    }
    return $this->getConditions($this->prefix, $this->tables, $this->fks);
  }
  
  function getConditions($prefix, $table, $fks) {
    global $debug;
    $fks_value = $this->fks;
    asort($fks_value);
    $fks_key = $this->fks;
    ksort($fks_key);
    if ($debug) {
      foreach ($fks_key as $key => $value) {
      	echo "$key => $value <br />";
      }
      echo "<br />";
      foreach ($fks_value as $key => $value) {
        echo "$key => $value <br />";
      }
    }
    
  }
  
  function getColumns($table) {
  	return array();
  }
  
  /**
   * get the order of the tables which are to be imported
   * @return array tables in the right order to do queries on
   */
  function getTableOrder() {
  	global $mysql_fks, $debug;
    $fktables = array();
    // set up the fktables array
    foreach ($mysql_fks as $key => $val) {
      $t1 = explode(".", $key);
      $t2 = explode(".", $val);
      $fktables[] = array($t1[0], $t2[0]);
    }
    $tables = array();
    $tables2 = array();
    
    foreach ($fktables as $val) {
      $tables2[] = $val[0]; 
    }
    
    foreach ($fktables as $key => $val) {
      list($t1, $t2) = $val;
      if (!in_array($t2, $tables2))
        $tables[] = $t2;
    }
    $tables = array_unique($tables);
    
    $tables3 = array();
    while (count(array_diff($tables2, $tables)) > 0) { // not all tables are sorted
      foreach ($tables as $table) {
        // get lhs
        $tables3 = array_merge($tables3, $this->array_find_lhs($table, $fktables));
      }
      $tables = array_merge($tables, $tables3);
    }
    $tables = array_unique($tables);
    $tables = array_values($tables);
    $this->tables =& $tables;
  	return $tables;
  }

  /**
   * find a value in the rhs of a 2d array and return the lhs
   * 
   * @param string value to be found
   * @param array array2d a 2 dimensional array with 2 values in each array element, the fk tables
   */
  function array_find_lhs($value, $array2d) {
    $tables = array();
    foreach ($array2d as $key => $val) {
      if ($val[1] == $value) {
        $tables[] = $val[0];
      }
    }
    return $tables;
  }
}

if ($debug) {
  $import = new Import($mysql_table_prefix, $mysql_tables, $mysql_fks);
  $import->getSql();
  echo "<pre>";
  print_r($import->getTableOrder());
  echo "</pre>";
}
?>
