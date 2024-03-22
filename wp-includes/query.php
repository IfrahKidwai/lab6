<?php
 function get_query_var( $query_var, $default_value = '' ) { global $wp_query; return $wp_query->get( $query_var, $default_value ); } function get_queried_object() { global $wp_query; return $wp_query->get_queried_object(); } function get_queried_object_id() { global $wp_query; return $wp_query->get_queried_object_id(); } function set_query_var( $query_var, $value ) { global $wp_query; $wp_query->set( $query_var, $value ); } function query_posts( $query ) { $GLOBALS['wp_query'] = new WP_Query(); return $GLOBALS['wp_query']->query( $query ); } function wp_reset_query() { $GLOBALS['wp_query'] = $GLOBALS['wp_the_query']; wp_reset_postdata(); } function wp_reset_postdata() { global $wp_query; if ( isset( $wp_query ) ) { $wp_query->reset_postdata(); } } function is_archive() { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_archive(); } function is_post_type_archive( $post_types = '' ) { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_post_type_archive( $post_types ); } function is_attachment( $attachment = '' ) { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_attachment( $attachment ); } function is_author( $author = '' ) { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_author( $author ); } function is_category( $category = '' ) { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_category( $category ); } function is_tag( $tag = '' ) { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_tag( $tag ); } function is_tax( $taxonomy = '', $term = '' ) { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_tax( $taxonomy, $term ); } function is_date() { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_date(); } function is_day() { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_day(); } function is_feed( $feeds = '' ) { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_feed( $feeds ); } function is_comment_feed() { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_comment_feed(); } function is_front_page() { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_front_page(); } function is_home() { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_home(); } function is_privacy_policy() { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_privacy_policy(); } function is_month() { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_month(); } function is_page( $page = '' ) { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_page( $page ); } function is_paged() { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_paged(); } function is_preview() { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_preview(); } function is_robots() { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_robots(); } function is_favicon() { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_favicon(); } function is_search() { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_search(); } function is_single( $post = '' ) { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_single( $post ); } function is_singular( $post_types = '' ) { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_singular( $post_types ); } function is_time() { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_time(); } function is_trackback() { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_trackback(); } function is_year() { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_year(); } function is_404() { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_404(); } function is_embed() { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' ); return false; } return $wp_query->is_embed(); } function is_main_query() { global $wp_query; if ( ! isset( $wp_query ) ) { _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '6.1.0' ); return false; } if ( 'pre_get_posts' === current_filter() ) { _doing_it_wrong( __FUNCTION__, sprintf( __( 'In %1$s, use the %2$s method, not the %3$s function. See %4$s.' ), '<code>pre_get_posts</code>', '<code>WP_Query->is_main_query()</code>', '<code>is_main_query()</code>', __( 'https://developer.wordpress.org/reference/functions/is_main_query/' ) ), '3.7.0' ); } return $wp_query->is_main_query(); } function have_posts() { global $wp_query; if ( ! isset( $wp_query ) ) { return false; } return $wp_query->have_posts(); } function in_the_loop() { global $wp_query; if ( ! isset( $wp_query ) ) { return false; } return $wp_query->in_the_loop; } function rewind_posts() { global $wp_query; if ( ! isset( $wp_query ) ) { return; } $wp_query->rewind_posts(); } function the_post() { global $wp_query; if ( ! isset( $wp_query ) ) { return; } $wp_query->the_post(); } function have_comments() { global $wp_query; if ( ! isset( $wp_query ) ) { return false; } return $wp_query->have_comments(); } function the_comment() { global $wp_query; if ( ! isset( $wp_query ) ) { return; } $wp_query->the_comment(); } function wp_old_slug_redirect() { if ( is_404() && '' !== get_query_var( 'name' ) ) { if ( get_query_var( 'post_type' ) ) { $post_type = get_query_var( 'post_type' ); } elseif ( get_query_var( 'attachment' ) ) { $post_type = 'attachment'; } elseif ( get_query_var( 'pagename' ) ) { $post_type = 'page'; } else { $post_type = 'post'; } if ( is_array( $post_type ) ) { if ( count( $post_type ) > 1 ) { return; } $post_type = reset( $post_type ); } if ( is_post_type_hierarchical( $post_type ) ) { return; } $id = _find_post_by_old_slug( $post_type ); if ( ! $id ) { $id = _find_post_by_old_date( $post_type ); } $id = apply_filters( 'old_slug_redirect_post_id', $id ); if ( ! $id ) { return; } $link = get_permalink( $id ); if ( get_query_var( 'paged' ) > 1 ) { $link = user_trailingslashit( trailingslashit( $link ) . 'page/' . get_query_var( 'paged' ) ); } elseif ( is_embed() ) { $link = user_trailingslashit( trailingslashit( $link ) . 'embed' ); } $link = apply_filters( 'old_slug_redirect_url', $link ); if ( ! $link ) { return; } wp_redirect( $link, 301 ); exit; } } function _find_post_by_old_slug( $post_type ) { global $wpdb; $query = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta, $wpdb->posts WHERE ID = post_id AND post_type = %s AND meta_key = '_wp_old_slug' AND meta_value = %s", $post_type, get_query_var( 'name' ) ); if ( get_query_var( 'year' ) ) { $query .= $wpdb->prepare( ' AND YEAR(post_date) = %d', get_query_var( 'year' ) ); } if ( get_query_var( 'monthnum' ) ) { $query .= $wpdb->prepare( ' AND MONTH(post_date) = %d', get_query_var( 'monthnum' ) ); } if ( get_query_var( 'day' ) ) { $query .= $wpdb->prepare( ' AND DAYOFMONTH(post_date) = %d', get_query_var( 'day' ) ); } $key = md5( $query ); $last_changed = wp_cache_get_last_changed( 'posts' ); $cache_key = "find_post_by_old_slug:$key:$last_changed"; $cache = wp_cache_get( $cache_key, 'post-queries' ); if ( false !== $cache ) { $id = $cache; } else { $id = (int) $wpdb->get_var( $query ); wp_cache_set( $cache_key, $id, 'post-queries' ); } return $id; } function _find_post_by_old_date( $post_type ) { global $wpdb; $date_query = ''; if ( get_query_var( 'year' ) ) { $date_query .= $wpdb->prepare( ' AND YEAR(pm_date.meta_value) = %d', get_query_var( 'year' ) ); } if ( get_query_var( 'monthnum' ) ) { $date_query .= $wpdb->prepare( ' AND MONTH(pm_date.meta_value) = %d', get_query_var( 'monthnum' ) ); } if ( get_query_var( 'day' ) ) { $date_query .= $wpdb->prepare( ' AND DAYOFMONTH(pm_date.meta_value) = %d', get_query_var( 'day' ) ); } $id = 0; if ( $date_query ) { $query = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta AS pm_date, $wpdb->posts WHERE ID = post_id AND post_type = %s AND meta_key = '_wp_old_date' AND post_name = %s" . $date_query, $post_type, get_query_var( 'name' ) ); $key = md5( $query ); $last_changed = wp_cache_get_last_changed( 'posts' ); $cache_key = "find_post_by_old_date:$key:$last_changed"; $cache = wp_cache_get( $cache_key, 'post-queries' ); if ( false !== $cache ) { $id = $cache; } else { $id = (int) $wpdb->get_var( $query ); if ( ! $id ) { $id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts, $wpdb->postmeta AS pm_slug, $wpdb->postmeta AS pm_date WHERE ID = pm_slug.post_id AND ID = pm_date.post_id AND post_type = %s AND pm_slug.meta_key = '_wp_old_slug' AND pm_slug.meta_value = %s AND pm_date.meta_key = '_wp_old_date'" . $date_query, $post_type, get_query_var( 'name' ) ) ); } wp_cache_set( $cache_key, $id, 'post-queries' ); } } return $id; } function setup_postdata( $post ) { global $wp_query; if ( ! empty( $wp_query ) && $wp_query instanceof WP_Query ) { return $wp_query->setup_postdata( $post ); } return false; } function generate_postdata( $post ) { global $wp_query; if ( ! empty( $wp_query ) && $wp_query instanceof WP_Query ) { return $wp_query->generate_postdata( $post ); } return false; } 