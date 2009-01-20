<?php
/*
 * file: ui_csvhandle.php 
 * project: csvtosql
 * Created on 14/07/2007 at 4:12:00 PM
 *
 * @author Tim-Hinnerk Heuer (tim@ihostnz.com)
 * Copyright 2007 iHostNZ - Tim-Hinnerk Heuer
 *
 * This file only makes sense when it is included in index.php 
 */
require_once('includes.php');

?>
<script type="text/javascript"><!--
  function addItem()
  {
    var allCols = document.csv_import.allCols;
    if(allCols.selectedIndex != -1)
    {
      var el = document.getElementsByName("sCols[]")[0];
      var entry = document.createElement("option");
      var item = allCols.options[allCols.selectedIndex];
      entry.text = "col " + (el.length+1) + ": " + item.text;
      entry.value = item.value;
      if(item.value!="SPACER"){
        allCols.remove(allCols.selectedIndex);
      }
      var option = null;
      if (document.all)
        option = entry.length;
      el.add(entry, option);
    }
  }
  function removeItem()
  {
    var allCols = document.getElementsByName("sCols[]")[0];
    if(allCols.selectedIndex != -1)
    {
      var el = document.csv_import.allCols;
      var entry = document.createElement("option");
      var item = allCols.options[allCols.selectedIndex];
      entry.text = item.value;
      entry.value = item.value;
      allCols.remove(allCols.selectedIndex);

      var allOptions = allCols.getElementsByTagName("option");
      for (var i = 0; i < allOptions.length; i++) {
        allOptions[i].innerHTML = "col " + (i+1) + ": " + allOptions[i].value;
      }
      var option = null;
      if (document.all)
        option = entry.length;
      if(item.value!="SPACER"){
      el.add(entry, option);
      }
    }
  }
  function selectAll()
  {
    var allCols = document.getElementsByName("sCols[]")[0];
    for(var i = 0; i < allCols.length; i++)
    {
      allCols.options[i].selected = true;
    }
  }

  /**
   * Select elements by their class name
   *
   * @author Dustin Diaz <dustin [at] dustindiaz [dot] com>
   * @link   http://www.dustindiaz.com/getelementsbyclass/
   */
  function getElementsByClass(searchClass,node,tag) {
  	var classElements = new Array();
    if ( node == null )
      node = document;
    if ( tag == null )
      tag = '*';
    var els = node.getElementsByTagName(tag);
    var elsLen = els.length;
    //var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)");
    var pattern = new RegExp(""+searchClass+"");
    for (i = 0, j = 0; i < elsLen; i++) {
      if ( pattern.test(els[i].className) ) {
        classElements[j] = els[i];
        j++;
      }
    }
    return classElements;
  }
  
  function toggleVisible(element, initial) {
	var state = element.style.visibility;
	if (state == "") {
      state = initial;
    }
	if (state == "collapse") {
	  element.style.visibility = "visible";
	} else {
	  element.style.visibility = "collapse";
	}
  }
  
  function toggleVisibleElements(elements, initial) {
    for (var i=0; i< elements.length; i++) {
      //window.alert('in toggleVisibleElements element class = ' + element.className);
  	  toggleVisible(elements[i], initial);
  	}
  }
  
  function toggleVisibleClass(tag, className, initial) {
  	classElements = getElementsByClass(className, null, tag);
  	toggleVisibleElements(classElements, 'collapse');
  } 
  
  function toggleVisibleName(name, initial) {
  	nameElements = document.getElementsByName(name);
  	toggleVisibleElements(nameElements, 'collapse');
  } 
//--></script>
<?php

// get the schema of each table in config.
$i = 0;
foreach ($mysql_tables as $table) {
  $i++;
  $q = "SHOW COLUMNS FROM $mysql_table_prefix$table";
  if ($debug):
    $sess['db']->query($q);
    echo "<table border='1'>";
    echo "<tr><th colspan='7'><b>$table</b></th></tr>";
    echo "<tr>" .
        "<th>NAME</th>" .
        "<th>TYPE</th>" .
        "<th>NULL</th>" .
        "<th>KEY</th>" .
        "<th>DEFAULT</th>" .
        "<th>EXTRA</th>" .
        "</tr>";
    while ($row = $sess['db']->fetch_assoc()) {  	
    	echo "<tr>\n";
      foreach ($row as $col) {
        echo "  <td>$col</td>\n";
      }
      echo "</tr>\n";  	
    }
    echo "</table><br />";
  endif;
}
if ($debug) echo "<br />\n" . $i;
// prompt user to insert numbers from CSV header in appropriate field of
//    sql table.
// display it with form fields behind every column.
// show how many columns have values in CSV file.
// show numbers 1, 2, 3, ..., $number_of_columns in table row. 
// display CSV content (first $number_of_lines_to_display lines).

?>
<form name="csv_import" enctype="multipart/form-data" action="<?php 
  echo $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="selectAll();">

<!-- <form name="csv_import" action="import-csv.php?ID=<?php print $_GET['ID'];?>&Table=<?php print $table;?>" method="post" enctype="multipart/form-data" onSubmit="selectAll();"> -->
<table class="Normal" width="650">
<tr align="center">
  <th align="center"><h1>CSV File Import</h1></th>
</tr>
<tr align="center">
  <td><!--<input type="file" name="userfile">--></td>
