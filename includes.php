<?php
/*
 * Created on 13/07/2007
 *
 */
session_start();
require_once('debug.php');
//require_once('classes.php');
require_once('header.html.php');

if (strpos($_SERVER['SERVER_SOFTWARE'], 'Win32') !== false) {
  $path_seperator = "\\";
}
else {
  $path_seperator = "/";
}
require_once(dirname(__FILE__) .$path_seperator.'config.php');

if ($path_seperator == "\\") {
  $upload_dir = str_replace("/", "\\", $upload_dir);
}

$ver = explode( '.', PHP_VERSION );

$ver_num = $ver[0] . $ver[1] . $ver[2];

if ( $ver_num >= 500 ) {
  function __autoload($class_name) {
  	global $path_seperator;
    require_once("classes". $path_seperator . $class_name . ".php");
  }
}
else {
  $handle = opendir(dirname(__FILE__) . $path_seperator . 'classes');
  while ($file = readdir($handle)) {
  	$file_name = explode('.', $file);
    $extension = $file_name[sizeof($file_name) - 1];
    if ($extension == "php") {
      require_once("classes$path_seperator$file");
      if ($debug)
        echo "Loaded classes$path_seperator$file<br />\n";
    }
  }

}

$sess =& $_SESSION;

$sess['db'] = $db = new Db();
//$sess['csv'] = $csv = new CSV("EXPORT.csv");

ini_set( "auto_detect_line_endings", "1" );

if ($debug):
  echo "server = " . $_SERVER['SERVER_SOFTWARE'];
  if (strpos($_SERVER['SERVER_SOFTWARE'], 'Win32') !== false) {
    echo "<br />\nyes";
  }
endif;

?>
