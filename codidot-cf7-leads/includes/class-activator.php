<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Codidot_CF7_Activator {
    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'codidot_cf7_leads';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE `{$table_name}` (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            form_id bigint(20) unsigned NOT NULL,
            form_title varchar(255) NOT NULL,
            lead_data longtext NOT NULL,
            date_submitted datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}
