<?php
/*
 * file: ui_upload.php 
 * project: csvtosql
 * Created on 14/07/2007 at 3:57:00 PM
 *
 * @author Tim-Hinnerk Heuer (tim@ihostnz.com)
 * Copyright 2007 iHostNZ - Tim-Hinnerk Heuer
 *
 * This file only makes sense when it is included in index.php 
 */
require_once('includes.php');
$maxFileSize = new INI;

?>
<p>Maximum file size allowed is <? 
  echo $maxFileSize->get_size('post_max_size') / (1024); ?>KB.</p>

<h3>CSV Import</h3>
<p>Select delimiter, encloser and escape symbol:</p>
<form name="csvfile" enctype="multipart/form-data" action="<?php 
  echo $_SERVER['PHP_SELF']; ?>" method="POST">
<table>
  <tr>
    <td align="right">delimiter:</td>
    <td>
      <input type="text" name="delimiter" value='<? echo $csv_delimiter; ?>' size="3" maxlength="5" />
    </td>
  </tr>
  <tr>
    <td align="right">encloser:</td>
    <td>
      <input type="text" name="encloser" value='<? echo $csv_encloser; ?>' size="3" maxlength="5" />
    </td>
  </tr>
  <tr>
    <td align="right">escape:</td>
    <td>
      <input type="text" name="escape" value='<? echo $csv_escape; ?>' size="3" maxlength="5" />
    </td>
  </tr>
  <tr>
    <td align="right">escaped EOL (end of line character) e.g. '\r' (windows), '\n' (unix), '\r\n' (when others fail):</td>
    <td>
      <input type="text" name="lineend" value='<? echo $csv_lineend; ?>' size="3" maxlength="5" />
    </td>
  </tr>
  <tr>
    <td>
      <input type="hidden" name="MAX_FILE_SIZE" value="<? 
  echo $maxFileSize->get_size('post_max_size');
  ?>" />
      <input type="file" name="filecsv" width="40" />
    </td>
    <td><input type="submit" name="submitcsv" value="Upload file"/></td>
  </tr>
</table>
</form>

<h3>Upload Photo archive (zip, jpg)</h3>
<p>Here you can select a zip file that has product images
with the name being <br />
&lt;product_sku&gt;.jpg or &lt;product_sku&gt;.gif</p>
<form name="photo_upload" enctype="multipart/form-data" action="<?php 
  echo $_SERVER['PHP_SELF']; ?>" method="POST">
<table>
  <tr>
    <td>
      <input type="hidden" name="MAX_FILE_SIZE" value="<? 
  echo $maxFileSize->get_size('post_max_size');
  ?>" />
      <input type="file" name="filezip" width="40" />
    </td>
    <td><input type="submit" name="submitzip" value="Upload Photo archive (zip)"/></td>
  </tr>
</table>
  