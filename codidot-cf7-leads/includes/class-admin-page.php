<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Codidot_CF7_Admin {
    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'register_admin_menu' ] );
        add_action( 'admin_post_export_cf7_leads', [ __CLASS__, 'handle_csv_export' ] );
    }

    public static function register_admin_menu() {
        add_menu_page( 'CF7 Leads', 'Macchu Manager', 'manage_options', 'codi-cf7-leads', [ __CLASS__, 'render_admin_page' ], 'dashicons-groups', 31 );
        add_submenu_page( 'codi-cf7-leads', 'CF7 Leads', 'CF7 Leads', 'manage_options', 'codi-cf7-leads', [ __CLASS__, 'render_admin_page' ] );
        add_submenu_page( 'codi-cf7-leads', 'Macchu Users', 'Macchu Users', 'manage_options', 'macchu-users', [ __CLASS__, 'render_user_management_page' ] );
    }

    public static function render_admin_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'codidot_cf7_leads';
        $leads = $wpdb->get_results( "SELECT * FROM `{$table_name}` ORDER BY date_submitted DESC LIMIT 100" );
        
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e( 'Contact Form 7 Leads (Codidot)', 'codidot-cf7-leads' ); ?></h1>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block; margin-bottom: 20px;">
                <input type="hidden" name="action" value="export_cf7_leads">
                <?php wp_nonce_field( 'codi_export_leads_nonce', 'export_nonce' ); ?>
                <button type="submit" class="button button-primary">Export All Leads to CSV</button>
            </form>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr><th style="width: 50px;">ID</th><th style="width: 200px;">Form Title</th><th>Lead Details</th><th style="width: 180px;">Date Submitted</th></tr>
                </thead>
                <tbody>
                    <?php if ( $leads ) : foreach ( $leads as $lead ) : $lead_data = json_decode( $lead->lead_data, true ); ?>
                    <tr>
                        <td><?php echo esc_html( $lead->id ); ?></td>
                        <td><strong><?php echo esc_html( $lead->form_title ); ?></strong></td>
                        <td>
                            <?php 
                            if ( is_array( $lead_data ) ) {
                                foreach ( $lead_data as $key => $val ) {
                                    echo '<strong>' . esc_html( ucfirst( str_replace( '-', ' ', $key ) ) ) . ':</strong> ' . esc_html( $val ) . '<br>';
                                }
                            }
                            ?>
                        </td>
                        <td><?php echo esc_html( date( 'd M Y, h:i A', strtotime( $lead->date_submitted ) ) ); ?></td>
                    </tr>
                    <?php endforeach; else : ?>
                    <tr><td colspan="4">No leads found yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public static function render_user_management_page() {
        $users = get_users( [ 'role__not_in' => [ 'administrator' ] ] );
        ?>
        <div class="wrap">
            <h1>Macchu World Wide Users</h1>
            <p>Manage approval status for non-admin users. Unapproved users will not be able to log in.</p>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr><th>Name</th><th>Email</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach( $users as $user ) : 
                        $status = get_user_meta( $user->ID, 'macchu_status', true );
                        if ( empty( $status ) ) $status = 'unapproved';
                        $color = ( $status == 'approved' ) ? 'green' : 'orange';
                    ?>
                    <tr>
                        <td><?php echo esc_html( $user->display_name ); ?></td>
                        <td><?php echo esc_html( $user->user_email ); ?></td>
                        <td><span style="color:<?php echo $color; ?>; font-weight:bold;"><?php echo esc_html( strtoupper( $status ) ); ?></span></td>
                        <td>
                            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;">
                                <input type="hidden" name="action" value="macchu_user_action">
                                <input type="hidden" name="user_id" value="<?php echo $user->ID; ?>">
                                <?php wp_nonce_field( 'macchu_user_nonce' ); ?>
                                <?php if( $status !== 'approved' ) : ?>
                                    <input type="hidden" name="new_status" value="approved">
                                    <button type="submit" class="button button-primary">Approve</button>
                                <?php else : ?>
                                    <input type="hidden" name="new_status" value="unapproved">
                                    <button type="submit" class="button">Unapprove</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public static function handle_csv_export() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        if ( ! isset( $_POST['export_nonce'] ) || ! wp_verify_nonce( $_POST['export_nonce'], 'codi_export_leads_nonce' ) ) wp_die( 'Security check failed' );

        global $wpdb;
        $table_name = $wpdb->prefix . 'codidot_cf7_leads';
        $leads = $wpdb->get_results( "SELECT * FROM `{$table_name}` ORDER BY date_submitted DESC" );

        if ( ! $leads ) wp_die( 'No data to export.' );

        $all_keys = [ 'Lead ID', 'Form Title', 'Date Submitted' ];
        $parsed_leads = [];

        foreach ( $leads as $lead ) {
            $lead_data = json_decode( $lead->lead_data, true );
            $row = [ 'Lead ID' => $lead->id, 'Form Title' => $lead->form_title, 'Date Submitted' => $lead->date_submitted ];
            if ( is_array( $lead_data ) ) {
                foreach ( $lead_data as $key => $val ) {
                    $clean_key = ucfirst( str_replace( '-', ' ', $key ) );
                    if ( ! in_array( $clean_key, $all_keys ) ) $all_keys[] = $clean_key;
                    $row[ $clean_key ] = $val;
                }
            }
            $parsed_leads[] = $row;
        }

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=CF7_Leads_Codidot_' . date( 'Y-m-d' ) . '.csv' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        $output = fopen( 'php://output', 'w' );
        fputcsv( $output, $all_keys );
        foreach ( $parsed_leads as $lead_row ) {
            $csv_row = [];
            foreach ( $all_keys as $key ) $csv_row[] = isset( $lead_row[ $key ] ) ? $lead_row[ $key ] : ''; 
            fputcsv( $output, $csv_row );
        }
        fclose( $output );
        exit;
    }
}
