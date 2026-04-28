<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Codidot_Rates_Ajax {
    public static function init() {
        add_action( 'wp_ajax_codi_get_rate', array( __CLASS__, 'get_rate' ) );
        add_action( 'wp_ajax_nopriv_codi_get_rate', array( __CLASS__, 'get_rate' ) );
    }

    public static function get_rate() {
        // --- Security Check: Nonce Verification Added ---
        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'codi_rates_ajax_nonce' ) ) {
            wp_send_json_error( ['message' => 'Security check failed.'] );
        }

        global $wpdb;
        
        $country = isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '';
        $weight  = isset( $_POST['weight'] ) ? floatval( wp_unslash( $_POST['weight'] ) ) : 0;

        if ( $country === 'nz' ) {
            wp_send_json_error( ['message' => 'Contact Sales for Zone Pricing'] );
        }

        $price = $wpdb->get_var( 
            $wpdb->prepare(
                "SELECT price FROM `{$wpdb->prefix}dynamic_intl_rates` WHERE country = %s AND start_weight <= %f AND end_weight >= %f LIMIT 1",
                $country, 
                $weight, 
                $weight
            ) 
        );

        if ( $price !== null ) {
            wp_send_json_success( ['price' => number_format( (float) $price, 2, '.', '' )] );
        } else {
            $max_weight = $wpdb->get_var( 
                $wpdb->prepare(
                    "SELECT MAX(end_weight) FROM `{$wpdb->prefix}dynamic_intl_rates` WHERE country = %s", 
                    $country
                ) 
            );
            
            if ( $max_weight && $weight > $max_weight ) {
                wp_send_json_error( ['message' => 'Contact sales for weights over ' . esc_html( $max_weight ) . ' Kg.'] );
            } else {
                wp_send_json_error( ['message' => 'Rate not found.'] );
            }
        }
    }
}