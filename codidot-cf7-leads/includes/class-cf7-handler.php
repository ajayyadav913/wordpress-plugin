<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Codidot_CF7_Handler {
    public static function init() {
        add_action( 'wpcf7_before_send_mail', [ __CLASS__, 'save_lead_to_db' ], 10, 3 );
    }

    public static function save_lead_to_db( $contact_form, &$abort, $submission ) {
        global $wpdb;

        $submission = WPCF7_Submission::get_instance();
        if ( ! $submission ) return;

        $posted_data = $submission->get_posted_data();
        $form_id     = $contact_form->id();
        $form_title  = $contact_form->title();

        $clean_data = [];

        foreach ( $posted_data as $key => $value ) {
            if ( strpos( $key, '_wpcf7' ) === false && strpos( $key, '_wpnonce' ) === false ) {
                if ( is_array( $value ) ) {
                    $clean_data[ $key ] = implode( ', ', array_map( 'sanitize_text_field', wp_unslash( $value ) ) );
                } else {
                    $clean_data[ $key ] = sanitize_textarea_field( wp_unslash( $value ) );
                }
            }
        }

        $table_name = $wpdb->prefix . 'codidot_cf7_leads';
        $wpdb->insert( 
            $table_name, 
            [
                'form_id'    => intval( $form_id ),
                'form_title' => sanitize_text_field( $form_title ),
                'lead_data'  => wp_json_encode( $clean_data )
            ],
            [ '%d', '%s', '%s' ]
        );
    }
}
