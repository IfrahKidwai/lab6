<?php
 class Theme_Upgrader extends WP_Upgrader { public $result; public $bulk = false; public $new_theme_data = array(); public function upgrade_strings() { $this->strings['up_to_date'] = __( 'The theme is at the latest version.' ); $this->strings['no_package'] = __( 'Update package not available.' ); $this->strings['downloading_package'] = sprintf( __( 'Downloading update from %s&#8230;' ), '<span class="code pre">%s</span>' ); $this->strings['unpack_package'] = __( 'Unpacking the update&#8230;' ); $this->strings['remove_old'] = __( 'Removing the old version of the theme&#8230;' ); $this->strings['remove_old_failed'] = __( 'Could not remove the old theme.' ); $this->strings['process_failed'] = __( 'Theme update failed.' ); $this->strings['process_success'] = __( 'Theme updated successfully.' ); } public function install_strings() { $this->strings['no_package'] = __( 'Installation package not available.' ); $this->strings['downloading_package'] = sprintf( __( 'Downloading installation package from %s&#8230;' ), '<span class="code pre">%s</span>' ); $this->strings['unpack_package'] = __( 'Unpacking the package&#8230;' ); $this->strings['installing_package'] = __( 'Installing the theme&#8230;' ); $this->strings['remove_old'] = __( 'Removing the old version of the theme&#8230;' ); $this->strings['remove_old_failed'] = __( 'Could not remove the old theme.' ); $this->strings['no_files'] = __( 'The theme contains no files.' ); $this->strings['process_failed'] = __( 'Theme installation failed.' ); $this->strings['process_success'] = __( 'Theme installed successfully.' ); $this->strings['process_success_specific'] = __( 'Successfully installed the theme <strong>%1$s %2$s</strong>.' ); $this->strings['parent_theme_search'] = __( 'This theme requires a parent theme. Checking if it is installed&#8230;' ); $this->strings['parent_theme_prepare_install'] = __( 'Preparing to install <strong>%1$s %2$s</strong>&#8230;' ); $this->strings['parent_theme_currently_installed'] = __( 'The parent theme, <strong>%1$s %2$s</strong>, is currently installed.' ); $this->strings['parent_theme_install_success'] = __( 'Successfully installed the parent theme, <strong>%1$s %2$s</strong>.' ); $this->strings['parent_theme_not_found'] = sprintf( __( '<strong>The parent theme could not be found.</strong> You will need to install the parent theme, %s, before you can use this child theme.' ), '<strong>%s</strong>' ); $this->strings['current_theme_has_errors'] = __( 'The active theme has the following error: "%s".' ); if ( ! empty( $this->skin->overwrite ) ) { if ( 'update-theme' === $this->skin->overwrite ) { $this->strings['installing_package'] = __( 'Updating the theme&#8230;' ); $this->strings['process_failed'] = __( 'Theme update failed.' ); $this->strings['process_success'] = __( 'Theme updated successfully.' ); } if ( 'downgrade-theme' === $this->skin->overwrite ) { $this->strings['installing_package'] = __( 'Downgrading the theme&#8230;' ); $this->strings['process_failed'] = __( 'Theme downgrade failed.' ); $this->strings['process_success'] = __( 'Theme downgraded successfully.' ); } } } public function check_parent_theme_filter( $install_result, $hook_extra, $child_result ) { $theme_info = $this->theme_info(); if ( ! $theme_info->parent() ) { return $install_result; } $this->skin->feedback( 'parent_theme_search' ); if ( ! $theme_info->parent()->errors() ) { $this->skin->feedback( 'parent_theme_currently_installed', $theme_info->parent()->display( 'Name' ), $theme_info->parent()->display( 'Version' ) ); return $install_result; } $api = themes_api( 'theme_information', array( 'slug' => $theme_info->get( 'Template' ), 'fields' => array( 'sections' => false, 'tags' => false, ), ) ); if ( ! $api || is_wp_error( $api ) ) { $this->skin->feedback( 'parent_theme_not_found', $theme_info->get( 'Template' ) ); add_filter( 'install_theme_complete_actions', array( $this, 'hide_activate_preview_actions' ) ); return $install_result; } $child_api = $this->skin->api; $child_success_message = $this->strings['process_success']; $this->skin->api = $api; $this->strings['process_success_specific'] = $this->strings['parent_theme_install_success']; $this->skin->feedback( 'parent_theme_prepare_install', $api->name, $api->version ); add_filter( 'install_theme_complete_actions', '__return_false', 999 ); $parent_result = $this->run( array( 'package' => $api->download_link, 'destination' => get_theme_root(), 'clear_destination' => false, 'clear_working' => true, ) ); if ( is_wp_error( $parent_result ) ) { add_filter( 'install_theme_complete_actions', array( $this, 'hide_activate_preview_actions' ) ); } remove_filter( 'install_theme_complete_actions', '__return_false', 999 ); $this->result = $child_result; $this->skin->api = $child_api; $this->strings['process_success'] = $child_success_message; return $install_result; } public function hide_activate_preview_actions( $actions ) { unset( $actions['activate'], $actions['preview'] ); return $actions; } public function install( $package, $args = array() ) { $defaults = array( 'clear_update_cache' => true, 'overwrite_package' => false, ); $parsed_args = wp_parse_args( $args, $defaults ); $this->init(); $this->install_strings(); add_filter( 'upgrader_source_selection', array( $this, 'check_package' ) ); add_filter( 'upgrader_post_install', array( $this, 'check_parent_theme_filter' ), 10, 3 ); if ( $parsed_args['clear_update_cache'] ) { add_action( 'upgrader_process_complete', 'wp_clean_themes_cache', 9, 0 ); } $this->run( array( 'package' => $package, 'destination' => get_theme_root(), 'clear_destination' => $parsed_args['overwrite_package'], 'clear_working' => true, 'hook_extra' => array( 'type' => 'theme', 'action' => 'install', ), ) ); remove_action( 'upgrader_process_complete', 'wp_clean_themes_cache', 9 ); remove_filter( 'upgrader_source_selection', array( $this, 'check_package' ) ); remove_filter( 'upgrader_post_install', array( $this, 'check_parent_theme_filter' ) ); if ( ! $this->result || is_wp_error( $this->result ) ) { return $this->result; } wp_clean_themes_cache( $parsed_args['clear_update_cache'] ); if ( $parsed_args['overwrite_package'] ) { do_action( 'upgrader_overwrote_package', $package, $this->new_theme_data, 'theme' ); } return true; } public function upgrade( $theme, $args = array() ) { $defaults = array( 'clear_update_cache' => true, ); $parsed_args = wp_parse_args( $args, $defaults ); $this->init(); $this->upgrade_strings(); $current = get_site_transient( 'update_themes' ); if ( ! isset( $current->response[ $theme ] ) ) { $this->skin->before(); $this->skin->set_result( false ); $this->skin->error( 'up_to_date' ); $this->skin->after(); return false; } $r = $current->response[ $theme ]; add_filter( 'upgrader_pre_install', array( $this, 'current_before' ), 10, 2 ); add_filter( 'upgrader_post_install', array( $this, 'current_after' ), 10, 2 ); add_filter( 'upgrader_clear_destination', array( $this, 'delete_old_theme' ), 10, 4 ); if ( $parsed_args['clear_update_cache'] ) { add_action( 'upgrader_process_complete', 'wp_clean_themes_cache', 9, 0 ); } $this->run( array( 'package' => $r['package'], 'destination' => get_theme_root( $theme ), 'clear_destination' => true, 'clear_working' => true, 'hook_extra' => array( 'theme' => $theme, 'type' => 'theme', 'action' => 'update', 'temp_backup' => array( 'slug' => $theme, 'src' => get_theme_root( $theme ), 'dir' => 'themes', ), ), ) ); remove_action( 'upgrader_process_complete', 'wp_clean_themes_cache', 9 ); remove_filter( 'upgrader_pre_install', array( $this, 'current_before' ) ); remove_filter( 'upgrader_post_install', array( $this, 'current_after' ) ); remove_filter( 'upgrader_clear_destination', array( $this, 'delete_old_theme' ) ); if ( ! $this->result || is_wp_error( $this->result ) ) { return $this->result; } wp_clean_themes_cache( $parsed_args['clear_update_cache'] ); $past_failure_emails = get_option( 'auto_plugin_theme_update_emails', array() ); if ( isset( $past_failure_emails[ $theme ] ) ) { unset( $past_failure_emails[ $theme ] ); update_option( 'auto_plugin_theme_update_emails', $past_failure_emails ); } return true; } public function bulk_upgrade( $themes, $args = array() ) { $defaults = array( 'clear_update_cache' => true, ); $parsed_args = wp_parse_args( $args, $defaults ); $this->init(); $this->bulk = true; $this->upgrade_strings(); $current = get_site_transient( 'update_themes' ); add_filter( 'upgrader_pre_install', array( $this, 'current_before' ), 10, 2 ); add_filter( 'upgrader_post_install', array( $this, 'current_after' ), 10, 2 ); add_filter( 'upgrader_clear_destination', array( $this, 'delete_old_theme' ), 10, 4 ); $this->skin->header(); $res = $this->fs_connect( array( WP_CONTENT_DIR ) ); if ( ! $res ) { $this->skin->footer(); return false; } $this->skin->bulk_header(); $maintenance = ( is_multisite() && ! empty( $themes ) ); foreach ( $themes as $theme ) { $maintenance = $maintenance || get_stylesheet() === $theme || get_template() === $theme; } if ( $maintenance ) { $this->maintenance_mode( true ); } $results = array(); $this->update_count = count( $themes ); $this->update_current = 0; foreach ( $themes as $theme ) { ++$this->update_current; $this->skin->theme_info = $this->theme_info( $theme ); if ( ! isset( $current->response[ $theme ] ) ) { $this->skin->set_result( true ); $this->skin->before(); $this->skin->feedback( 'up_to_date' ); $this->skin->after(); $results[ $theme ] = true; continue; } $r = $current->response[ $theme ]; $result = $this->run( array( 'package' => $r['package'], 'destination' => get_theme_root( $theme ), 'clear_destination' => true, 'clear_working' => true, 'is_multi' => true, 'hook_extra' => array( 'theme' => $theme, 'temp_backup' => array( 'slug' => $theme, 'src' => get_theme_root( $theme ), 'dir' => 'themes', ), ), ) ); $results[ $theme ] = $result; if ( false === $result ) { break; } } $this->maintenance_mode( false ); wp_clean_themes_cache( $parsed_args['clear_update_cache'] ); do_action( 'upgrader_process_complete', $this, array( 'action' => 'update', 'type' => 'theme', 'bulk' => true, 'themes' => $themes, ) ); $this->skin->bulk_footer(); $this->skin->footer(); remove_filter( 'upgrader_pre_install', array( $this, 'current_before' ) ); remove_filter( 'upgrader_post_install', array( $this, 'current_after' ) ); remove_filter( 'upgrader_clear_destination', array( $this, 'delete_old_theme' ) ); $past_failure_emails = get_option( 'auto_plugin_theme_update_emails', array() ); foreach ( $results as $theme => $result ) { if ( ! $result || is_wp_error( $result ) || ! isset( $past_failure_emails[ $theme ] ) ) { continue; } unset( $past_failure_emails[ $theme ] ); } update_option( 'auto_plugin_theme_update_emails', $past_failure_emails ); return $results; } public function check_package( $source ) { global $wp_filesystem, $wp_version; $this->new_theme_data = array(); if ( is_wp_error( $source ) ) { return $source; } $working_directory = str_replace( $wp_filesystem->wp_content_dir(), trailingslashit( WP_CONTENT_DIR ), $source ); if ( ! is_dir( $working_directory ) ) { return $source; } if ( ! file_exists( $working_directory . 'style.css' ) ) { return new WP_Error( 'incompatible_archive_theme_no_style', $this->strings['incompatible_archive'], sprintf( __( 'The theme is missing the %s stylesheet.' ), '<code>style.css</code>' ) ); } $info = get_file_data( $working_directory . 'style.css', array( 'Name' => 'Theme Name', 'Version' => 'Version', 'Author' => 'Author', 'Template' => 'Template', 'RequiresWP' => 'Requires at least', 'RequiresPHP' => 'Requires PHP', ) ); if ( empty( $info['Name'] ) ) { return new WP_Error( 'incompatible_archive_theme_no_name', $this->strings['incompatible_archive'], sprintf( __( 'The %s stylesheet does not contain a valid theme header.' ), '<code>style.css</code>' ) ); } if ( empty( $info['Template'] ) && ! file_exists( $working_directory . 'index.php' ) && ! file_exists( $working_directory . 'templates/index.html' ) && ! file_exists( $working_directory . 'block-templates/index.html' ) ) { return new WP_Error( 'incompatible_archive_theme_no_index', $this->strings['incompatible_archive'], sprintf( __( 'Template is missing. Standalone themes need to have a %1$s or %2$s template file. <a href="%3$s">Child themes</a> need to have a %4$s header in the %5$s stylesheet.' ), '<code>templates/index.html</code>', '<code>index.php</code>', __( 'https://developer.wordpress.org/themes/advanced-topics/child-themes/' ), '<code>Template</code>', '<code>style.css</code>' ) ); } $requires_php = isset( $info['RequiresPHP'] ) ? $info['RequiresPHP'] : null; $requires_wp = isset( $info['RequiresWP'] ) ? $info['RequiresWP'] : null; if ( ! is_php_version_compatible( $requires_php ) ) { $error = sprintf( __( 'The PHP version on your server is %1$s, however the uploaded theme requires %2$s.' ), PHP_VERSION, $requires_php ); return new WP_Error( 'incompatible_php_required_version', $this->strings['incompatible_archive'], $error ); } if ( ! is_wp_version_compatible( $requires_wp ) ) { $error = sprintf( __( 'Your WordPress version is %1$s, however the uploaded theme requires %2$s.' ), $wp_version, $requires_wp ); return new WP_Error( 'incompatible_wp_required_version', $this->strings['incompatible_archive'], $error ); } $this->new_theme_data = $info; return $source; } public function current_before( $response, $theme ) { if ( is_wp_error( $response ) ) { return $response; } $theme = isset( $theme['theme'] ) ? $theme['theme'] : ''; if ( get_stylesheet() !== $theme ) { return $response; } if ( ! $this->bulk ) { $this->maintenance_mode( true ); } return $response; } public function current_after( $response, $theme ) { if ( is_wp_error( $response ) ) { return $response; } $theme = isset( $theme['theme'] ) ? $theme['theme'] : ''; if ( get_stylesheet() !== $theme ) { return $response; } if ( get_stylesheet() === $theme && $theme !== $this->result['destination_name'] ) { wp_clean_themes_cache(); $stylesheet = $this->result['destination_name']; switch_theme( $stylesheet ); } if ( ! $this->bulk ) { $this->maintenance_mode( false ); } return $response; } public function delete_old_theme( $removed, $local_destination, $remote_destination, $theme ) { global $wp_filesystem; if ( is_wp_error( $removed ) ) { return $removed; } if ( ! isset( $theme['theme'] ) ) { return $removed; } $theme = $theme['theme']; $themes_dir = trailingslashit( $wp_filesystem->wp_themes_dir( $theme ) ); if ( $wp_filesystem->exists( $themes_dir . $theme ) ) { if ( ! $wp_filesystem->delete( $themes_dir . $theme, true ) ) { return false; } } return true; } public function theme_info( $theme = null ) { if ( empty( $theme ) ) { if ( ! empty( $this->result['destination_name'] ) ) { $theme = $this->result['destination_name']; } else { return false; } } $theme = wp_get_theme( $theme ); $theme->cache_delete(); return $theme; } } 