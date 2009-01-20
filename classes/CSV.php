<?php
/*
 * file: CSV.php 
 * project: csvtosql
 * Created on 22/07/2007 at 6:05:12 PM
 *
 * @author Tim-Hinnerk Heuer (tim@ihostnz.com)
 * Copyright 2007 iHostNZ - Tim-Hinnerk Heuer
 * 
 * Todo: 
 * TODO clean up code and design user interface
 */
require_once(dirname(__FILE__) . '/../includes.php');

class CSV {
  var $terminator;
  var $encloser;
  var $escape;
  var $lineend;
  var $filehandle;
  var $filename;
  var $csvTable;
  var $columns = array();
  var $csvColMatch = array();

  var $prefix;
  var $tables;
  var $fks;
  var $key_col;

  /*
   * constructor
   * set up file handle and delimiter, terminator etc.
   * @param string $filename a CSV file
   * optional:
   * @param string $method method of import (csv, mysql)
   * @param string $terminator
   * @param string $encloser
   * @param string $escape
   * @param string $lineend
   */
  function CSV($filename, $terminator = ",", 
               $encloser = "\"", $escape = "\"", $lineend = "\\r", 
               $csvColMatch = null, $prefix = null, $tables = null, $fks = null,
               $key = null,
               $method = 'csv') {
    global $debug;
    $this->filename = $filename;
    
    $this->csvColMatch = $csvColMatch;

    $this->prefix = $prefix;
    $this->tables = $tables;
    $this->fks = $fks;
    $this->key_col = $key;

    $this->encloser = $encloser;
    $this->terminator = $terminator;
    $this->escape = $escape;
    $this->lineend = $lineend;
    $columns = array();
    
    $this->csvTable = array(array());
    if ($method == 'mysql') {
      $this->filename = $filename;
      return $filename;
    }
    elseif ($method == 'csv') {
      $this->filehandle = @fopen( $filename, "r" );
      if ( $this->filehandle ) {
        if ( $debug ):
          echo "file handle = ". $this->filehandle ."<br />";
        endif;
      }
      else {
        return null;
      }
    }
    foreach ($this->getCols() as $key => $col) {
      $columns[] = "col ". ($key + 1);
    }
    $this->columns = $columns;
  }
  
  /*
   * import the CSV file into a MySQL table
   * 
   * @param array() $columns an array of columns
   * @return success?  
   */
  function import($columns) {
  	global $debug, $tmp_table;
    $columns = $this->columns;
  	$db = new Db();
  	$create_query = "CREATE TABLE $tmp_table (";
    $table_size = count($columns);
    for ($i = 0; $i < $table_size; $i++) {
      if ($i < $table_size - 1) {
        $create_query .= "`". $columns[$i] . "` text CHARACTER SET latin1,"; 
      }
      else {
        $create_query .= "`". $columns[$i] . "` text CHARACTER SET latin1);"; 
      }
  	}
    if ($debug) echo "<br />". $create_query ."<br />";
    $db->query("DROP TABLE IF EXISTS $tmp_table;");
    $db->query($create_query);
    $import_query = "" .
        "LOAD DATA LOCAL INFILE '$this->filename' " .
        "INTO TABLE $tmp_table " .
        "FIELDS TERMINATED BY '$this->terminator' " .
        "ENCLOSED BY '$this->encloser' " .
        "LINES TERMINATED BY '".$this->unescape($this->lineend)."' " .
        "(`". implode("`, `", $columns) ."`);";
    if ($debug) {
      echo $import_query ."<br />";
    }
    return $db->query($import_query);
  }
  
  /*
   * @param string $str a CSV string
   * @return array() CSV values
   */
  function stringToArray($str){
    $expr="/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/";
    $results=preg_split($expr,trim($str));
    return preg_replace("/^\"(.*)\"$/","$1",$results);
  }
  
  function getCols() {
    $csvstring = fgets($this->filehandle);
    $row = $this->stringToArray($csvstring);
  	return $row;
  }
  
  function getNColumns() {
    $row = $this->getCols();
  	return count($row);
  }
  
  function getCsvColsTable($table) {
  	$cols = array();
    foreach ($this->csvColMatch as $key => $val) {
      if ($this->getTable($val) == $table) {
      	$cols[] = $key;
      }
    }
    return $cols;
  }
  
  function getCsvKey() {
  	echo "<pre> csvColMatch = ";
  	print_r($this->csvColMatch);
  	echo "\nkey_col = $this->key_col";
  	echo "</pre>";
  	foreach ($this->csvColMatch as $key => $val) {
  	  if ($val == $this->key_col)
  	  	return $key;
  	}
  }
  
  function getColumn($string) {
  	$col = explode(".", $string);
    if (count($col) == 2)
      return $col[1];
    else
      return $string;
  }

