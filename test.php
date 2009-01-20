<?php

set_time_limit(60*60);
echo "<p>" . ini_get("max_execution_time") . "</p>";

include("classes/PclZip.php");



$archive_name = "E:\\xampp\\htdocs\\vm_csvimporter\\upload\\photos\\Photos.zip";
$archive = new PclZip($archive_name);
$path = "E:\\xampp\\htdocs\\vm_csvimporter\\upload\\photos";
$current_dir = dirname(__FILE__);
$path = str_replace($current_dir, "", $path);
$path = str_replace("\\", "/", $path);
$path = substr($path, 1);
echo "<pre>";
echo "$path\n";
print_r($archive->extract($p_path = $path));
echo "</pre>"; 
?>