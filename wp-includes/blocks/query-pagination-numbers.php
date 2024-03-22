<?php
 function render_block_core_query_pagination_numbers( $attributes, $content, $block ) { $page_key = isset( $block->context['queryId'] ) ? 'query-' . $block->context['queryId'] . '-page' : 'query-page'; $enhanced_pagination = isset( $block->context['enhancedPagination'] ) && $block->context['enhancedPagination']; $page = empty( $_GET[ $page_key ] ) ? 1 : (int) $_GET[ $page_key ]; $max_page = isset( $block->context['query']['pages'] ) ? (int) $block->context['query']['pages'] : 0; $wrapper_attributes = get_block_wrapper_attributes(); $content = ''; global $wp_query; $mid_size = isset( $block->attributes['midSize'] ) ? (int) $block->attributes['midSize'] : null; if ( isset( $block->context['query']['inherit'] ) && $block->context['query']['inherit'] ) { $total = ! $max_page || $max_page > $wp_query->max_num_pages ? $wp_query->max_num_pages : $max_page; $paginate_args = array( 'prev_next' => false, 'total' => $total, ); if ( null !== $mid_size ) { $paginate_args['mid_size'] = $mid_size; } $content = paginate_links( $paginate_args ); } else { $block_query = new WP_Query( build_query_vars_from_query_block( $block, $page ) ); $prev_wp_query = $wp_query; $wp_query = $block_query; $total = ! $max_page || $max_page > $wp_query->max_num_pages ? $wp_query->max_num_pages : $max_page; $paginate_args = array( 'base' => '%_%', 'format' => "?$page_key=%#%", 'current' => max( 1, $page ), 'total' => $total, 'prev_next' => false, ); if ( null !== $mid_size ) { $paginate_args['mid_size'] = $mid_size; } if ( 1 !== $page ) { $paginate_args['add_args'] = array( 'cst' => '' ); } $paged = empty( $_GET['paged'] ) ? null : (int) $_GET['paged']; if ( $paged ) { $paginate_args['add_args'] = array( 'paged' => $paged ); } $content = paginate_links( $paginate_args ); wp_reset_postdata(); $wp_query = $prev_wp_query; } if ( empty( $content ) ) { return ''; } if ( $enhanced_pagination ) { $p = new WP_HTML_Tag_Processor( $content ); while ( $p->next_tag( array( 'tag_name' => 'a', 'class_name' => 'page-numbers', ) ) ) { $p->set_attribute( 'data-wp-on--click', 'actions.core.query.navigate' ); } $content = $p->get_updated_html(); } return sprintf( '<div %1$s>%2$s</div>', $wrapper_attributes, $content ); } function register_block_core_query_pagination_numbers() { register_block_type_from_metadata( __DIR__ . '/query-pagination-numbers', array( 'render_callback' => 'render_block_core_query_pagination_numbers', ) ); } add_action( 'init', 'register_block_core_query_pagination_numbers' ); 