  function getSql($method = "insert", $cols_to_update = array()) {
    global $debug, $tmp_table, $mysql_key;
    if ($method == "insert") {
      $action = "INSERT INTO ";
    }
    if ($method == "update") {
      $action = "INSERT INTO ";
    }
    $queries = array();
    if ($debug) {
      //print_r($this->csvColMatch);
    }
      foreach ($this->tables as $table) {
        $cols = $this->getColumns($table);
        if (in_array($this->getColumn($mysql_key), $cols) && !$this->hasForeignKey($table)) {
          $queries[] = $action . $this->prefix . $table . " ("
                      . implode(", ", $cols) .") \n"
                      . "SELECT `". implode("`, `", $this->getCsvColsTable($table)) ."` \n"
                      . "FROM $tmp_table \n"
                      . "WHERE `".$this->getKeyCol()."` NOT IN ("
                      . "SELECT ". $this->getColumn($mysql_key) ." "
                      . "FROM $this->prefix$table) " 
                      . "GROUP BY `".$this->getKeyCol()."`\n\n";
        }
        elseif (!$this->hasForeignKey($table)) {
          $queries[] = $action . $this->prefix . $table . " ("
                      . implode(", ", $cols) .") \n"
                      . "SELECT DISTINCT `". implode("`, `", $this->getCsvColsTable($table)) ."` \n"
                      . "FROM $tmp_table \n"
                      . "WHERE `".implode("`, `", $this->getCsvColsTable($table))."` NOT IN ("
                      . "SELECT `". implode("`, `", $cols) ."` " 
                      . "FROM ". $this->prefix . $table .")";
        }
        else { // insert foreign keys
          $csvFkRhsTable = $this->getFkRhsTable($table);
          $csvColsTable = $this->getCsvColsTable($table);
          $queries[] = $action . $this->prefix . $table . " ("
                      . implode(", ", $this->getFkRhs($table)) 
                      . ((count($cols) > 0) ? ", " : "") . implode(", ", $cols) .") \n"
                      . "SELECT $this->prefix". implode(", $this->prefix", $this->getFkRhsFull($table))
                      . (count($cols) > 0 ? ", $tmp_table.`"  
                      . implode("`, $tmp_table.`", $this->getCsvColsTable($table)) ."` \n" : " \n")
                      . "FROM $this->prefix" //. $this->getKeyTable() .", "
                      . implode(", $this->prefix", $csvFkRhsTable) 
                      // . ((count($cols) > 0) ? ", $this->prefix" : "") . implode(", $this->prefix", $cols) 
                      . ", $tmp_table \n"
                      . "WHERE ". $this->getConditions($csvFkRhsTable)
                      . " AND ($this->prefix". implode(", $this->prefix", $this->getFkRhsFull($table))
                      . (count($cols) > 0 ? ", $tmp_table.`"  
                      . implode("`, $tmp_table.`", $this->getCsvColsTable($table)) ."` \n" : " \n")
                      . ") NOT IN (SELECT ". implode(", ", $this->getFkRhs($table)) 
                      . ((count($cols) > 0) ? ", " : "") . implode(", ", $cols) ." \n"
                      . " FROM $this->prefix$table)";
                      
                      //. "WHERE ". $this->getConditions($table);
        }
      } // end foreach
    if ($method == "update") {
      $action = "UPDATE ";
      $set_values = array();
      foreach ($this->csvColMatch as $key => $val) {
      	if (in_array($key, $cols_to_update))
      	  $set_values[] = "$this->prefix$val = $tmp_table.`$key`";
      }
      $where_values = array();
      $where_values[] = "$this->prefix$this->key_col = $tmp_table.`". $this->getCsvKey() ."`";
      foreach ($this->fks as $key => $val) {
      	$where_values[] = "$this->prefix$key = $this->prefix$val";
      }
      $query = $action ."$this->prefix". implode(", $this->prefix", $this->tables) .
      		", $tmp_table \n" .
      		"SET ". implode(", ", $set_values)." \n" .
      		"WHERE ". implode(" AND ", $where_values);
      $queries[] = $query;
    } // end if elseif $method == "update" ... else
    //return $this->getConditions($this->prefix, $this->tables, $this->fks);
    return $queries;
  }
  
  function getFkLhs($table) {
  	global $mysql_fks;
    $lhs = array();
    foreach (array_keys($mysql_fks) as $fk) {
      if ($this->getTable($fk) == $table)
        $lhs[] = $this->getColumn($fk);
    }
    return $lhs;
  }

  function getFkRhs($table) {
    global $mysql_fks;
    $rhs = array();
    foreach ($mysql_fks as $key => $fk) {
      if ($this->getTable($key) == $table)
        $rhs[] = $this->getColumn($fk);
    }
    return $rhs;
  }
  
