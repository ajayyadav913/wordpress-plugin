<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Codidot_Rates_Admin {
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
    }

    public static function add_admin_menu() {
        add_menu_page( 'Codidot Rates', 'Manage Rates', 'manage_options', 'codi-manage-rates', array( __CLASS__, 'admin_page_html' ), 'dashicons-database', 30 );
    }

    public static function admin_page_html() {
        global $wpdb;
        $message = '';

        // Initialize WP_Filesystem securely
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        WP_Filesystem();
        global $wp_filesystem;

        // --- Handle CSV Import (WITH SECURITY NONCE & WP_FILESYSTEM) ---
        if ( isset( $_POST['import_csv'] ) && isset( $_FILES['csv_file'] ) && current_user_can( 'manage_options' ) ) {
            check_admin_referer( 'codi_rates_csv_action', 'codi_rates_nonce' );

            if ( isset( $_POST['replace_all'] ) ) {
                // Fixed: No interpolated variable, used direct prefix
                $wpdb->query( "TRUNCATE TABLE `{$wpdb->prefix}dynamic_intl_rates`" );
            }
            
            // Fixed: Input validation & sanitization
            $file = isset( $_FILES['csv_file']['tmp_name'] ) ? sanitize_text_field( wp_unslash( $_FILES['csv_file']['tmp_name'] ) ) : '';
            
            if ( ! empty( $file ) && $wp_filesystem->exists( $file ) ) {
                $file_contents = $wp_filesystem->get_contents( $file );
                $lines = explode( "\n", $file_contents );
                
                $row = 0;
                foreach ( $lines as $line ) {
                    $row++;
                    if ( $row == 1 || empty( trim( $line ) ) ) continue; 
                    
                    $data = str_getcsv( $line );
                    if ( count( $data ) >= 4 ) {
                        $wpdb->insert( "{$wpdb->prefix}dynamic_intl_rates", [
                            'country'      => strtolower( trim( sanitize_text_field( $data[0] ) ) ),
                            'start_weight' => floatval( $data[1] ),
                            'end_weight'   => floatval( $data[2] ),
                            'price'        => floatval( $data[3] )
                        ]);
                    }
                }
                $message = '<div class="notice notice-success"><p>' . esc_html__( 'Bulk Rates Imported Successfully!', 'codidot-dynamic-rates' ) . '</p></div>';
            }
        }

        // --- Handle Manual Add (WITH SECURITY NONCE & INPUT CHECKS) ---
        if ( isset( $_POST['add_manual_rate'] ) && current_user_can( 'manage_options' ) ) {
            check_admin_referer( 'codi_rates_manual_action', 'codi_rates_manual_nonce' );
            
            $m_country = isset( $_POST['m_country'] ) ? sanitize_text_field( wp_unslash( $_POST['m_country'] ) ) : '';
            $m_start   = isset( $_POST['m_start'] ) ? floatval( wp_unslash( $_POST['m_start'] ) ) : 0;
            $m_end     = isset( $_POST['m_end'] ) ? floatval( wp_unslash( $_POST['m_end'] ) ) : 0;
            $m_price   = isset( $_POST['m_price'] ) ? floatval( wp_unslash( $_POST['m_price'] ) ) : 0;

            if ( ! empty( $m_country ) ) {
                $wpdb->insert( "{$wpdb->prefix}dynamic_intl_rates", [
                    'country'      => strtolower( $m_country ),
                    'start_weight' => $m_start,
                    'end_weight'   => $m_end,
                    'price'        => $m_price
                ]);
                $message = '<div class="notice notice-success"><p>' . esc_html__( 'Rate Added Successfully!', 'codidot-dynamic-rates' ) . '</p></div>';
            }
        }

        // --- Handle Delete (WITH SECURITY NONCE) ---
        if ( isset( $_GET['delete_id'] ) && isset( $_GET['_wpnonce'] ) && current_user_can( 'manage_options' ) ) {
            $delete_id = intval( wp_unslash( $_GET['delete_id'] ) );
            if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'codi_delete_rate_' . $delete_id ) ) {
                $wpdb->delete( "{$wpdb->prefix}dynamic_intl_rates", [ 'id' => $delete_id ] );
                $message = '<div class="notice notice-warning"><p>' . esc_html__( 'Rate Deleted Successfully!', 'codidot-dynamic-rates' ) . '</p></div>';
            }
        }

        // Fixed: Interpolated variable removed
        $rates = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}dynamic_intl_rates` ORDER BY country ASC, start_weight ASC LIMIT 500" );
        
        ?>
        <div class="wrap">
            <h2><?php esc_html_e( 'Codidot International Rates Management', 'codidot-dynamic-rates' ); ?></h2>
            <?php echo wp_kses_post( $message ); // Fixed Output Escaping ?>

            <div style="display:flex; gap: 20px; margin-top:20px; flex-wrap: wrap;">
                <div class="card" style="padding: 20px; max-width: 400px; margin:0;">
                    <h3><?php esc_html_e( '1. Bulk Upload (CSV)', 'codidot-dynamic-rates' ); ?></h3>
                    <form method="post" enctype="multipart/form-data">
                        <?php wp_nonce_field( 'codi_rates_csv_action', 'codi_rates_nonce' ); ?>
                        <input type="file" name="csv_file" accept=".csv" required /><br><br>
                        <label><input type="checkbox" name="replace_all" value="1" checked> <?php esc_html_e( 'Delete old rates before import', 'codidot-dynamic-rates' ); ?></label><br><br>
                        <input type="submit" name="import_csv" class="button button-primary" value="<?php esc_attr_e( 'Upload CSV', 'codidot-dynamic-rates' ); ?>" />
                    </form>
                </div>

                <div class="card" style="padding: 20px; max-width: 500px; margin:0;">
                    <h3><?php esc_html_e( '2. Add Rate Manually', 'codidot-dynamic-rates' ); ?></h3>
                    <form method="post" style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                        <?php wp_nonce_field( 'codi_rates_manual_action', 'codi_rates_manual_nonce' ); ?>
                        <div><label><?php esc_html_e( 'Country Code', 'codidot-dynamic-rates' ); ?></label><br><input type="text" name="m_country" required style="width:100%;"></div>
                        <div><label><?php esc_html_e( 'Price (₹)', 'codidot-dynamic-rates' ); ?></label><br><input type="number" step="0.01" name="m_price" required style="width:100%;"></div>
                        <div><label><?php esc_html_e( 'Start Weight (Kg)', 'codidot-dynamic-rates' ); ?></label><br><input type="number" step="0.01" name="m_start" required style="width:100%;"></div>
                        <div><label><?php esc_html_e( 'End Weight (Kg)', 'codidot-dynamic-rates' ); ?></label><br><input type="number" step="0.01" name="m_end" required style="width:100%;"></div>
                        <div style="grid-column: span 2;"><input type="submit" name="add_manual_rate" class="button button-primary" value="<?php esc_attr_e( 'Add Rate', 'codidot-dynamic-rates' ); ?>" /></div>
                    </form>
                </div>
            </div>

            <h3 style="margin-top:40px;"><?php esc_html_e( 'Current Rates Database', 'codidot-dynamic-rates' ); ?></h3>
            <table class="wp-list-table widefat fixed striped">
                <thead><tr><th>ID</th><th>Country Code</th><th>Start Wgt (Kg)</th><th>End Wgt (Kg)</th><th>Price (₹)</th><th>Action</th></tr></thead>
                <tbody>
                    <?php if ( $rates ) : foreach ( $rates as $r ) : 
                        $delete_url = wp_nonce_url( "?page=codi-manage-rates&delete_id=" . intval( $r->id ), 'codi_delete_rate_' . intval( $r->id ) );
                    ?>
                    <tr>
                        <td><?php echo esc_html( $r->id ); ?></td>
                        <td><strong style="text-transform:uppercase;"><?php echo esc_html( $r->country ); ?></strong></td>
                        <td><?php echo esc_html( $r->start_weight ); ?></td>
                        <td><?php echo esc_html( $r->end_weight ); ?></td>
                        <td>₹<?php echo esc_html( $r->price ); ?></td>
                        <td><a href="<?php echo esc_url( $delete_url ); ?>" class="button button-small" style="color:red; border-color:red;" onclick="return confirm('<?php esc_attr_e( 'Delete this rate?', 'codidot-dynamic-rates' ); ?>');">Delete</a></td>
                    </tr>
                    <?php endforeach; else : ?>
                    <tr><td colspan="6"><?php esc_html_e( 'No rates found. Please upload a CSV.', 'codidot-dynamic-rates' ); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}