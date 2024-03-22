<?php
 #[AllowDynamicProperties]
 class WP_Comment_Query { public $request; public $meta_query = false; protected $meta_query_clauses; protected $sql_clauses = array( 'select' => '', 'from' => '', 'where' => array(), 'groupby' => '', 'orderby' => '', 'limits' => '', ); protected $filtered_where_clause; public $date_query = false; public $query_vars; public $query_var_defaults; public $comments; public $found_comments = 0; public $max_num_pages = 0; public function __call( $name, $arguments ) { if ( 'get_search_sql' === $name ) { return $this->get_search_sql( ...$arguments ); } return false; } public function __construct( $query = '' ) { $this->query_var_defaults = array( 'author_email' => '', 'author_url' => '', 'author__in' => '', 'author__not_in' => '', 'include_unapproved' => '', 'fields' => '', 'ID' => '', 'comment__in' => '', 'comment__not_in' => '', 'karma' => '', 'number' => '', 'offset' => '', 'no_found_rows' => true, 'orderby' => '', 'order' => 'DESC', 'paged' => 1, 'parent' => '', 'parent__in' => '', 'parent__not_in' => '', 'post_author__in' => '', 'post_author__not_in' => '', 'post_ID' => '', 'post_id' => 0, 'post__in' => '', 'post__not_in' => '', 'post_author' => '', 'post_name' => '', 'post_parent' => '', 'post_status' => '', 'post_type' => '', 'status' => 'all', 'type' => '', 'type__in' => '', 'type__not_in' => '', 'user_id' => '', 'search' => '', 'count' => false, 'meta_key' => '', 'meta_value' => '', 'meta_query' => '', 'date_query' => null, 'hierarchical' => false, 'cache_domain' => 'core', 'update_comment_meta_cache' => true, 'update_comment_post_cache' => false, ); if ( ! empty( $query ) ) { $this->query( $query ); } } public function parse_query( $query = '' ) { if ( empty( $query ) ) { $query = $this->query_vars; } $this->query_vars = wp_parse_args( $query, $this->query_var_defaults ); do_action_ref_array( 'parse_comment_query', array( &$this ) ); } public function query( $query ) { $this->query_vars = wp_parse_args( $query ); return $this->get_comments(); } public function get_comments() { global $wpdb; $this->parse_query(); $this->meta_query = new WP_Meta_Query(); $this->meta_query->parse_query_vars( $this->query_vars ); do_action_ref_array( 'pre_get_comments', array( &$this ) ); $this->meta_query->parse_query_vars( $this->query_vars ); if ( ! empty( $this->meta_query->queries ) ) { $this->meta_query_clauses = $this->meta_query->get_sql( 'comment', $wpdb->comments, 'comment_ID', $this ); } $comment_data = null; $comment_data = apply_filters_ref_array( 'comments_pre_query', array( $comment_data, &$this ) ); if ( null !== $comment_data ) { if ( is_array( $comment_data ) && ! $this->query_vars['count'] ) { $this->comments = $comment_data; } return $comment_data; } $_args = wp_array_slice_assoc( $this->query_vars, array_keys( $this->query_var_defaults ) ); unset( $_args['fields'], $_args['update_comment_meta_cache'], $_args['update_comment_post_cache'] ); $key = md5( serialize( $_args ) ); $last_changed = wp_cache_get_last_changed( 'comment' ); $cache_key = "get_comments:$key:$last_changed"; $cache_value = wp_cache_get( $cache_key, 'comment-queries' ); if ( false === $cache_value ) { $comment_ids = $this->get_comment_ids(); if ( $comment_ids ) { $this->set_found_comments(); } $cache_value = array( 'comment_ids' => $comment_ids, 'found_comments' => $this->found_comments, ); wp_cache_add( $cache_key, $cache_value, 'comment-queries' ); } else { $comment_ids = $cache_value['comment_ids']; $this->found_comments = $cache_value['found_comments']; } if ( $this->found_comments && $this->query_vars['number'] ) { $this->max_num_pages = ceil( $this->found_comments / $this->query_vars['number'] ); } if ( $this->query_vars['count'] ) { return (int) $comment_ids; } $comment_ids = array_map( 'intval', $comment_ids ); if ( $this->query_vars['update_comment_meta_cache'] ) { wp_lazyload_comment_meta( $comment_ids ); } if ( 'ids' === $this->query_vars['fields'] ) { $this->comments = $comment_ids; return $this->comments; } _prime_comment_caches( $comment_ids, false ); $_comments = array(); foreach ( $comment_ids as $comment_id ) { $_comment = get_comment( $comment_id ); if ( $_comment ) { $_comments[] = $_comment; } } if ( $this->query_vars['update_comment_post_cache'] ) { $comment_post_ids = array(); foreach ( $_comments as $_comment ) { $comment_post_ids[] = $_comment->comment_post_ID; } _prime_post_caches( $comment_post_ids, false, false ); } $_comments = apply_filters_ref_array( 'the_comments', array( $_comments, &$this ) ); $comments = array_map( 'get_comment', $_comments ); if ( $this->query_vars['hierarchical'] ) { $comments = $this->fill_descendants( $comments ); } $this->comments = $comments; return $this->comments; } protected function get_comment_ids() { global $wpdb; $approved_clauses = array(); $status_clauses = array(); $statuses = wp_parse_list( $this->query_vars['status'] ); if ( empty( $statuses ) ) { $statuses = array( 'all' ); } if ( ! in_array( 'any', $statuses, true ) ) { foreach ( $statuses as $status ) { switch ( $status ) { case 'hold': $status_clauses[] = "comment_approved = '0'"; break; case 'approve': $status_clauses[] = "comment_approved = '1'"; break; case 'all': case '': $status_clauses[] = "( comment_approved = '0' OR comment_approved = '1' )"; break; default: $status_clauses[] = $wpdb->prepare( 'comment_approved = %s', $status ); break; } } if ( ! empty( $status_clauses ) ) { $approved_clauses[] = '( ' . implode( ' OR ', $status_clauses ) . ' )'; } } if ( ! empty( $this->query_vars['include_unapproved'] ) ) { $include_unapproved = wp_parse_list( $this->query_vars['include_unapproved'] ); foreach ( $include_unapproved as $unapproved_identifier ) { if ( is_numeric( $unapproved_identifier ) ) { $approved_clauses[] = $wpdb->prepare( "( user_id = %d AND comment_approved = '0' )", $unapproved_identifier ); } else { if ( ! empty( $_GET['unapproved'] ) && ! empty( $_GET['moderation-hash'] ) ) { $approved_clauses[] = $wpdb->prepare( "( comment_author_email = %s AND comment_approved = '0' AND {$wpdb->comments}.comment_ID = %d )", $unapproved_identifier, (int) $_GET['unapproved'] ); } else { $approved_clauses[] = $wpdb->prepare( "( comment_author_email = %s AND comment_approved = '0' )", $unapproved_identifier ); } } } } if ( ! empty( $approved_clauses ) ) { if ( 1 === count( $approved_clauses ) ) { $this->sql_clauses['where']['approved'] = $approved_clauses[0]; } else { $this->sql_clauses['where']['approved'] = '( ' . implode( ' OR ', $approved_clauses ) . ' )'; } } $order = ( 'ASC' === strtoupper( $this->query_vars['order'] ) ) ? 'ASC' : 'DESC'; if ( in_array( $this->query_vars['orderby'], array( 'none', array(), false ), true ) ) { $orderby = ''; } elseif ( ! empty( $this->query_vars['orderby'] ) ) { $ordersby = is_array( $this->query_vars['orderby'] ) ? $this->query_vars['orderby'] : preg_split( '/[,\s]/', $this->query_vars['orderby'] ); $orderby_array = array(); $found_orderby_comment_id = false; foreach ( $ordersby as $_key => $_value ) { if ( ! $_value ) { continue; } if ( is_int( $_key ) ) { $_orderby = $_value; $_order = $order; } else { $_orderby = $_key; $_order = $_value; } if ( ! $found_orderby_comment_id && in_array( $_orderby, array( 'comment_ID', 'comment__in' ), true ) ) { $found_orderby_comment_id = true; } $parsed = $this->parse_orderby( $_orderby ); if ( ! $parsed ) { continue; } if ( 'comment__in' === $_orderby ) { $orderby_array[] = $parsed; continue; } $orderby_array[] = $parsed . ' ' . $this->parse_order( $_order ); } if ( empty( $orderby_array ) ) { $orderby_array[] = "$wpdb->comments.comment_date_gmt $order"; } if ( ! $found_orderby_comment_id ) { $comment_id_order = ''; foreach ( $orderby_array as $orderby_clause ) { if ( preg_match( '/comment_date(?:_gmt)*\ (ASC|DESC)/', $orderby_clause, $match ) ) { $comment_id_order = $match[1]; break; } } if ( ! $comment_id_order ) { foreach ( $orderby_array as $orderby_clause ) { if ( str_contains( 'ASC', $orderby_clause ) ) { $comment_id_order = 'ASC'; } else { $comment_id_order = 'DESC'; } break; } } if ( ! $comment_id_order ) { $comment_id_order = 'DESC'; } $orderby_array[] = "$wpdb->comments.comment_ID $comment_id_order"; } $orderby = implode( ', ', $orderby_array ); } else { $orderby = "$wpdb->comments.comment_date_gmt $order"; } $number = absint( $this->query_vars['number'] ); $offset = absint( $this->query_vars['offset'] ); $paged = absint( $this->query_vars['paged'] ); $limits = ''; if ( ! empty( $number ) ) { if ( $offset ) { $limits = 'LIMIT ' . $offset . ',' . $number; } else { $limits = 'LIMIT ' . ( $number * ( $paged - 1 ) ) . ',' . $number; } } if ( $this->query_vars['count'] ) { $fields = 'COUNT(*)'; } else { $fields = "$wpdb->comments.comment_ID"; } $post_id = absint( $this->query_vars['post_id'] ); if ( ! empty( $post_id ) ) { $this->sql_clauses['where']['post_id'] = $wpdb->prepare( 'comment_post_ID = %d', $post_id ); } if ( ! empty( $this->query_vars['comment__in'] ) ) { $this->sql_clauses['where']['comment__in'] = "$wpdb->comments.comment_ID IN ( " . implode( ',', wp_parse_id_list( $this->query_vars['comment__in'] ) ) . ' )'; } if ( ! empty( $this->query_vars['comment__not_in'] ) ) { $this->sql_clauses['where']['comment__not_in'] = "$wpdb->comments.comment_ID NOT IN ( " . implode( ',', wp_parse_id_list( $this->query_vars['comment__not_in'] ) ) . ' )'; } if ( ! empty( $this->query_vars['parent__in'] ) ) { $this->sql_clauses['where']['parent__in'] = 'comment_parent IN ( ' . implode( ',', wp_parse_id_list( $this->query_vars['parent__in'] ) ) . ' )'; } if ( ! empty( $this->query_vars['parent__not_in'] ) ) { $this->sql_clauses['where']['parent__not_in'] = 'comment_parent NOT IN ( ' . implode( ',', wp_parse_id_list( $this->query_vars['parent__not_in'] ) ) . ' )'; } if ( ! empty( $this->query_vars['post__in'] ) ) { $this->sql_clauses['where']['post__in'] = 'comment_post_ID IN ( ' . implode( ',', wp_parse_id_list( $this->query_vars['post__in'] ) ) . ' )'; } if ( ! empty( $this->query_vars['post__not_in'] ) ) { $this->sql_clauses['where']['post__not_in'] = 'comment_post_ID NOT IN ( ' . implode( ',', wp_parse_id_list( $this->query_vars['post__not_in'] ) ) . ' )'; } if ( '' !== $this->query_vars['author_email'] ) { $this->sql_clauses['where']['author_email'] = $wpdb->prepare( 'comment_author_email = %s', $this->query_vars['author_email'] ); } if ( '' !== $this->query_vars['author_url'] ) { $this->sql_clauses['where']['author_url'] = $wpdb->prepare( 'comment_author_url = %s', $this->query_vars['author_url'] ); } if ( '' !== $this->query_vars['karma'] ) { $this->sql_clauses['where']['karma'] = $wpdb->prepare( 'comment_karma = %d', $this->query_vars['karma'] ); } $raw_types = array( 'IN' => array_merge( (array) $this->query_vars['type'], (array) $this->query_vars['type__in'] ), 'NOT IN' => (array) $this->query_vars['type__not_in'], ); $comment_types = array(); foreach ( $raw_types as $operator => $_raw_types ) { $_raw_types = array_unique( $_raw_types ); foreach ( $_raw_types as $type ) { switch ( $type ) { case '': case 'all': break; case 'comment': case 'comments': $comment_types[ $operator ][] = "''"; $comment_types[ $operator ][] = "'comment'"; break; case 'pings': $comment_types[ $operator ][] = "'pingback'"; $comment_types[ $operator ][] = "'trackback'"; break; default: $comment_types[ $operator ][] = $wpdb->prepare( '%s', $type ); break; } } if ( ! empty( $comment_types[ $operator ] ) ) { $types_sql = implode( ', ', $comment_types[ $operator ] ); $this->sql_clauses['where'][ 'comment_type__' . strtolower( str_replace( ' ', '_', $operator ) ) ] = "comment_type $operator ($types_sql)"; } } $parent = $this->query_vars['parent']; if ( $this->query_vars['hierarchical'] && ! $parent ) { $parent = 0; } if ( '' !== $parent ) { $this->sql_clauses['where']['parent'] = $wpdb->prepare( 'comment_parent = %d', $parent ); } if ( is_array( $this->query_vars['user_id'] ) ) { $this->sql_clauses['where']['user_id'] = 'user_id IN (' . implode( ',', array_map( 'absint', $this->query_vars['user_id'] ) ) . ')'; } elseif ( '' !== $this->query_vars['user_id'] ) { $this->sql_clauses['where']['user_id'] = $wpdb->prepare( 'user_id = %d', $this->query_vars['user_id'] ); } if ( isset( $this->query_vars['search'] ) && strlen( $this->query_vars['search'] ) ) { $search_sql = $this->get_search_sql( $this->query_vars['search'], array( 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_author_IP', 'comment_content' ) ); $this->sql_clauses['where']['search'] = preg_replace( '/^\s*AND\s*/', '', $search_sql ); } $join_posts_table = false; $plucked = wp_array_slice_assoc( $this->query_vars, array( 'post_author', 'post_name', 'post_parent' ) ); $post_fields = array_filter( $plucked ); if ( ! empty( $post_fields ) ) { $join_posts_table = true; foreach ( $post_fields as $field_name => $field_value ) { $esses = array_fill( 0, count( (array) $field_value ), '%s' ); $this->sql_clauses['where'][ $field_name ] = $wpdb->prepare( " {$wpdb->posts}.{$field_name} IN (" . implode( ',', $esses ) . ')', $field_value ); } } foreach ( array( 'post_status', 'post_type' ) as $field_name ) { $q_values = array(); if ( ! empty( $this->query_vars[ $field_name ] ) ) { $q_values = $this->query_vars[ $field_name ]; if ( ! is_array( $q_values ) ) { $q_values = explode( ',', $q_values ); } if ( in_array( 'any', $q_values, true ) || empty( $q_values ) ) { continue; } $join_posts_table = true; $esses = array_fill( 0, count( $q_values ), '%s' ); $this->sql_clauses['where'][ $field_name ] = $wpdb->prepare( " {$wpdb->posts}.{$field_name} IN (" . implode( ',', $esses ) . ')', $q_values ); } } if ( ! empty( $this->query_vars['author__in'] ) ) { $this->sql_clauses['where']['author__in'] = 'user_id IN ( ' . implode( ',', wp_parse_id_list( $this->query_vars['author__in'] ) ) . ' )'; } if ( ! empty( $this->query_vars['author__not_in'] ) ) { $this->sql_clauses['where']['author__not_in'] = 'user_id NOT IN ( ' . implode( ',', wp_parse_id_list( $this->query_vars['author__not_in'] ) ) . ' )'; } if ( ! empty( $this->query_vars['post_author__in'] ) ) { $join_posts_table = true; $this->sql_clauses['where']['post_author__in'] = 'post_author IN ( ' . implode( ',', wp_parse_id_list( $this->query_vars['post_author__in'] ) ) . ' )'; } if ( ! empty( $this->query_vars['post_author__not_in'] ) ) { $join_posts_table = true; $this->sql_clauses['where']['post_author__not_in'] = 'post_author NOT IN ( ' . implode( ',', wp_parse_id_list( $this->query_vars['post_author__not_in'] ) ) . ' )'; } $join = ''; $groupby = ''; if ( $join_posts_table ) { $join .= "JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->comments.comment_post_ID"; } if ( ! empty( $this->meta_query_clauses ) ) { $join .= $this->meta_query_clauses['join']; $this->sql_clauses['where']['meta_query'] = preg_replace( '/^\s*AND\s*/', '', $this->meta_query_clauses['where'] ); if ( ! $this->query_vars['count'] ) { $groupby = "{$wpdb->comments}.comment_ID"; } } if ( ! empty( $this->query_vars['date_query'] ) && is_array( $this->query_vars['date_query'] ) ) { $this->date_query = new WP_Date_Query( $this->query_vars['date_query'], 'comment_date' ); $this->sql_clauses['where']['date_query'] = preg_replace( '/^\s*AND\s*/', '', $this->date_query->get_sql() ); } $where = implode( ' AND ', $this->sql_clauses['where'] ); $pieces = array( 'fields', 'join', 'where', 'orderby', 'limits', 'groupby' ); $clauses = apply_filters_ref_array( 'comments_clauses', array( compact( $pieces ), &$this ) ); $fields = isset( $clauses['fields'] ) ? $clauses['fields'] : ''; $join = isset( $clauses['join'] ) ? $clauses['join'] : ''; $where = isset( $clauses['where'] ) ? $clauses['where'] : ''; $orderby = isset( $clauses['orderby'] ) ? $clauses['orderby'] : ''; $limits = isset( $clauses['limits'] ) ? $clauses['limits'] : ''; $groupby = isset( $clauses['groupby'] ) ? $clauses['groupby'] : ''; $this->filtered_where_clause = $where; if ( $where ) { $where = 'WHERE ' . $where; } if ( $groupby ) { $groupby = 'GROUP BY ' . $groupby; } if ( $orderby ) { $orderby = "ORDER BY $orderby"; } $found_rows = ''; if ( ! $this->query_vars['no_found_rows'] ) { $found_rows = 'SQL_CALC_FOUND_ROWS'; } $this->sql_clauses['select'] = "SELECT $found_rows $fields"; $this->sql_clauses['from'] = "FROM $wpdb->comments $join"; $this->sql_clauses['groupby'] = $groupby; $this->sql_clauses['orderby'] = $orderby; $this->sql_clauses['limits'] = $limits; $this->request = "
			{$this->sql_clauses['select']}
			{$this->sql_clauses['from']}
			{$where}
			{$this->sql_clauses['groupby']}
			{$this->sql_clauses['orderby']}
			{$this->sql_clauses['limits']}
		"; if ( $this->query_vars['count'] ) { return (int) $wpdb->get_var( $this->request ); } else { $comment_ids = $wpdb->get_col( $this->request ); return array_map( 'intval', $comment_ids ); } } private function set_found_comments() { global $wpdb; if ( $this->query_vars['number'] && ! $this->query_vars['no_found_rows'] ) { $found_comments_query = apply_filters( 'found_comments_query', 'SELECT FOUND_ROWS()', $this ); $this->found_comments = (int) $wpdb->get_var( $found_comments_query ); } } protected function fill_descendants( $comments ) { $levels = array( 0 => wp_list_pluck( $comments, 'comment_ID' ), ); $key = md5( serialize( wp_array_slice_assoc( $this->query_vars, array_keys( $this->query_var_defaults ) ) ) ); $last_changed = wp_cache_get_last_changed( 'comment' ); $level = 0; $exclude_keys = array( 'parent', 'parent__in', 'parent__not_in' ); do { $child_ids = array(); $uncached_parent_ids = array(); $_parent_ids = $levels[ $level ]; if ( $_parent_ids ) { $cache_keys = array(); foreach ( $_parent_ids as $parent_id ) { $cache_keys[ $parent_id ] = "get_comment_child_ids:$parent_id:$key:$last_changed"; } $cache_data = wp_cache_get_multiple( array_values( $cache_keys ), 'comment-queries' ); foreach ( $_parent_ids as $parent_id ) { $parent_child_ids = $cache_data[ $cache_keys[ $parent_id ] ]; if ( false !== $parent_child_ids ) { $child_ids = array_merge( $child_ids, $parent_child_ids ); } else { $uncached_parent_ids[] = $parent_id; } } } if ( $uncached_parent_ids ) { $parent_query_args = $this->query_vars; foreach ( $exclude_keys as $exclude_key ) { $parent_query_args[ $exclude_key ] = ''; } $parent_query_args['parent__in'] = $uncached_parent_ids; $parent_query_args['no_found_rows'] = true; $parent_query_args['hierarchical'] = false; $parent_query_args['offset'] = 0; $parent_query_args['number'] = 0; $level_comments = get_comments( $parent_query_args ); $parent_map = array_fill_keys( $uncached_parent_ids, array() ); foreach ( $level_comments as $level_comment ) { $parent_map[ $level_comment->comment_parent ][] = $level_comment->comment_ID; $child_ids[] = $level_comment->comment_ID; } $data = array(); foreach ( $parent_map as $parent_id => $children ) { $cache_key = "get_comment_child_ids:$parent_id:$key:$last_changed"; $data[ $cache_key ] = $children; } wp_cache_set_multiple( $data, 'comment-queries' ); } ++$level; $levels[ $level ] = $child_ids; } while ( $child_ids ); $descendant_ids = array(); for ( $i = 1, $c = count( $levels ); $i < $c; $i++ ) { $descendant_ids = array_merge( $descendant_ids, $levels[ $i ] ); } _prime_comment_caches( $descendant_ids, $this->query_vars['update_comment_meta_cache'] ); $all_comments = $comments; foreach ( $descendant_ids as $descendant_id ) { $all_comments[] = get_comment( $descendant_id ); } if ( 'threaded' === $this->query_vars['hierarchical'] ) { $threaded_comments = array(); $ref = array(); foreach ( $all_comments as $k => $c ) { $_c = get_comment( $c->comment_ID ); if ( ! isset( $ref[ $c->comment_parent ] ) ) { $threaded_comments[ $_c->comment_ID ] = $_c; $ref[ $_c->comment_ID ] = $threaded_comments[ $_c->comment_ID ]; } else { $ref[ $_c->comment_parent ]->add_child( $_c ); $ref[ $_c->comment_ID ] = $ref[ $_c->comment_parent ]->get_child( $_c->comment_ID ); } } foreach ( $ref as $_ref ) { $_ref->populated_children( true ); } $comments = $threaded_comments; } else { $comments = $all_comments; } return $comments; } protected function get_search_sql( $search, $columns ) { global $wpdb; $like = '%' . $wpdb->esc_like( $search ) . '%'; $searches = array(); foreach ( $columns as $column ) { $searches[] = $wpdb->prepare( "$column LIKE %s", $like ); } return ' AND (' . implode( ' OR ', $searches ) . ')'; } protected function parse_orderby( $orderby ) { global $wpdb; $allowed_keys = array( 'comment_agent', 'comment_approved', 'comment_author', 'comment_author_email', 'comment_author_IP', 'comment_author_url', 'comment_content', 'comment_date', 'comment_date_gmt', 'comment_ID', 'comment_karma', 'comment_parent', 'comment_post_ID', 'comment_type', 'user_id', ); if ( ! empty( $this->query_vars['meta_key'] ) ) { $allowed_keys[] = $this->query_vars['meta_key']; $allowed_keys[] = 'meta_value'; $allowed_keys[] = 'meta_value_num'; } $meta_query_clauses = $this->meta_query->get_clauses(); if ( $meta_query_clauses ) { $allowed_keys = array_merge( $allowed_keys, array_keys( $meta_query_clauses ) ); } $parsed = false; if ( $this->query_vars['meta_key'] === $orderby || 'meta_value' === $orderby ) { $parsed = "$wpdb->commentmeta.meta_value"; } elseif ( 'meta_value_num' === $orderby ) { $parsed = "$wpdb->commentmeta.meta_value+0"; } elseif ( 'comment__in' === $orderby ) { $comment__in = implode( ',', array_map( 'absint', $this->query_vars['comment__in'] ) ); $parsed = "FIELD( {$wpdb->comments}.comment_ID, $comment__in )"; } elseif ( in_array( $orderby, $allowed_keys, true ) ) { if ( isset( $meta_query_clauses[ $orderby ] ) ) { $meta_clause = $meta_query_clauses[ $orderby ]; $parsed = sprintf( 'CAST(%s.meta_value AS %s)', esc_sql( $meta_clause['alias'] ), esc_sql( $meta_clause['cast'] ) ); } else { $parsed = "$wpdb->comments.$orderby"; } } return $parsed; } protected function parse_order( $order ) { if ( ! is_string( $order ) || empty( $order ) ) { return 'DESC'; } if ( 'ASC' === strtoupper( $order ) ) { return 'ASC'; } else { return 'DESC'; } } } 