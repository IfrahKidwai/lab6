<?php
 #[AllowDynamicProperties]
 class File_Upload_Upgrader { public $package; public $filename; public $id = 0; public function __construct( $form, $urlholder ) { if ( empty( $_FILES[ $form ]['name'] ) && empty( $_GET[ $urlholder ] ) ) { wp_die( __( 'Please select a file' ) ); } if ( ! empty( $_FILES ) ) { $overrides = array( 'test_form' => false, 'test_type' => false, ); $file = wp_handle_upload( $_FILES[ $form ], $overrides ); if ( isset( $file['error'] ) ) { wp_die( $file['error'] ); } if ( 'pluginzip' === $form || 'themezip' === $form ) { $archive_is_valid = false; if ( class_exists( 'ZipArchive', false ) && apply_filters( 'unzip_file_use_ziparchive', true ) ) { $archive = new ZipArchive(); $archive_is_valid = $archive->open( $file['file'], ZIPARCHIVE::CHECKCONS ); if ( true === $archive_is_valid ) { $archive->close(); } } else { require_once ABSPATH . 'wp-admin/includes/class-pclzip.php'; $archive = new PclZip( $file['file'] ); $archive_is_valid = is_array( $archive->properties() ); } if ( true !== $archive_is_valid ) { wp_delete_file( $file['file'] ); wp_die( __( 'Incompatible Archive.' ) ); } } $this->filename = $_FILES[ $form ]['name']; $this->package = $file['file']; $attachment = array( 'post_title' => $this->filename, 'post_content' => $file['url'], 'post_mime_type' => $file['type'], 'guid' => $file['url'], 'context' => 'upgrader', 'post_status' => 'private', ); $this->id = wp_insert_attachment( $attachment, $file['file'] ); wp_schedule_single_event( time() + 2 * HOUR_IN_SECONDS, 'upgrader_scheduled_cleanup', array( $this->id ) ); } elseif ( is_numeric( $_GET[ $urlholder ] ) ) { $this->id = (int) $_GET[ $urlholder ]; $attachment = get_post( $this->id ); if ( empty( $attachment ) ) { wp_die( __( 'Please select a file' ) ); } $this->filename = $attachment->post_title; $this->package = get_attached_file( $attachment->ID ); } else { $uploads = wp_upload_dir(); if ( ! ( $uploads && false === $uploads['error'] ) ) { wp_die( $uploads['error'] ); } $this->filename = sanitize_file_name( $_GET[ $urlholder ] ); $this->package = $uploads['basedir'] . '/' . $this->filename; if ( ! str_starts_with( realpath( $this->package ), realpath( $uploads['basedir'] ) ) ) { wp_die( __( 'Please select a file' ) ); } } } public function cleanup() { if ( $this->id ) { wp_delete_attachment( $this->id ); } elseif ( file_exists( $this->package ) ) { return @unlink( $this->package ); } return true; } } 