<?php
/**
 * Plugin Name: Codidot CF7 & Macchu Manager
 * Description: Saves CF7 Leads and provides Admin Approval for user logins.
 * Version: 1.1.0
 * Author: Ajay Yadav
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'CODI_MACCHU_PATH', plugin_dir_path( __FILE__ ) );

require_once CODI_MACCHU_PATH . 'includes/class-activator.php';
require_once CODI_MACCHU_PATH . 'includes/class-cf7-handler.php';
require_once CODI_MACCHU_PATH . 'includes/class-admin-page.php';
require_once CODI_MACCHU_PATH . 'includes/class-macchu-users.php';

class Codidot_Macchu_Plugin {
    public static function init() {
        register_activation_hook( __FILE__, [ 'Codidot_CF7_Activator', 'activate' ] );
        
        Codidot_CF7_Handler::init();
        Codidot_CF7_Admin::init();
        Codidot_Macchu_Users::init();

        // --- LOGIN BLOCKER LOGIC ---
        add_filter( 'wp_authenticate_user', [ __CLASS__, 'block_unapproved_users' ], 10, 1 );
    }

    public static function block_unapproved_users( $user ) {
        if ( is_wp_error( $user ) ) return $user;

        // Check if user has a status
        $status = get_user_meta( $user->ID, 'macchu_status', true );
        
        // If user has a status (meaning they are part of this system) and it's not 'approved', block login
        if ( $status && 'approved' !== $status ) {
            return new WP_Error( 'denied', '<strong>ERROR</strong>: Your account is pending approval from Macchu Worldwide.' );
        }
        return $user;
    }
}
Codidot_Macchu_Plugin::init();
