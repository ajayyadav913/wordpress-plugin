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
        $table_name = $wpdb->prefix . 'dynamic_intl_rates';
        $message = '';

        // Security Nonce Verification should ideally be added here for production

        if ( isset( $_POST['import_csv'] ) && isset( $_FILES['csv_file'] ) ) {
            if ( isset( $_POST['replace_all'] ) ) $wpdb->query( "TRUNCATE TABLE $table_name" );
            
            $file = $_FILES['csv_file']['tmp_name'];
            if ( ( $handle = fopen( $file, 'r' ) ) !== false ) {
                $row = 0;
                while ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== false ) {
                    $row++;
                    if ( $row == 1 ) continue; 
                    $wpdb->insert( $table_name, array(
                        'country'      => strtolower(trim($data[0])),
                        'start_weight' => floatval( $data[1] ),
                        'end_weight'   => floatval( $data[2] ),
                        'price'        => floatval( $data[3] )
                    ));
                }
                fclose( $handle );
                $message = '<div class="notice notice-success"><p>Rates Imported Successfully!</p></div>';
            }
        }

        if ( isset( $_POST['add_manual_rate'] ) ) {
            $wpdb->insert( $table_name, array(
                'country'      => strtolower(sanitize_text_field( $_POST['m_country'] )),
                'start_weight' => floatval( $_POST['m_start'] ),
                'end_weight'   => floatval( $_POST['m_end'] ),
                'price'        => floatval( $_POST['m_price'] )
            ));
            $message = '<div class="notice notice-success"><p>Rate Added!</p></div>';
        }

        if ( isset( $_GET['delete_id'] ) ) {
            $wpdb->delete( $table_name, array( 'id' => intval( $_GET['delete_id'] ) ) );
            $message = '<div class="notice notice-warning"><p>Rate Deleted!</p></div>';
        }

        $rates = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY country ASC, start_weight ASC LIMIT 500" );
        ?>
        <div class="wrap">
            <h2>Manage International Rates</h2>
            <?php echo $message; ?>
            <div style="display:flex; gap: 20px; margin-top:20px;">
                <div class="card" style="padding: 20px; max-width: 400px; margin:0;">
                    <h3>Bulk Upload (CSV)</h3>
                    <p>Format: Country Code, Start Weight, End Weight, Price</p>
                    <form method="post" enctype="multipart/form-data">
                        <input type="file" name="csv_file" accept=".csv" required /><br><br>
                        <label><input type="checkbox" name="replace_all" value="1" checked> Delete old rates</label><br><br>
                        <input type="submit" name="import_csv" class="button button-primary" value="Upload CSV" />
                    </form>
                </div>
                <div class="card" style="padding: 20px; max-width: 500px; margin:0;">
                    <h3>Add Rate Manually</h3>
                    <form method="post" style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                        <div><label>Country Code (e.g. usa)</label><input type="text" name="m_country" required style="width:100%;"></div>
                        <div><label>Price (₹)</label><input type="number" step="0.01" name="m_price" required style="width:100%;"></div>
                        <div><label>Start Wgt</label><input type="number" step="0.01" name="m_start" required style="width:100%;"></div>
                        <div><label>End Wgt</label><input type="number" step="0.01" name="m_end" required style="width:100%;"></div>
                        <div style="grid-column: span 2;"><input type="submit" name="add_manual_rate" class="button button-primary" value="Add Rate" /></div>
                    </form>
                </div>
            </div>
            <h3 style="margin-top:40px;">Current Rates</h3>
            <table class="wp-list-table widefat fixed striped">
                <thead><tr><th>ID</th><th>Country</th><th>Start Wgt</th><th>End Wgt</th><th>Price</th><th>Action</th></tr></thead>
                <tbody>
                    <?php if($rates): foreach($rates as $r): ?>
                    <tr>
                        <td><?php echo $r->id; ?></td>
                        <td><strong><?php echo esc_html(strtoupper($r->country)); ?></strong></td>
                        <td><?php echo $r->start_weight; ?></td><td><?php echo $r->end_weight; ?></td><td>₹<?php echo $r->price; ?></td>
                        <td><a href="?page=codi-manage-rates&delete_id=<?php echo $r->id; ?>" class="button button-small" style="color:red; border-color:red;">Delete</a></td>
                    </tr>
                    <?php endforeach; else: ?><tr><td colspan="6">No rates found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}