<?php
/*
 * file: Upload.php 
 * project: csvtosql
 * Created on 22/07/2007 at 6:10:43 PM
 *
 * @author Tim-Hinnerk Heuer (tim@ihostnz.com)
 * Copyright 2007 iHostNZ - Tim-Hinnerk Heuer
 * 
 * Todo:
 * TODO
 */
require_once(dirname(__FILE__) . '/../includes.php');

class Upload {
    var $file;
    var $filehandle;

    /**
       * get a file from POST of user (html form)
       * @param string $upload_dir upload directory
     */
    function fromPostToFile($upload_dir = ".", $inputname = "filecsv", $upload_filename_new = 0) {
      global $debug, $upload_filename, $path_seperator;
      if ($upload_filename_new != 0) {
        if ($debug) {
          echo "<p>In changed upload file name</p>";
        }
        $this->file = $upload_file = $upload_filename = $upload_filename_new;
      }
      else {
        //$this->file = $upload_file = $upload_dir . "/" . basename($_FILES['filecsv']['name']);
        // for simplicity only use one file name
        $this->file = $upload_file = addslashes($upload_dir . $path_seperator . $upload_filename);
      }
  
      if ($debug) {
        echo $_FILES[$inputname]['name'] . "<br>\n";
        echo $upload_file . "<br>\n";
        echo $_FILES[$inputname]['tmp_name'] . "\n";
      }
   
      if (move_uploaded_file($_FILES[$inputname]['tmp_name'], $upload_file)) {
        return "File is valid, and was successfully uploaded.\n";
      }
      else {
        return "Possible file upload attack!\n";
      }    
    }
    
    function convertFile() {
      global $debug;
      $this->filehandle = @fopen($this->file, "r");
      $tmpFile = @fopen("tmp.csv", "w");
      if ($this->filehandle) {
        while (!feof($this->filehandle)) {
          $buffer = fgets($this->filehandle, 4096);
          $buffer = substr( $buffer, 0, strlen( $buffer ) - 1  );
          $buffer .= "\r\n";
          if ($debug) {
            if ($tmpFile == null) {
              echo "tmpFile = null<br />\n";
            }
          }
          fputs( $tmpFile, $buffer );
        }
      }
      fclose($this->filehandle);
      // replace old file with new one
      $this->filehandle = @fopen($this-file, "w");
      $fcontents = file_get_contents("tmp.csv");
      fputs($this->filehandle, $fcontents);
    }
}
?>
