<?php
/**
 * Description: Creates database tables used by  ezengage wordpress plugin
 */

//Database versions
global $ezengage_db_version;
$ezengage_db_version = "2";

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
        $sql .= "CREATE TABLE " . $table_name . " (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                user_id int(11) NOT NULL,
                provider varchar(20) NOT NULL,
                identity varchar(100) NOT NULL,
                profile text NOT NULL,
                sync tinyint(1) NOT NULL DEFAULT 0,
                enable_avatar tinyint(1) NOT NULL DEFAULT 1,
                avatar_url varchar(255) NULL,
                PRIMARY KEY  (`id`),
                UNIQUE KEY `identity` (`identity`)
            );";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        update_option("ezengage_db_version", $ezengage_db_version);
    }
    if(intval($installed_ver) < 2){
        update_ezegage_identity_avatar_url();   
    }
}

function update_ezegage_identity_avatar_url(){
    global $wpdb; 
    $sql = "SELECT id,profile FROM " . EZENGAGE_IDENTITY_TABLE_NAME . " WHERE avatar_url is NULL;";
    $identities =  $wpdb->get_results($wpdb->prepare($sql));
    foreach($identities as $identity){
        $profile = json_decode($identity->profile, true);     
        $wpdb->update(EZENGAGE_IDENTITY_TABLE_NAME, array('avatar_url' => $profile['avatar_url']), array('id' =>$identity->id));
    }
}
?>
