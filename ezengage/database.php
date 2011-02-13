<?php
/**
 * Description: Creates database tables used by  ezengage wordpress plugin
 */

//Database versions
global $ezengage_db_version;
$ezengage_db_version = "1";

global $wpdb;
define('EZENGAGE_IDENTITY_TABLE_NAME', $wpdb->prefix . 'ezengage_identity'); 

//Create database tables needed by the 
function ezengage_db_create () {
    ezengage_create_table_identity();
}
 
//Create identity table
function ezengage_create_table_identity(){
    global $wpdb;
    global $ezengage_db_version;
    $installed_ver = get_option("ezengage_db_version", '0');
    $table_name = EZENGAGE_IDENTITY_TABLE_NAME;
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ||
        $installed_ver != $ezengage_db_version ) {
        $sql .= "CREATE TABLE " . $table_name . "(
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                user_id int(11) NOT NULL,
                provider varchar(20) NOT NULL,
                identity varchar(100) NOT NULL,
                profile text NOT NULL,
                sync tinyint(1) NOT NULL DEFAULT 0,
                PRIMARY KEY  (`id`),
                UNIQUE KEY `identity` (`identity`)
            );";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        update_option("ezengage_db_version", $ezengage_db_version);
    }
}
?>
