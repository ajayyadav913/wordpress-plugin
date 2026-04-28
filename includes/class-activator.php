<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Codidot_Rates_Activator {
    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dynamic_intl_rates';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            country varchar(100) NOT NULL,
            start_weight float NOT NULL,
            end_weight float NOT NULL,
            price float NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}