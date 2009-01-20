<?php
/*
 * Created on 11/07/2007
 *
 * Config file for csvtosql
 *
 * Please give:
 *  database details
 *  table to be updated
 *  table structure
 *  unique data column
 */

$username = " Username ";
$password = " Password ";

//$virtuemart_path = "E:\\xampp\\htdocs\\proparts";
$virtuemart_path = "..";

$mysql_table_prefix = 'jos_vm_';
$mysql_server    = 'localhost';
$mysql_user      = 'proparts';
$mysql_password  = 'proparts';
$mysql_database  = 'vm_proparts';
$mysql_tables    = array(
      'product',
      'category',
      'product_category_xref',
      'product_price',
      );
$mysql_constraints = array(
      'TABLE product_price FOREIGN KEY (product_id) REFERENCES product(product_id)',
      'TABLE product_category_xref FOREIGN KEY (product_id) REFERENCES product(product_id)',
      'TABLE product_category_xref FOREIGN KEY (category_id) REFERENCES category(category_id)',
      );
$mysql_key = 'product.product_sku';

$mysql_fks = array(
      'product_price.product_id'          => 'product.product_id',
      'product_category_xref.product_id'  => 'product.product_id',
      'product_category_xref.category_id' => 'category.category_id',
      );

// only edit the following if your CSV file has different columns or delimiters
$csv_delimiter = ',';
$csv_encloser  = '"';
$csv_escape    = '"';
$csv_lineend   = '\r\n';
$csv_cols = array(
              'product.product_sku',
              'product.product_name',
              'category.category_name',
              'product_price.product_price',
              'product.attribute',
              'product.product_desc',
            );


/********* DO NOT EDIT AFTER THIS UNLESS YOU KNOW WHAT YOU ARE DOING! **************/
// You should not need to edit the following:
$upload_dir = dirname(__FILE__) . "/upload";
$upload_filename = "tmp.csv";
$tmp_table = "tmp";
$number_of_lines_to_display = 10;

// post queries for VirtueMart:
$post_queries = array(
	"UPDATE jos_vm_category SET category_published='Y'", # need products online
	"INSERT INTO jos_vm_category_xref (category_parent_id, category_child_id)
  	 SELECT 0, category_id
  	 FROM jos_vm_category",
	"UPDATE jos_vm_category SET vendor_id=1",
	"INSERT INTO `jos_vm_shopper_group` " .
	"VALUES (5,1,'-default-','This is the default shopper group.','0.00',1,1)",
	"UPDATE jos_vm_product_price SET shopper_group_id = 5",
	"UPDATE jos_vm_product SET vendor_id=1",
	//"UPDATE jos_vm_product set product_publish='Y'", # need only products online
);

define('_VALID_MOS', true);
require_once($virtuemart_path . "/configuration.php");
require_once($virtuemart_path . "/administrator/components/com_virtuemart/virtuemart.cfg.php");
$store_in = "upload/photos";
?>
