<?php
namespace TheTrainingMangerLMS\Content\MyAccount;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

import('TheTrainingMangerLMS.Utility.WP_List_Table');
import('TheTrainingMangerLMS.Constants');

final class CourseListTable extends \TheTrainingMangerLMS\Utility\WP_List_Table {
	private $categories;	

	function __construct( $args = array() ) {
		error_log(\TheTrainingMangerLMS\Admin\CourseBuilder::MENU_HANDLE);
		$args = wp_parse_args( $args, array(
			'plural' => 'courses',
			'singular' => 'course',
		) );

		parent::__construct( $args );
	}

	function get_columns() {
		$columns = array(
			'title'  => 'Title',
			'type'   => 'Type',
			'categories' => 'Categories',
			'status' => 'Status'
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'title'  => array('title',false)
		);
		return $sortable_columns;
	}

	function get_filterable_columns() {
		return array();
		/*return array(
			'title',
			'type',
			'categories');*/
	}

	function get_category() {
		if (isset($_REQUEST['cat']) && ($_REQUEST['cat'] != '0')) {
			if (in_array($_REQUEST['cat'], get_categories(array( 'fields' => 'ids', 'orderby' => 'id')))) {
				return $_REQUEST['cat'];
			}
		}
		return false;
	}

	function get_type() {
		if (isset($_REQUEST['type'])) {
			if (in_array($_REQUEST['type'], array('online', 'live'))) {
				return $_REQUEST['type'];
			}
		}
		return false;
	}

	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$filterable = $this->get_filterable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable, $filterable);

		$sorting = $this->get_sorting();
		$filter = array( 'search' => $this->get_search(), 'category' => $this->get_category(), 'type' => $this->get_type());

		$current_page = $this->get_pagenum();
		$per_page = 10;
		$total_items = $this->count_items($filter);
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page
		) );
		$offset = ($current_page - 1) * $per_page;
  		$this->items = $this->fetch_items($per_page, $offset, $sorting, $filter);

  		$categories = get_categories();
  		$mapped = array();
  		foreach($categories as $category) {
  			$mapped[$category->term_id] = $category;
  		}
  		$this->categories = $mapped;
	}

	private function build_filter_sql($filter) {
		global $wpdb;
		$condition = "";
		$condition .= $filter['search'] !== false ? ' AND ' . implode(' AND ', self::db_create_condition_list(self::db_prepare_search_terms(self::get_search_terms($filter['search'])), 'post_title')) : "";
		$condition .= $filter['category'] !== false ? $wpdb->prepare(' AND t.term_id IN (%s)', $filter['category']) : "";
		if ($filter['type'] !== false) {
			if ($filter['type'] == 'online') {
				$type = 'TheTrainingMangerLMS\OnlineCourse';
			} else {
				$type = 'TheTrainingMangerLMS\LiveCourse';
			}
			$condition .= $wpdb->prepare(' AND meta_value = %s', $type);
		}
		return $condition;		
	}

	private function count_items($filter) {
		global $wpdb;
		$condition = $this->build_filter_sql($filter);
		$query = 
		"SELECT count(*) FROM $wpdb->posts p" .
		($filter['type'] ?
		" JOIN $wpdb->postmeta m ON (p.ID = m.post_id)" : "") .
		($filter['category'] ?
		" JOIN ($wpdb->terms AS t, $wpdb->term_taxonomy AS tt, $wpdb->term_relationships AS tr) 
		ON ((tr.`object_id` = p.ID) AND (tt.term_id = t.term_id) AND (tr.term_taxonomy_id = tt.term_taxonomy_id))" : "") .
		$wpdb->prepare(" WHERE p.post_type = %s", ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::CoursePostType)) .
		($filter['category'] ?
		" AND tt.taxonomy = 'category'" : "") .
		($filter['type'] ?
		$wpdb->prepare(" AND m.meta_key = %s", ttp_lms_prefix('course_type')) : "") .
		$condition .
		($filter['category'] ?
		" GROUP BY p.ID" : "");
		return $wpdb->get_var($query);//$wpdb->prepare($query, ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::CoursePostType)));
	}

	private function fetch_items($count, $offset, $sorting, $filter) {
		global $wpdb;
		$order = $sorting !== false ? " ORDER BY " . $sorting['orderby'] . " " . $sorting['order'] : "";
		$condition = $this->build_filter_sql($filter);
		$query =
		"SELECT ID, post_title AS title, meta_value AS type, IFNULL(GROUP_CONCAT(IF(tt.`taxonomy` = 'category', t.term_id, NULL) ORDER BY t.name SEPARATOR ','), '') AS `categories`, post_status AS status FROM $wpdb->posts p
		JOIN $wpdb->postmeta m ON (p.ID = m.post_id) 
		LEFT OUTER JOIN ($wpdb->terms AS t, $wpdb->term_taxonomy AS tt, $wpdb->term_relationships AS tr) 
		ON ((tr.`object_id` = p.ID) AND (tt.term_id = t.term_id) AND (tr.term_taxonomy_id = tt.term_taxonomy_id))
		WHERE p.post_type = %s AND m.meta_key = %s " . $condition . " GROUP BY p.ID " . $order . " LIMIT %d OFFSET %d";
		$query = $wpdb->prepare($query, ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::CoursePostType), ttp_lms_prefix('course_type'), $count, $offset);
		return $wpdb->get_results($query, ARRAY_A);
	}

	private static function get_search_terms($search) {
		$matches = array();
		preg_match_all('/([^"\s]+)|(?:"([^"]+)")/', $search, $matches);
		$merge = array();
		while(list($key) = each($matches[0])) {
			array_push($merge, $matches[1][$key] | $matches[2][$key]); // phrase =>, quoted => false/true, modifier => +/-
		}
		return array_unique($merge);
	}

	private static function escape_regexp($term) {
		return preg_replace("/([.\[\]*^\$])/", '\\\$1', $term);
	}

	private static function db_prepare_search_terms($terms) {
		$escaped = array();
		foreach($terms as $term) {
			array_push($escaped, '[[:<:]]'.AddSlashes(self::escape_regexp($term)).'[[:>:]]');
		}
		return $escaped;
	}

	private static function db_create_condition_list($terms, $field) {
		global $wpdb;
		$conditions = array();
		foreach($terms as $term) {
			array_push($conditions, $wpdb->prepare($field . " REGEXP %s", $term));
		}
		return $conditions;
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'title': echo '<strong><a class="row-title" title="Edit" href="' .
							self::get_edit_url($item['ID']) . '">' . $item['title'] . '</a>';
							// row_actions
							break;
			case 'type': if (strpos($item[ $column_name ], 'Online') !== false) echo 'Online'; else echo 'Live'; break;
			case 'categories': $this->display_categories($item['categories'] == '' ? array() : explode(',', $item['categories'])); break;
			case 'status': echo ucfirst($item['status']); break;
			return $item[ $column_name ];
//			default:
//			return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
			}
	}

	function column_filter_default( $column_name ) {
		switch( $column_name ) {
			case 'title': 
				if (isset($_REQUEST['s']) || $this->has_items()) {
					echo '<form class="search-box" style="position: relative;">';
					echo '<input type="text" placeholder="Filter by Name" value="" name="s" style="width: 100%; padding-left: 2px; padding-right: 42px;"></input>';
					echo '<button type="submit" class="dashicons-before dashicons-search" style="position: absolute; right: 0px;"></button>';
					echo '</form></div>';
				}
			     //$this->search_box('Filter', 'title');
			     break;
			case 'type':
				if (!( empty( $_REQUEST['type'] ) && !$this->has_items() )) {
					$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'all';
?>
	<select name="type">
		<option value="all" <?php echo $type == 'all' ? 'selected' : ''; ?>>All Types</option>
		<option value="online" <?php echo $type == 'online' ? 'selected' : ''; ?>>Online</option>
		<option value="live" <?php echo $type == 'live' ? 'selected' : ''; ?>>Live</option>
	</select>
<?php
				}
				break;
			case 'categories':
				if (!( empty( $_REQUEST['cat'] ) && !$this->has_items() )) {
					$cat = isset($_REQUEST['cat']) ? $_REQUEST['cat'] : 0;
					$dropdown_options = array(
						'show_option_all' => __( 'All Categories' ),
						'hide_empty' => 1,
						'hierarchical' => 1,
						'show_count' => 0,
						'orderby' => 'name',
						'selected' => $cat
					);
					wp_dropdown_categories( $dropdown_options );
				}
				break;
		}
	}

	function display_categories($category_ids) {
		$categories = $this->categories;

		// do it the WP way
		if ($category_ids) {
			$out = array();
			foreach ( $category_ids as $id ) {
				$posts_in_term_qv = array();
				$posts_in_term_qv['post_type'] = ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::CoursePostType);
				$posts_in_term_qv[ 'category_name' ] = $categories[$id]->slug;

				$out[] = sprintf( '<a href="%s">%s</a>',
					esc_url( add_query_arg( $posts_in_term_qv, 'edit.php' ) ),
					esc_html( sanitize_term_field( 'name', $categories[$id]->name, $id, 'category', 'display' ) )
				);
			}
			echo join( __(', '), $out );
		} else {
			echo '&#8212;';
		}
}

	function extra_tablenav( $which ) {
		if ($which == 'top') {
?>
<div class="table-nav-filter alignleft actions">
<form method="post">
    <input type="hidden" name="list" value="CourseListTable" />
<?php
	if (!( empty( $_REQUEST['type'] ) && !$this->has_items() )) {
			$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'all';
?>
	<select name="type">
		<option value="all" <?php echo $type == 'all' ? 'selected' : ''; ?>>All Types</option>
		<option value="online" <?php echo $type == 'online' ? 'selected' : ''; ?>>Online</option>
		<option value="live" <?php echo $type == 'live' ? 'selected' : ''; ?>>Live</option>
	</select>
<?php
	}
	if (!( empty( $_REQUEST['cat'] ) && !$this->has_items() )) {
			//if ( is_object_in_taxonomy( ttp_lms_prefix(\TheTrainingMangerLMS\Constants::CoursePostType), 'category' ) ) {
				$cat = isset($_REQUEST['cat']) ? $_REQUEST['cat'] : 0;
				$dropdown_options = array(
					'show_option_all' => __( 'All Categories' ),
					'hide_empty' => 1,
					'hierarchical' => 1,
					'show_count' => 0,
					'orderby' => 'name',
					'selected' => $cat
				);
				wp_dropdown_categories( $dropdown_options );
	}
			//}
     $this->search_box('Filter', 'title'); ?>
</form>
</div>
<?php
		}
	}

	public function ajax_user_can() {
    	check_ajax_referer( get_called_class(), 'nonce' );
//    	check_ajax_referer( get_called_class(), '_ajax_' . ($singular ? $singular . '_' : '') . 'nonce' );
	}

	public function ajax_nonce_field() {
		wp_nonce_field( get_called_class(), '_ajax_nonce', false);
	}

	private static function get_edit_url( $course_id ) {
		if (is_admin()) {
			$edit_url = admin_url('admin.php?page=' . ttp_lms_prefix(\TheTrainingMangerLMS\Admin\CourseBuilder::MENU_HANDLE) . '&action=edit&course_id=' . $course_id);
		} else {
			$edit_url = "";
		}

		return $edit_url;
	}
}