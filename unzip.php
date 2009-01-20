<?php
/*
 *********** vm_csvimporter *******************
 * unzip.php
 * Created on 19/11/2007
 *
 * Author: Tim-Hinnerk Heuer (Tim@iHostNZ.com)
 * Copyright iHostNZ Ltd. 2007
 * Distributed under the Commercial iHostNZ License
 *********** vm_csvimporter *******************
 */
require_once("includes.php");
if (!defined('IMAGEPATH')) {
	die("Configuration File does not have the right path!");
} 

$files = array();

function listdir($start_dir='.') {
  $files = array();
  if (is_dir($start_dir)) {
    $fh = opendir($start_dir);
    while (($file = readdir($fh)) !== false) {
      # loop through the files, skipping . and .., and recursing if necessary
      if (strcmp($file, '.')==0 || strcmp($file, '..')==0) continue;
      $filepath = $start_dir . '/' . $file;
      if ( is_dir($filepath) )
        $files = array_merge($files, listdir($filepath));
      else
        array_push($files, $filepath);
    }
    closedir($fh);
  } else {
    # false if the function was called with an invalid non-directory argument
    $files = false;
  }

  return $files;
}
//$vm_image_path = "../proparts/components/com_virtuemart/shop_image/product/"; 

//$path = str_replace($path_seperator . "upload", "", $upload_dir) . $path_seperator;
$path = $upload_dir . $path_seperator . "photos";
if (!file_exists($path) || !is_dir($path)) {
  die("Photo upload directory $path does not exist!");
}
//$file = $path . $path_seperator . "Photos.zip";
$file = $path . $path_seperator . $upload_filename;
if ($debug) {
  echo "<p>Photo file = $file</p>";
}
$upload = new Upload;
$upload->fromPostToFile($path, "filezip", $file);
 
$archive = new PclZip($file);
 
$store_in = $path;
if (($archive->extract($p_path = $store_in)) == 0) {
  die("<span class='error'>Error : ".$archive->errorInfo(true)."</span>");
}
else {
	echo "<p>unzipped successfully into $store_in!</p>";
}

// recurse into sub dirs
$files = listdir($store_in);

$image_files = array();
$product_sku = array();

foreach ($files as $file) {
  $extension = strtoupper(substr($file, -3));
	if ($extension == "GIF" || $extension == "JPG") {
		$image_files[] = $file;
    $file_parts = explode("/", $file);
    $product_sku[] = substr($file_parts[count($file_parts) - 1], 0, -4);
	}
}

for ($i = 0; $i < count($image_files); $i ++) {
	// take the image and sku
  $image = $image_files[$i];
  $sku = $product_sku[$i];
  // get the lower case extension (plus '.' in front)
  $extension = "." . strtolower(substr($image, -3));
  // give it a unique identifier for thumbnail and full image  
  $uniqueThumbId = md5(uniqid("VirtueMart")) . $extension;
  $uniqueFullId = md5(uniqid("FullImage")) . $extension;
  // resize it (full and thumbnail)
  new Img2Thumb($image, 500, 500, IMAGEPATH."/product/". $uniqueFullId);
  new Img2Thumb($image, PSHOP_IMG_WIDTH, PSHOP_IMG_HEIGHT, IMAGEPATH."/product/". $uniqueThumbId);    
  // take the used identifiers and put them into db
  $sql = "UPDATE ${mysql_table_prefix}product SET " .
      "product_thumb_image = '$uniqueThumbId', " .
      "product_full_image = '$uniqueFullId' " .
      "WHERE product_sku LIKE '%$sku%'";
  if ($debug) $debug_vars['sql'][] = $sql;
  $db->query($sql);
}

if ($debug) {
  echo "<pre>";
  print_r($image_files);
  print_r($product_skus);
  print_r($debug_vars);
  echo "</pre>";
}
 
include("footer.html.php");
?>
