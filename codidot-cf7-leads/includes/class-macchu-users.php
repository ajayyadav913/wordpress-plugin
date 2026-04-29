<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Codidot_Macchu_Users {
    public static function init() {
        add_action( 'admin_post_macchu_user_action', [ __CLASS__, 'handle_approval_action' ] );
    }

    public static function handle_approval_action() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'macchu_user_nonce' );

        $user_id = intval( $_POST['user_id'] );
        $new_status = sanitize_text_field( $_POST['new_status'] );

        update_user_meta( $user_id, 'macchu_status', $new_status );
        wp_redirect( admin_url( 'admin.php?page=macchu-users&message=updated' ) );
        exit;
    }
}
