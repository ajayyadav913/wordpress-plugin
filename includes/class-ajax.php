<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Codidot_Rates_Ajax {
    public static function init() {
        add_action( 'wp_ajax_codi_get_rate', array( __CLASS__, 'get_rate' ) );
        add_action( 'wp_ajax_nopriv_codi_get_rate', array( __CLASS__, 'get_rate' ) );
    }

    public static function get_rate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dynamic_intl_rates';
        
        $country = sanitize_text_field( $_POST['country'] );
        $weight  = floatval( $_POST['weight'] );

        if($country === 'nz') {
            wp_send_json_error( ['message' => 'Contact Sales for Zone Pricing'] );
        }

        $query = $wpdb->prepare(
            "SELECT price FROM $table_name WHERE country = %s AND start_weight <= %f AND end_weight >= %f LIMIT 1",
            $country, $weight, $weight
        );
        
        $price = $wpdb->get_var($query);

        if ( $price !== null ) {
            wp_send_json_success( ['price' => number_format($price, 2)] );
        } else {
            $max_weight = $wpdb->get_var($wpdb->prepare("SELECT MAX(end_weight) FROM $table_name WHERE country = %s", $country));
            if($max_weight && $weight > $max_weight) {
                wp_send_json_error( ['message' => 'Contact sales for weights over ' . $max_weight . ' Kg.'] );
            } else {
                wp_send_json_error( ['message' => 'Rate not found.'] );
            }
        }
    }
}