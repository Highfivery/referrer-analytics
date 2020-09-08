<?php
/**
 * Referrer Analytics log table
 *
 * @package ReferrerAnalytics
 * @since 1.0.0
 */

// Security Note: Blocks direct access to the plugin PHP files.
defined( 'ABSPATH' ) || die();

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Log table class.
 */
class Referrer_Analytics_Log_Table extends WP_List_Table {
	/**
	 * Class constructor.
	 */
	public function __construct() {
		global $status, $page;

		$args = array(
			'singular' => __( 'Referrer Log Entry', 'referrer-analytics' ),
			'plural'   => __( 'Referrer Log Entries', 'referrer-analytics' ),
		);

		parent::__construct( $args );
	}

	/**
	 * Register table columns.
	 */
	public function get_columns() {
		$columns = array(
			'cb'              => '<input type="checkbox" />',
			'date_recorded'   => __( 'Date', 'referrer-analytics' ),
			'visitor_ip'      => __( 'IP', 'referrer-analytics' ),
			'user_id'         => __( 'User', 'referrer-analytics' ),
			'referrer_url'    => __( 'Referring URL', 'referrer-analytics' ),
			'url_destination' => __( 'URL Destination', 'referrer-analytics' ),
			'referrer_host'   => __( 'Referrer Host', 'referrer-analytics' ),
			'referrer_type'   => __( 'Referrer Type', 'referrer-analytics' ),
			'referrer_name'   => __( 'Referrer Name', 'referrer-analytics' ),
		);

		return $columns;
	}

	/**
	 * Define sortable columns.
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'date_recorded'   => array( 'date_recorded', false ),
			'visitor_ip'      => array( 'visitor_ip', false ),
			'user_id'         => array( 'user_id', false ),
			'referrer_url'    => array( 'referrer_url', false ),
			'url_destination' => array( 'url_destination', false ),
			'referrer_host'   => array( 'referrer_host', false ),
			'referrer_type'   => array( 'referrer_type', false ),
			'referrer_name'   => array( 'referrer_name', false ),
		);

		return $sortable_columns;
	}

	/**
	 * Checkbox column.
	 *
	 * @param array $item Item from the table.
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ 'ids',
			/*$2%s*/ $item['referrer_id']
		);
	}

	/**
	 * Render the column value.
	 *
	 * @param array  $item Item from the table.
	 * @param string $column_name Column key.
	 */
	public function column_default( $item, $column_name ) {
		$value = 'N/A';

		switch ( $column_name ) {
			case 'visitor_ip':
				if ( rest_is_ip_address( $item['visitor_ip'] ) ) {
					$value = '<a href="https://whatismyipaddress.com/ip/' . $item['visitor_ip'] . '" target="_blank" rel="noopener noreferrer">' . $item['visitor_ip'] . '</a>';
				}
				break;
			case 'date_recorded':
				$value = gmdate( 'M j, Y g:ia', strtotime( $item['date_recorded'] ) );
				break;
			case 'user_id':
				if ( ! empty( $item['user_id'] ) ) {
					$user = get_user_by( 'ID', $item['user_id'] );
					if ( $user ) {
						$value = '<a href="' . get_edit_user_link( $user->ID ) . '">' . $user->display_name . ' (' . $user->ID . ')</a>';
					}
				}
				break;
			case 'referrer_url':
				if ( ! empty( $item['referrer_url'] ) ) {
					$value = '<a href="' . esc_url( $item['referrer_url'] ) . '" target="_blank" rel="noopener noreferrer">' . $item['referrer_url'] . '</a>';
				}
				break;
			case 'url_destination':
				if ( ! empty( $item['url_destination'] ) ) {
					$value = '<a href="' . esc_url( $item['url_destination'] ) . '" target="_blank" rel="noopener noreferrer">' . $item['url_destination'] . '</a>';
				}
				break;
			case 'referrer_host':
				if ( ! empty( $item['referrer_host'] ) ) {
					$value = $item['referrer_host'];
				}
				break;
			case 'referrer_type':
				if ( ! empty( $item['referrer_type'] ) ) {
					$value = $item['referrer_type'];
				}
				break;
			case 'referrer_name':
				if ( ! empty( $item['referrer_name'] ) ) {
					$value = $item['referrer_name'];
				}
				break;
		}

		return $value;
	}

	/**
	 * Register bulk actions.
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete'     => __( 'Delete Selected', 'referrer-analytics' ),
			'delete_all' => __( 'Delete All', 'referrer-analytics' ),
		);

		return $actions;
	}

	/**
	 * Define which columns are hidden
	 */
	public function get_hidden_columns() {
		return array();
	}

	/**
	 * Get log entries.
	 */
	public function prepare_items() {
		global $referrer_analytics;

		$this->process_bulk_action();

		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$per_page     = 50;
		$current_page = $this->get_pagenum();
		$offset       = $per_page * ( $current_page - 1 );
		$order        = ! empty( $_REQUEST['order'] ) ? sanitize_text_field( $_REQUEST['order'] ) : 'desc'; // phpcs:ignore
		$orderby      = ! empty( $_REQUEST['orderby'] ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'date_recorded'; // phpcs:ignore

		$query_args = array(
			'limit'   => $per_page,
			'offset'  => $offset,
			'order'   => $order,
			'orderby' => $orderby,
		);
		$data       = $referrer_analytics->get_log( $query_args );
		if ( ! $data ) {
			return false;
		}

		$total_items = $referrer_analytics->get_log(
			array(
				'select' => array(
					'COUNT(referrer_id)',
				),
			),
			true
		);

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
				'orderby'     => $orderby,
				'order'       => $order,
			)
		);

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $data;
	}

	/**
	 * Process bulk actions.
	 */
	public function process_bulk_action() {
		global $wpdb;

		$nonce = ( isset( $_POST['referrer_analytics_nonce'] ) ) ? $_POST['referrer_analytics_nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'referrer_analytics_nonce' ) ) {
			return false;
		}

		$ids        = ( isset( $_REQUEST['ids'] ) ) ? $_REQUEST['ids'] : ''; // phpcs:ignore
		$table_name = $wpdb->prefix . 'referrer_analytics';

		switch ( $this->current_action() ) {
			// Delete item.
			case 'delete':
				if ( ! empty( $ids ) && is_array( $ids ) ) {
					foreach ( $ids as $k => $referrer_id ) {
						$wpdb->delete( $table_name, array( 'referrer_id' => $referrer_id  ) ); // phpcs:ignore
					}
				}
				break;
			case 'delete_all':
				$wpdb->query( "TRUNCATE TABLE $table_name" ); // phpcs:ignore
				break;
		}
	}
}
