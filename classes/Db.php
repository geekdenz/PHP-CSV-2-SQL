<?php
/*
 * Created on 13/07/2007
 *
 */
require_once(dirname(__FILE__) . '/../includes.php');

class Db {
  var $conn;
  var $result;
  var $constraints = array(
        'foreignKey' => array(  ),
        'other' => array(  ),
    );
  
  function Db() {
  	global $debug, $mysql_server, $mysql_user, $mysql_password, $mysql_server, 
  		$mysql_database;
    if ($debug) {
      echo "$debug <br />";
      echo "<pre>";
      print_r(get_included_files());
      echo "</pre>";
      echo "mysql db = ".$mysql_database."<br />";	
      echo "mysql user = ".$mysql_user."<br />";	
      echo "mysql pass = ".$mysql_password."<br />";	
    } 
  	$this->conn = mysql_connect($mysql_server, 
                    $mysql_user, $mysql_password);
    mysql_select_db($mysql_database) or die("Could not connect/select MySQL database!");    
  }
  
  function query($queryString) {
  	$this->result = mysql_query($queryString);
  	return $this->result;
  }
  
  function vm_query($queryString) {
  	$this->result = mysql_query($queryString);
  }
  
  function fetch_array() {
  	return mysql_fetch_array($this->result);
  }

  function fetch_assoc() {
    return mysql_fetch_assoc($this->result);
  }

  function getConstraints(  ) {
    global $mysql_constraints, $debug;
    // 'TABLE product_price FOREIGN KEY (product_id) REFERENCES product(product_id)',
    foreach ( $mysql_constraints as $constraint ) {      
      $tokens = preg_split( '/\s+/', $constraint );
      if ( strtolower( $tokens[0] ) == 'table' ) {
        if ( strtolower( $tokens[2] ) == 'foreign'
              && strtolower( $tokens[3] ) == 'key' ) {
          $column1 = substr( $tokens[4], 1, strlen( $tokens[4] ) - 2 );
          echo "<br>column1 = " . $column1;
        }
      }

      if ( strtolower( substr( $constraint, 0, 5 ) ) == 'table' ) {
        echo "<p>$constraint is a table constraint</p>";
        if ( $debug ):
          echo "<p>";
          foreach ( $tokens as $token ) {
            echo "token = $token <br />";
          }
          echo "</p>";
        endif;
      //$this->constraints['foreignKey'][]
      }
    }

    $this->constraints = $mysql_constraints;
  }
}

//$_GLOBALS['db'] = new Db();
?>
