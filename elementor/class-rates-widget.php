<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
    return; 
}

class Codidot_Rates_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'codidot_rates_calculator'; }
    public function get_title() { return __( 'Codidot Rates Calculator', 'codidot-dynamic-rates' ); }
    public function get_icon() { return 'eicon-calculator'; }
    public function get_categories() { return [ 'general' ]; }

    public function get_style_depends() {
        return [ 'codi-rates-css' ];
    }

    public function get_script_depends() {
        return [ 'codi-rates-js' ];
    }

    protected function render() {
        global $wpdb;
        
        $suppress = $wpdb->suppress_errors();
        // Fixed: Interpolated variable removed
        $db_countries = $wpdb->get_col( "SELECT DISTINCT country FROM `{$wpdb->prefix}dynamic_intl_rates` ORDER BY country ASC" );
        $wpdb->suppress_errors( $suppress );
        
        if ( ! is_array( $db_countries ) ) { $db_countries = []; }
        
        $country_names = [
            'usa' => 'United States', 'uk'  => 'United Kingdom', 
            'ae'  => 'United Arab Emirates', 'australia' => 'Australia',
            'nz'  => 'New Zealand (Zone Pricing)'
        ];

        // Create a security nonce to pass to our JS for the new AJAX check
        $ajax_nonce = wp_create_nonce( 'codi_rates_ajax_nonce' );
        ?>
        
        <div class="irc-calculator-wrapper">
            <h2 class="irc-title"><?php esc_html_e( 'International Rates Calculator', 'codidot-dynamic-rates' ); ?></h2>
            <div id="irc-rate-form" data-nonce="<?php echo esc_attr( $ajax_nonce ); ?>">
                <div class="irc-grid">
                    <div class="irc-field"><label><?php esc_html_e( 'Mobile*', 'codidot-dynamic-rates' ); ?></label><input type="text" id="irc-mobile" required placeholder="<?php esc_attr_e( '*mobile', 'codidot-dynamic-rates' ); ?>"></div>
                    <div class="irc-field"><label><?php esc_html_e( 'Pickup Pincode*', 'codidot-dynamic-rates' ); ?></label><input type="text" id="irc-pickup-pincode" required placeholder="<?php esc_attr_e( '*Pickup Pincode', 'codidot-dynamic-rates' ); ?>"></div>
                    <div class="irc-field">
                        <label><?php esc_html_e( 'Destination Country*', 'codidot-dynamic-rates' ); ?></label>
                        <select id="irc-destination-country" required>
                            <option value=""><?php esc_html_e( 'Select Country', 'codidot-dynamic-rates' ); ?></option>
                            <?php foreach ( $db_countries as $code ) : 
                                $display = isset( $country_names[$code] ) ? $country_names[$code] : strtoupper( $code );
                            ?>
                                <option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $display ); ?></option>
                            <?php endforeach; ?>
                            <?php if ( ! in_array( 'nz', $db_countries ) ) echo '<option value="nz">' . esc_html__( 'New Zealand (Zone Pricing)', 'codidot-dynamic-rates' ) . '</option>'; ?>
                        </select>
                    </div>
                    <div class="irc-field"><label><?php esc_html_e( 'Drop State / County', 'codidot-dynamic-rates' ); ?></label><select id="irc-drop-state" disabled><option value=""><?php esc_html_e( 'Select State/County', 'codidot-dynamic-rates' ); ?></option></select></div>
                    <div class="irc-field"><label><?php esc_html_e( 'Drop Pincode', 'codidot-dynamic-rates' ); ?></label><input type="text" id="irc-drop-pincode" placeholder="<?php esc_attr_e( 'Drop Pincode', 'codidot-dynamic-rates' ); ?>"></div>
                    <div class="irc-field"><label><?php esc_html_e( 'Doc Type', 'codidot-dynamic-rates' ); ?></label><select id="irc-doc-type"><option value="Non Doc"><?php esc_html_e( 'Non Doc', 'codidot-dynamic-rates' ); ?></option><option value="Doc"><?php esc_html_e( 'Doc', 'codidot-dynamic-rates' ); ?></option></select></div>
                    <div class="irc-field"><label><?php esc_html_e( 'Weight (Kg)*', 'codidot-dynamic-rates' ); ?></label><input type="number" step="0.1" id="irc-weight" required value="0.5"></div>
                    <div class="irc-field"><label><?php esc_html_e( 'Length (CM)', 'codidot-dynamic-rates' ); ?></label><input type="number" id="irc-length" value="1"></div>
                    <div class="irc-field"><label><?php esc_html_e( 'Width (CM)', 'codidot-dynamic-rates' ); ?></label><input type="number" id="irc-width" value="1"></div>
                    <div class="irc-field"><label><?php esc_html_e( 'Height (CM)', 'codidot-dynamic-rates' ); ?></label><input type="number" id="irc-height" value="1"></div>
                    
                    <div class="irc-field irc-radios">
                        <label><input type="radio" name="pickup-type" value="door" checked> <?php esc_html_e( 'Door Pickup', 'codidot-dynamic-rates' ); ?></label>
                        <label><input type="radio" name="pickup-type" value="delhi"> <?php esc_html_e( 'Delhi Pickup', 'codidot-dynamic-rates' ); ?></label>
                        <label><input type="radio" name="csb-type" value="csb4"> <?php esc_html_e( 'CSB 4', 'codidot-dynamic-rates' ); ?></label>
                        <label><input type="radio" name="csb-type" value="csb5"> <?php esc_html_e( 'CSB 5', 'codidot-dynamic-rates' ); ?></label>
                    </div>
                </div>
                <button type="button" id="irc-show-rate-card" class="irc-submit-btn"><?php esc_html_e( 'Show Rate Card', 'codidot-dynamic-rates' ); ?></button>
                <div id="irc-price-result" class="irc-result"><?php esc_html_e( 'Estimated Rate: --', 'codidot-dynamic-rates' ); ?></div>
            </div>
        </div>
        <?php
    }
}