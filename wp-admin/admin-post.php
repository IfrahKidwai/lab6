<?php
 if ( ! defined( 'WP_ADMIN' ) ) { define( 'WP_ADMIN', true ); } if ( defined( 'ABSPATH' ) ) { require_once ABSPATH . 'wp-load.php'; } else { require_once dirname( __DIR__ ) . '/wp-load.php'; } send_origin_headers(); require_once ABSPATH . 'wp-admin/includes/admin.php'; nocache_headers(); do_action( 'admin_init' ); $action = ! empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : ''; if ( ! is_scalar( $action ) ) { wp_die( '', 400 ); } if ( ! is_user_logged_in() ) { if ( empty( $action ) ) { do_action( 'admin_post_nopriv' ); } else { if ( ! has_action( "admin_post_nopriv_{$action}" ) ) { wp_die( '', 400 ); } do_action( "admin_post_nopriv_{$action}" ); } } else { if ( empty( $action ) ) { do_action( 'admin_post' ); } else { if ( ! has_action( "admin_post_{$action}" ) ) { wp_die( '', 400 ); } do_action( "admin_post_{$action}" ); } } 