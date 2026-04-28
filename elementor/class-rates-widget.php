<?php
if ( ! defined( 'ABSPATH' ) ) exit;

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
        $table_name = $wpdb->prefix . 'dynamic_intl_rates';
        $db_countries = $wpdb->get_col("SELECT DISTINCT country FROM $table_name ORDER BY country ASC");
        
        $country_names = [
            'usa' => 'United States',
            'uk'  => 'United Kingdom',
            'ae'  => 'United Arab Emirates',
            'australia' => 'Australia',
            'nz'  => 'New Zealand (Zone Pricing)'
        ];
        ?>
        <div class="irc-calculator-wrapper">
            <h2 class="irc-title">International Rates Calculator</h2>
            <div id="irc-rate-form">
                <div class="irc-grid">
                    <div class="irc-field">
                        <label>Mobile*</label>
                        <input type="text" id="irc-mobile" required placeholder="*mobile">
                    </div>
                    <div class="irc-field">
                        <label>Pickup Pincode*</label>
                        <input type="text" id="irc-pickup-pincode" required placeholder="*Pickup Pincode">
                    </div>
                    <div class="irc-field">
                        <label>Destination Country*</label>
                        <select id="irc-destination-country" required>
                            <option value="">Select Country</option>
                            <?php foreach($db_countries as $code): 
                                $display = isset($country_names[$code]) ? $country_names[$code] : strtoupper($code);
                            ?>
                                <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($display); ?></option>
                            <?php endforeach; ?>
                            <?php if(!in_array('nz', $db_countries)) echo '<option value="nz">New Zealand (Zone Pricing)</option>'; ?>
                        </select>
                    </div>
                    <div class="irc-field">
                        <label>Drop State / County</label>
                        <select id="irc-drop-state" disabled><option value="">Select State/County</option></select>
                    </div>
                    <div class="irc-field">
                        <label>Drop Pincode</label>
                        <input type="text" id="irc-drop-pincode" placeholder="Drop Pincode">
                    </div>
                    <div class="irc-field">
                        <label>Doc Type</label>
                        <select id="irc-doc-type"><option value="Non Doc">Non Doc</option><option value="Doc">Doc</option></select>
                    </div>
                    <div class="irc-field">
                        <label>Weight (Kg)*</label>
                        <input type="number" step="0.1" id="irc-weight" required value="0.5">
                    </div>
                    <div class="irc-field">
                        <label>Length (CM)</label>
                        <input type="number" id="irc-length" value="1">
                    </div>
                    <div class="irc-field">
                        <label>Width (CM)</label>
                        <input type="number" id="irc-width" value="1">
                    </div>
                    <div class="irc-field">
                        <label>Height (CM)</label>
                        <input type="number" id="irc-height" value="1">
                    </div>
                    <div class="irc-field irc-radios" style="grid-column: span 2;">
                        <label><input type="radio" name="pickup-type" value="door" checked> Door Pickup</label>
                        <label><input type="radio" name="pickup-type" value="delhi"> Delhi Pickup</label>
                        <label><input type="radio" name="csb-type" value="csb4"> CSB 4</label>
                        <label><input type="radio" name="csb-type" value="csb5"> CSB 5</label>
                    </div>
                </div>
                <button type="button" id="irc-show-rate-card" class="irc-submit-btn">Show Rate Card</button>
                <div id="irc-price-result" class="irc-result">Estimated Rate: --</div>
            </div>
        </div>
        <?php
    }
}