</tr>
<tr align="center">
  <td>
    <p align="left">
    <!-- 
    <span style="font-size: 0.8em">&nbsp;
      <input type="radio" value="ColIsHead" name="Cols" />
      Column name in first line
    </span><br />
     -->
    <span style="font-size: 0.8em">&nbsp;
      <input type="radio" name="Cols" checked="checked" value="Select" />
        Select columns
    </span>    
    </p>
  </td>
</tr>
<tr align="center">
  <td>
  <select size="20" name="sCols[]" style="width:300px;" onclick="removeItem()" multiple="multiple">
  <?
    foreach ($csv_cols as $key => $col) {
    ?>
    <option id='<?=$col?>' value='<?=$col?>'>col <?=($key+1) .": ". $col?></option>
    <? } ?>
  </select>
  <select size="20" name="allCols" style="width:300px;" onclick="addItem()">
  <option value="SPACER">[skip col]</option>
  <?php
    $options = "";
    $options2 = "";
    foreach ($mysql_tables as $table) {
      $sess['db']->query("show columns from $mysql_table_prefix$table");
      while($result = $sess['db']->fetch_array($query)) {
      	$keyCols = explode(".", $mysql_key);
      	$keyCol = $keyCols[1];
      	if (!in_array($table.".".$result[0], $csv_cols)) { 
	        $options .= "<option id='"
	                      . htmlspecialchars($table.".".$result[0])
	                      ."' value='"
	                      . htmlspecialchars($table.".".$result[0]) 
	                      . "'>"
	                      . htmlspecialchars($table.".".$result[0]) ."</option>\n";
      	}                      
        $options2 .= "<option id='"
                      . htmlspecialchars($table.".".$result[0])
                      ."' value='"
                      . htmlspecialchars($table.".".$result[0]) 
                      . "'". (($keyCol == $result[0]) ? "selected='selected'" : "") .">"
                      . htmlspecialchars($table.".".$result[0]) ."</option>\n";
        $equalto .= "<br>$mysql_key == ".$result[0];
      }
    }
    print $options;
  ?>
  </select></td>
</tr>
<tr align="center">
  <td>
  
  <input type="checkbox" name="update" value="ON" 
  		onclick="toggleVisible(document.getElementById('updateCols'), 'collapse'); 
  				 toggleVisibleName('updateCol[]', 'collapse'); " /> 
    <font size="2">Overwrite rows with the same contents in
  <select name="identifier"><?php print $options2; ?></select>
  </font></td>
</tr>
</table>

<?php
if ( $debug ):
  echo "upload->file = ". $upload->file ."<br />";
endif;
?>

<p>
<?php
if ($debug) {
  echo "<pre>POST=\n";
  print_r($_POST);
  echo "\nGET=\n";
  print_r($_GET);
  echo "</pre";
}
?>
Your CSV file contains:
</p>
<input type="hidden" name="upload_file" value='<?=$upload->file?>' />
<input type="hidden" name="csv_delimiter" value='<?=$_POST['delimiter']?>' />
<input type="hidden" name="csv_encloser" value='<?=$_POST['encloser']?>' />
<input type="hidden" name="csv_escape" value='<?=$_POST['escape']?>' />
<input type="hidden" name="csv_lineend" value='<?=$_POST['lineend']?>' />
<?php
// $upload->file, $csv_delimiter, $csv_encloser, $csv_escape, $csv_lineend

$csv = new CSV($upload->file, $_POST['delimiter'], 
               $_POST['encloser'], $_POST['escape'], $_POST['lineend']);

$columns = array();
$maxlength = 0;

$csv->import($columns);

$q = "SELECT count(*) AS number FROM $tmp_table;";
if ($db->query($q)) {
  if ($debug)
    echo "<br />yes query success<br />";
  $row = $db->fetch_assoc();
}
else {
  if ($debug)
    echo "<br />no way it sux.<br />";
}
$num_records = $row['number'];
if ($debug) {
  echo "num records = $num_records <br />";
} 
if ($num_records > 0) {
  ?>
<table border="1" cellspacing="5" cellpadding="5" class="csv">
  <?php
  $q = "SELECT * FROM $tmp_table;";
  $sess['db']->query($q);
  
  for ($i = 0; $i <  $number_of_lines_to_display; 
       $i++ ) {
    $row = $sess['db']->fetch_assoc();
    if ($i == 0) {
    	echo "<tr id='updateCols'>";
      echo "<th colspan='".count($row)."'>Check to update (checked by default)</th>\n";
      echo "</tr>";
    	echo "<tr>";
      for ($j = 0; $j < count($row); $j++) {
        echo "<th>".
  			"<input class='updateCols' type='checkbox' checked='checked' name='updateCol[]' value='col ". ($j + 1) ."' />".
  			"col ". ($j + 1) .":</th>\n";
      }  
      echo "</tr>";
    }
    echo "<tr>\n";
    foreach ($row as $col) {
    	echo "<td>$col</td>\n";
    }  
    echo "</tr>\n";
  }
  echo "<tr><td colspan='". ($j + 1) ."' align='center'>...</td></tr>"
?>
</table><?
} // /if ($num_records > 0)
else {
  echo "<p class='error'>Error importing. Probably wrong EOL character.</p>";
  if ($debug) {
  	echo "No result from tmp table.";
  }
}
?>
<br />
<input type="submit" name="submitimport" value="Save in SQL Database"/>
</form>
<br />
&nbsp;