<?php
/*
 * file: index.php
 * project: csvtosql
 * Created on 14/07/2007 at 3:35:54 PM
 *
 * @author Tim-Hinnerk Heuer (tim@ihostnz.com)
 * Copyright 2007 iHostNZ - Tim-Hinnerk Heuer
 *
 * Todo:
 * user interface for CSV import
 */

session_start();

//error_reporting(E_ALL);

require_once('includes.php');
# copy protection
$header = file('header.html.php');
$footer = file('footer.html.php');
$header = implode('', $header);
$footer = implode('', $footer);
$md5_header = md5($header);
$md5_footer = md5($footer);
//echo "md5 header = ". $md5_header ."<br />";
//echo "md5 footer = ". $md5_footer ."<br />";
if (!$debug) {
  if (   $md5_header != 'f9be5e7885168ea40d28b9d24a1be302'
	  || $md5_footer != 'd66a0bbeb2e17c5b3edfee6e06fb19d1') {
  	echo "md5 header = $md5_header<br />";
  	echo "md5 footer = $md5_footer<br />";
  	die("Copyright issue with header or footer! Please contact Tim@ihostnz.com.");
  }
}

# /copy protection

if ($_POST['user'] == $username	&& $_POST['password'] == $password)
	$_SESSION['logged'] = true;
if ($_POST['logout'])
	$_SESSION['logged'] = false;

if (!$_SESSION['logged']) {
    include "login.php";
}
else {
	// CSV file was uploaded
	if (isset($_POST['submitcsv'])) { // csvfile form submitted
	  $sess['upload'] = $upload = new Upload;
	  $upload->fromPostToFile($upload_dir);
	  include('ui_csvhandle.php');
	}
	// After upload CSV file processing
	elseif (isset($_POST['submitimport'])) {
	  $cols = array();
	  //handleCSVPost($_POST);

	  // Setup the variables for getting the SQL
	  $csv_cols = $_POST['sCols'];
	  $csvColMatch = array();
	  foreach ($csv_cols as $key => $val) {
	  	if ($debug) echo "value = $val <br />";
	  	if ($val != 'SPACER')
	      $csvColMatch["col ". ($key + 1)] = $val;
	  }
	  $mysql_key = $_POST['identifier'];
	  if ($debug)
	  	echo "<br />mysql key = $mysql_key <br />";
	  $cols_to_update = $_POST['updateCol'];

	  foreach ($_POST as $key => $value) {
	    if (is_array($value)) {
	      foreach ($value as $key2 => $value2) {
	          $cols['col '. ($key2 + 1)] = $value2;
	      }
	    }
	  }
	  if ($debug) {
	  	echo "<pre>";
	  	print_r($_POST);
	  	echo "</pre>";
	  }

	  //asort($cols);
	  ?><br /><?php
	  if ($debug) {
	  	echo "<pre>";
	  	print_r($cols);
	  	echo "</pre>";
	  }

	  // get insert SQL if update is not ticked -> insert queries
	  $csv = new CSV($_POST['upload_file'], $_POST['csv_delimiter'], $_POST['csv_encloser'],
	  			$_POST['csv_escape'], $_POST['csv_lineend'],
	  			$csvColMatch, $mysql_table_prefix, $mysql_tables, $mysql_fks,
	            $mysql_key);
	  if (!isset($_POST['update'])) {
	  	$sql = $csv->getSql();
	  }
	  else {
	  	$sql = $csv->getSql("update", $cols_to_update);
	  }

	  if ($debug) {
	  	echo "<pre>";
	  	print_r($sql);
	  	echo "</pre>";
	  }
	  // perform all queries generated
	  //$sql += $post_queries;
	  if ($debug) echo "<pre> QUERIES = \n\n";
	  foreach ($sql as $q) {
	  	$db->query($q);
	  	if ($debug) echo $q."\n\n";
	  }
	  if (!isset($_POST['update']) || true) {
	    foreach ($post_queries as $q) {
		    $db->query($q);
		    echo $q."\n\n";
	    }
	    die("<pre>". print_r($post_queries,true) ." executed");
	  }
	  if ($debug) {
	    echo "</pre>";
	  }
	}// end CSV file processing
  elseif (isset($_POST['submitzip'])) {
    // zip image file upload
  	require_once("unzip.php");
  }
	// default is to show upload user interface
	else {
	  include('ui_upload.php');

	}
	if ($debug) {
	  echo "<br />size of mysql tables array = " . sizeof($mysql_tables);
	}
} // /if logged in
require_once('footer.html.php');

/*
 * handle user input and adjust insert/update queries
 * @todo handle update, select query to insert from table tmp
 * @param $post usually this is $_POST;
 */
function handleCSVPost($post = null) {
  global $debug;
  if ($post == null)
  	$post =& $_POST;
  $update = false;
  if (isset($post['identifier'])) {
    $sql = "UPDATE ";
    $sqlCondLast = " WHERE ". $post['identifier'] ." = ";
    $update = true;
  }
  else {
    $sql = "INSERT INTO ";
  }
  $prevTCol = "";
  if (isset($post['sCols'])) {
  	$i = 0;
    $nCols = 0;
    foreach ($post['sCols'] as $key => $col) {
      $tCol = explode(".", $col);
      $nextTCol = explode(".", $post['sCols'][$key + 1]);
      if ($prevTCol != $tCol[0]) {
        //if ()
        $queries[$i] .= $sql . $tCol[0] ." (". $tCol[1] .", ";
        $nCols++;
      }
      else {
      	echo "<p>key = $key == ".(sizeof($post['sCols']) - 1)."</p>";

        if ($nextTCol[0] != $tCol[0]) {
          $queries[$i] .= $tCol[1] .") SELECT ";
          // code to generate select from tmp
          $i++;
        }
        else {
          $queries[$i] .= $tCol[1] .", ";
          $nCols++;
        }
      }
      if ($update) {
        $queries[$i] .= $sqlCondLast;
      }
      $prevTCol = $tCol[0];
    }
  }
}

?>
