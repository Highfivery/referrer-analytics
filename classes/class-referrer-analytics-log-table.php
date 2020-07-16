<?php
/**
 * Referrer Analytics log table
 *
 * @package ReferrerAnalytics
 * @since 1.0.0
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class ReferrerAnalytics_Log_Table extends WP_List_Table {
  function __construct() {
    global $status, $page;

    $args = [
      'singular'  => __( 'Referrer Log Entry', 'referreranalytics' ),
      'plural'    => __( 'Referrer Log Entries', 'referreranalytics' ),
      'ajax'      => true
    ];
    parent::__construct( $args );
  }

  // Register columns
  function get_columns() {
    // Render a checkbox instead of text
    $columns = [
      'cb'              => '<input type="checkbox" />',
      'date_recorded'   => __( 'Date', 'referreranalytics' ),
      'visitor_ip'      => __( 'IP', 'referreranalytics' ),
      'user_id'         => __( 'User', 'referreranalytics' ),
      'referrer_url'    => __( 'Referring URL', 'referreranalytics' ),
      'url_destination' => __( 'URL Destination', 'referreranalytics' ),
      'referrer_host'   => __( 'Referrer Host', 'referreranalytics' ),
      'referrer_type'   => __( 'Referrer Type', 'referreranalytics' ),
      'referrer_name'   => __( 'Referrer Name', 'referreranalytics' ),
    ];

    return $columns;
  }

  // Sortable columns
  function get_sortable_columns() {
    $sortable_columns = [
      'date_recorded'   => [ 'date_recorded', false ],
      'visitor_ip'      => [ 'visitor_ip', false ],
      'user_id'         => [ 'user_id', false ],
      'referrer_url'    => [ 'referrer_url', false ],
      'url_destination' => [ 'url_destination', false ],
      'referrer_host'   => [ 'referrer_host', false ],
      'referrer_type'   => [ 'referrer_type', false ],
      'referrer_name'   => [ 'referrer_name', false ],
    ];

    return $sortable_columns;
  }

  // Checkbox column
  function column_cb( $item ){
    return sprintf(
        '<input type="checkbox" name="%1$s[]" value="%2$s" />',
        /*$1%s*/ 'ids',
        /*$2%s*/ $item->referrer_id
    );
  }

  // Render column
  function column_default( $item, $column_name ) {
    switch( $column_name ) {
      case 'visitor_ip':
        if ( ! empty( $item->visitor_ip ) ) {
          return '<a href="https://whatismyipaddress.com/ip/' . $item->visitor_ip .'" target="_blank" rel="noopener noreferrer">' . $item->visitor_ip . '</a>';
        }

        return 'N/A';
      break;
      case 'date_recorded':
        return date( 'M j, Y g:ia' , strtotime( $item->date_recorded ) );
      break;
      case 'user_id':
        if ( ! empty( $item->user_id ) ) {
          $user = get_user_by( 'ID',  $item->user_id );
          if ( ! $user ) { return 'N/A'; }

          return '<a href="' . get_edit_user_link( $user->ID ) . '">' . $user->display_name . ' (' . $user->ID . ')</a>';
        }

        return 'N/A';
      break;
      case 'referrer_url':
        if ( ! empty( $item->referrer_url ) ) {
          return '<a href="' . esc_url( $item->referrer_url ) . '" target="_blank" rel="noopener noreferrer">' . $item->referrer_url . '</a>';
        }

        return 'N/A';
      break;
      case 'url_destination':
        if ( ! empty( $item->url_destination ) ) {
          return '<a href="' . esc_url( $item->url_destination ) . '" target="_blank" rel="noopener noreferrer">' . $item->url_destination . '</a>';
        }

        return 'N/A';
      break;
      case 'referrer_host':
        if ( ! empty( $item->referrer_host ) ) {
          return $item->referrer_host;
        }

        return 'N/A';
      break;
      case 'referrer_type':
        if ( ! empty( $item->referrer_type ) ) {
          return $item->referrer_type;
        }

        return 'N/A';
      break;
      case 'referrer_name':
        if ( ! empty( $item->referrer_name ) ) {
          return $item->referrer_name;
        }

        return 'N/A';
      break;
    }
  }

  // Register bulk actions
  function get_bulk_actions() {
    $actions = [ 'delete' => __( 'Delete', 'referreranalytics' ) ];

    return $actions;
  }

  // Get results
  function prepare_items($args = []) {
    $this->process_bulk_action();

    $columns  = $this->get_columns();
    $sortable = $this->get_sortable_columns();
    $hidden   = [];

    $this->_column_headers = [ $columns, $hidden, $sortable ];

    $current_page = $this->get_pagenum() ? $this->get_pagenum() : 1;
    $paged = ( isset( $_REQUEST['page'])) ? $_REQUEST['page'] : $current_page;
    $paged = ( isset( $_REQUEST['paged'])) ? $_REQUEST['paged'] : $current_page;
    $paged = ( isset( $args['paged'] ) ) ? $args['paged'] : $paged;

    $per_page = ( isset( $args['per_page'] ) ) ? $args['per_page'] : 20;
    $orderby  = ( isset( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'id';
    $order    = ( isset( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'desc';

    $data = referrer_analytics_get_log();

    // Set pagniation args
    $pagination_args = [
      'total_items' => count( $data ),
      'per_page'    => $per_page,
      'total_pages' => ceil( count( $data ) / $per_page ),
    ];
    $this->set_pagination_args( $pagination_args );

    $data = array_slice ( $data, ( ( $current_page - 1 ) * $per_page ),$per_page );
    $this->items = $data;
  }

  // Process bulk actions
  function process_bulk_action() {
    global $wpdb;

    $ids = ( isset( $_REQUEST['ids'] ) ) ? $_REQUEST['ids'] : '';

    switch( $this->current_action() ) {
      // Delete
      case 'delete':
        $nonce = ( isset( $_POST['referreranalytics_nonce'] ) ) ? $_POST['referreranalytics_nonce'] : '';
        if ( ! wp_verify_nonce( $nonce, 'referreranalytics_nonce' ) ) return false;

        if ( ! empty ( $ids ) ) {
          // Delete query
          foreach( $ids as $k => $referrer_id ) {
            $wpdb->delete( $wpdb->prefix . 'referrer_analytics', [ 'referrer_id' => $referrer_id  ] );
          }
        }
      break;
    }
  }
}