  function getFkLhsTable($table) {
    global $mysql_fks;
    $rhs = array();
    foreach (array_keys($mysql_fks) as $key => $fk) {
      if ($this->getTable($key) == $table)
        $rhs[] = $this->getTable($fk);
    }
    return $rhs;
  }
  
  function getFkRhsTable($table) {
    global $mysql_fks;
    $rhs = array();
    foreach ($mysql_fks as $key => $fk) {
      if ($this->getTable($key) == $table)
        $rhs[] = $this->getTable($fk);
    }
    return $rhs;
  }
  
  function getFkLhsFull($table) {
    global $mysql_fks;
    $rhs = array();
    foreach (array_keys($mysql_fks) as $key => $fk) {
      if ($this->getTable($key) == $table)
        $rhs[] = $fk;
    }
    return $rhs;
  }
  
  function getFkRhsFull($table) {
    global $mysql_fks;
    $rhs = array();
    foreach ($mysql_fks as $key => $fk) {
      //echo $fk."\n";
      if ($this->getTable($key) == $table)
        $rhs[] = $fk;
    }
    return $rhs;
  }
  
  function hasForeignKey($table) {
  	global $mysql_fks;
    foreach ($mysql_fks as $key => $val) {
      $table1 = $this->getTable($key);
      if ($table1 == $table)
        return true;
    }
  	return false;
  }
  
  function getTable($fk) {
  	$table = explode(".", $fk);
    return $table[0];
  }
  
  function getKeyCol() {
  	global $mysql_key;
  	foreach ($this->csvColMatch as $key => $val) {
  	  if ($this->getColumn($val) == $this->getColumn($mysql_key)) {
  	  	return $key;
  	  }
  	}
    die ("no key column specified in config!");
  	//return 'col 1';
  }
  
  function getConditions($tables, $cols = null) {
    global $debug, $tmp_table;
    //$colMatches = array();
    $colMatches = $this->csvColMatch;
    /*
    foreach ($this->csvColMatch as $key => $colMatch) {
      $colMatches[$key] = $this->prefix . $colMatch;
    }
    */
    if ($debug):
      echo "<pre>";
      print_r($colMatches);
      print_r($tables);
      echo "</pre>";
    endif;
    $conditions = array();
    //$conditions[] = "$this->prefix$this->key = $tmp_table.`". $this->getKeyCol() ."`";    
    
    foreach ($tables as $table) {
      foreach ($colMatches as $key => $val) {
      	if ($this->getTable($val) == $table)
          $conditions[] = "$tmp_table.`$key` = $this->prefix$val";
      }
    }
    
    /*
    if (is_array($cols)) {
      foreach ($cols as $col) {
        $fk_value = "$table.$col";
        foreach ($this->fks as $fk_key => $fk) {
          if ($fk_key == $fk_value)
            $conditions[] = "$fk = ";
        }
      }
    }
    */
    // TODO: get fk conditions
    return implode(" \nAND ", $conditions);
  }

  function getKeyTable() {
  	global $mysql_key, $mysql_tables, $mysql_table_prefix, $db;
    if (!isset($db)) $db = new Db();    
    foreach ($mysql_tables as $table) {
      $q = "SHOW COLUMNS FROM $mysql_table_prefix$table";
      $db->query($q);
      while($row = $db->fetch_array()) {
      	if ($row[0] == $this->getColumn($mysql_key))
          return $table;
      }
    }
  }
  
  function getColumns($table, $columns = null) {
  	global $csv_cols;
    $cols = array();
    if ($columns == null) {
      $columns = $csv_cols;
    }
    foreach ($columns as $column){
      $table1 = explode(".", $column);
      $tab = $table1[0];
      $col = $table1[1];
      if ($table == $tab) {
      	$cols[] = $col;
      }
    }
    return $cols;
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
  
  function setVars($encloser, $escape, $lineend, $terminator) {
  	$this->encloser = $encloser;
    $this->escape = $escape;
    $this->lineend = $lineend;
    $this->terminator = $terminator;
  }
  
  function unescape($escaped) {
  	return str_replace("\\\\", "\\", $escaped);    
  }
}

if ($debug) {
  $csvColMatch = array(
    "col 1" => "product.product_sku",
    "col 2" => "product.product_name",
    "col 3" => "category.category_name",
    //"col 4" => "product_price.product_price",
    "col 5" => "product.attribute",
    "col 6" => "product.product_desc",
  );
  $updateCol = array(
  	"col 1","col 2","col 3","col 4","col 5","col 6"
  );
  
  $csv = new CSV("../EXPORT.csv", ",", "\"", "\"", "\\r", 
                 $csvColMatch, $mysql_table_prefix, $mysql_tables, $mysql_fks, $mysql_key);
  echo "<pre>";
  print_r($csv->getSql("update", $updateCol));
  print_r($csv->getTableOrder());
  print_r($mysql_fks);
  print_r($csv_cols);
  print_r($csv->csvColMatch);
  echo "</pre>";
}

?>
