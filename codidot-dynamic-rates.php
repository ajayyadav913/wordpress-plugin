<?php
/**
 * Plugin Name: Codidot Dynamic Rates
 * Description: Professional Elementor Addon for International Rates Calculator with Bulk CSV Upload.
 * Version: 1.0.0
 * Author: Ajay Yadav
 * Author URI: https://codidot.com
 * Company: Codidot
 * Text Domain: codidot-dynamic-rates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'CODI_RATES_PATH', plugin_dir_path( __FILE__ ) );
define( 'CODI_RATES_URL', plugin_dir_url( __FILE__ ) );
define( 'CODI_RATES_VERSION', '1.0.0' );

// 1. Include Core Files
require_once CODI_RATES_PATH . 'includes/class-activator.php';
require_once CODI_RATES_PATH . 'includes/class-admin.php';
require_once CODI_RATES_PATH . 'includes/class-ajax.php';

// 2. Register Activation Hook
register_activation_hook( __FILE__, array( 'Codidot_Rates_Activator', 'activate' ) );

// 3. Initialize Admin & AJAX
Codidot_Rates_Admin::init();
Codidot_Rates_Ajax::init();

// 4. Enqueue Frontend Assets (Only CSS/JS, no inline scripts)
add_action( 'wp_enqueue_scripts', 'codi_rates_enqueue_assets' );
function codi_rates_enqueue_assets() {
    wp_register_style( 'codi-rates-css', CODI_RATES_URL . 'assets/css/frontend.css', array(), CODI_RATES_VERSION );
    wp_register_script( 'codi-rates-js', CODI_RATES_URL . 'assets/js/frontend.js', array('jquery'), CODI_RATES_VERSION, true );
    
    // Pass Ajax URL to our JS file
    wp_localize_script( 'codi-rates-js', 'codiRatesObj', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' )
    ));
}

// 5. Register Elementor Widget
add_action( 'elementor/widgets/register', 'codi_register_elementor_widget' );
function codi_register_elementor_widget( $widgets_manager ) {
    require_once CODI_RATES_PATH . 'elementor/class-rates-widget.php';
    $widgets_manager->register( new \Codidot_Rates_Widget() );
}