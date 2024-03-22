<?php
 #[AllowDynamicProperties]
 class WP_Recovery_Mode { const EXIT_ACTION = 'exit_recovery_mode'; private $cookie_service; private $key_service; private $link_service; private $email_service; private $is_initialized = false; private $is_active = false; private $session_id = ''; public function __construct() { $this->cookie_service = new WP_Recovery_Mode_Cookie_Service(); $this->key_service = new WP_Recovery_Mode_Key_Service(); $this->link_service = new WP_Recovery_Mode_Link_Service( $this->cookie_service, $this->key_service ); $this->email_service = new WP_Recovery_Mode_Email_Service( $this->link_service ); } public function initialize() { $this->is_initialized = true; add_action( 'wp_logout', array( $this, 'exit_recovery_mode' ) ); add_action( 'login_form_' . self::EXIT_ACTION, array( $this, 'handle_exit_recovery_mode' ) ); add_action( 'recovery_mode_clean_expired_keys', array( $this, 'clean_expired_keys' ) ); if ( ! wp_next_scheduled( 'recovery_mode_clean_expired_keys' ) && ! wp_installing() ) { wp_schedule_event( time(), 'daily', 'recovery_mode_clean_expired_keys' ); } if ( defined( 'WP_RECOVERY_MODE_SESSION_ID' ) ) { $this->is_active = true; $this->session_id = WP_RECOVERY_MODE_SESSION_ID; return; } if ( $this->cookie_service->is_cookie_set() ) { $this->handle_cookie(); return; } $this->link_service->handle_begin_link( $this->get_link_ttl() ); } public function is_active() { return $this->is_active; } public function get_session_id() { return $this->session_id; } public function is_initialized() { return $this->is_initialized; } public function handle_error( array $error ) { $extension = $this->get_extension_for_error( $error ); if ( ! $extension || $this->is_network_plugin( $extension ) ) { return new WP_Error( 'invalid_source', __( 'Error not caused by a plugin or theme.' ) ); } if ( ! $this->is_active() ) { if ( ! is_protected_endpoint() ) { return new WP_Error( 'non_protected_endpoint', __( 'Error occurred on a non-protected endpoint.' ) ); } if ( ! function_exists( 'wp_generate_password' ) ) { require_once ABSPATH . WPINC . '/pluggable.php'; } return $this->email_service->maybe_send_recovery_mode_email( $this->get_email_rate_limit(), $error, $extension ); } if ( ! $this->store_error( $error ) ) { return new WP_Error( 'storage_error', __( 'Failed to store the error.' ) ); } if ( headers_sent() ) { return true; } $this->redirect_protected(); } public function exit_recovery_mode() { if ( ! $this->is_active() ) { return false; } $this->email_service->clear_rate_limit(); $this->cookie_service->clear_cookie(); wp_paused_plugins()->delete_all(); wp_paused_themes()->delete_all(); return true; } public function handle_exit_recovery_mode() { $redirect_to = wp_get_referer(); if ( ! $redirect_to ) { $redirect_to = is_user_logged_in() ? admin_url() : home_url(); } if ( ! $this->is_active() ) { wp_safe_redirect( $redirect_to ); die; } if ( ! isset( $_GET['action'] ) || self::EXIT_ACTION !== $_GET['action'] ) { return; } if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], self::EXIT_ACTION ) ) { wp_die( __( 'Exit recovery mode link expired.' ), 403 ); } if ( ! $this->exit_recovery_mode() ) { wp_die( __( 'Failed to exit recovery mode. Please try again later.' ) ); } wp_safe_redirect( $redirect_to ); die; } public function clean_expired_keys() { $this->key_service->clean_expired_keys( $this->get_link_ttl() ); } protected function handle_cookie() { $validated = $this->cookie_service->validate_cookie(); if ( is_wp_error( $validated ) ) { $this->cookie_service->clear_cookie(); $validated->add_data( array( 'status' => 403 ) ); wp_die( $validated ); } $session_id = $this->cookie_service->get_session_id_from_cookie(); if ( is_wp_error( $session_id ) ) { $this->cookie_service->clear_cookie(); $session_id->add_data( array( 'status' => 403 ) ); wp_die( $session_id ); } $this->is_active = true; $this->session_id = $session_id; } protected function get_email_rate_limit() { return apply_filters( 'recovery_mode_email_rate_limit', DAY_IN_SECONDS ); } protected function get_link_ttl() { $rate_limit = $this->get_email_rate_limit(); $valid_for = $rate_limit; $valid_for = apply_filters( 'recovery_mode_email_link_ttl', $valid_for ); return max( $valid_for, $rate_limit ); } protected function get_extension_for_error( $error ) { global $wp_theme_directories; if ( ! isset( $error['file'] ) ) { return false; } if ( ! defined( 'WP_PLUGIN_DIR' ) ) { return false; } $error_file = wp_normalize_path( $error['file'] ); $wp_plugin_dir = wp_normalize_path( WP_PLUGIN_DIR ); if ( str_starts_with( $error_file, $wp_plugin_dir ) ) { $path = str_replace( $wp_plugin_dir . '/', '', $error_file ); $parts = explode( '/', $path ); return array( 'type' => 'plugin', 'slug' => $parts[0], ); } if ( empty( $wp_theme_directories ) ) { return false; } foreach ( $wp_theme_directories as $theme_directory ) { $theme_directory = wp_normalize_path( $theme_directory ); if ( str_starts_with( $error_file, $theme_directory ) ) { $path = str_replace( $theme_directory . '/', '', $error_file ); $parts = explode( '/', $path ); return array( 'type' => 'theme', 'slug' => $parts[0], ); } } return false; } protected function is_network_plugin( $extension ) { if ( 'plugin' !== $extension['type'] ) { return false; } if ( ! is_multisite() ) { return false; } $network_plugins = wp_get_active_network_plugins(); foreach ( $network_plugins as $plugin ) { if ( str_starts_with( $plugin, $extension['slug'] . '/' ) ) { return true; } } return false; } protected function store_error( $error ) { $extension = $this->get_extension_for_error( $error ); if ( ! $extension ) { return false; } switch ( $extension['type'] ) { case 'plugin': return wp_paused_plugins()->set( $extension['slug'], $error ); case 'theme': return wp_paused_themes()->set( $extension['slug'], $error ); default: return false; } } protected function redirect_protected() { if ( ! function_exists( 'wp_safe_redirect' ) ) { require_once ABSPATH . WPINC . '/pluggable.php'; } $scheme = is_ssl() ? 'https://' : 'http://'; $url = "{$scheme}{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"; wp_safe_redirect( $url ); exit; } } 