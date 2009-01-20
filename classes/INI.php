<?php
/*
 * file: INI.php 
 * project: csvtosql
 * Created on 22/07/2007 at 6:10:10 PM
 *
 * @author Tim-Hinnerk Heuer (tim@ihostnz.com)
 * Copyright 2007 iHostNZ - Tim-Hinnerk Heuer
 * 
 * Todo:
 * TODO
 */
class INI {
  var $size;
  function get_size($sName) {
    $sSize = ini_get($sName);
    $sUnit = substr($sSize, -1);
    $iSize = (int) substr($sSize, 0, -1);
    switch (strtoupper($sUnit))
    {
        case 'Y' : $iSize *= 1024; // Yotta
        case 'Z' : $iSize *= 1024; // Zetta
        case 'E' : $iSize *= 1024; // Exa
        case 'P' : $iSize *= 1024; // Peta
        case 'T' : $iSize *= 1024; // Tera
        case 'G' : $iSize *= 1024; // Giga
        case 'M' : $iSize *= 1024; // Mega
        case 'K' : $iSize *= 1024; // kilo
    };
    $this->size = $iSize;
    return $iSize;
  }
}
?>
