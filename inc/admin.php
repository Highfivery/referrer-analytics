<?php
/**
 * Admin functionality
 *
 * @package ReferrerAnalytics
 * @since 1.0.0
 */

function referrer_analytics_admin_menu() {
  $options = referrer_analytics_options();

  add_submenu_page( 'options-general.php', __( 'Referrer Analytics', 'referrer_analytics' ), __( 'Referrer Analytics', 'referrer_analytics' ), 'manage_options', 'referrer-analytics', 'referrer_analytics_options_page' );

  if ( 'enabled' === $options['logging'] ) {
    add_submenu_page( 'options-general.php', __( 'Referrer Analytics Log', 'referrer_analytics' ), __( 'Referrer Log & Stats', 'referrer_analytics' ), 'manage_options', 'referrer-analytics-log', 'referrer_analytics_log_page' );
  }
}
add_action( 'admin_menu', 'referrer_analytics_admin_menu' );

function referrer_analytics_options_page() {
  if ( ! current_user_can( 'manage_options' ) ) { return; }
   ?>
   <div class="wrap">
     <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
     <form action="options.php" method="post">
     <?php
     // Output security fields for the registered setting "referrer_analytics"
     settings_fields( 'referrer_analytics' );

     // Output setting sections and their fields
     do_settings_sections( 'referrer_analytics' );

     // Output save settings button
     submit_button( 'Save Settings' );
     ?>
     </form>
   </div>
   <?php
 }

 function referrer_analytics_log_page() {
  if ( ! current_user_can( 'manage_options' ) ) { return; }

  if ( ! empty( $_REQUEST['delete'] ) && 'log' === $_REQUEST['delete'] ) {
    referrer_analytics_delete_log();
    wp_redirect( admin_url( 'options-general.php?page=referrer-analytics-log' ) );
    exit();
  }

  referrer_analytics_sync_log();

  $log = referrer_analytics_get_log();
  $log = array_reverse( $log );

  $known  = referrer_analytics_get_known();
  $parsed = referrer_analytics_parsed_log( $log );

  $log_limit      = 20;
  $log_size       = referrer_analytics_log_size();
  $total_pages    = ceil( count( $log  ) / $log_limit );
  $current_page   = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
  $starting_index = ( $current_page * $log_limit ) - $log_limit + 1;
  $ending_index   = ( $current_page * $log_limit );
  ?>
  <div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <?php if ( $current_page === 1 ): ?>
      <h2><?php _e( 'Statistics', 'referrer-analytics' ); ?></h2>
      <div class="referrer-analytics-boxes">
        <div class="referrer-analytics-box">
          <h3><?php _e( 'Referrers', 'referrer-analytics' ); ?></h3>
          <div class="inside">
            <canvas id="referrer-analytics-pie-referrers"></canvas>
          </div>
        </div>
        <div class="referrer-analytics-box">
          <h3><?php _e( 'Referrer Types', 'referrer-analytics' ); ?></h3>
          <div class="inside">
            <canvas id="referrer-analytics-pie-types"></canvas>
          </div>
        </div>
      </div>
      <script>
      // Referrers
      var referrers = document.getElementById('referrer-analytics-pie-referrers');
      var referrerAnalyticsPie = new Chart(referrers, {
        type: 'pie',
        data: {
          labels: <?php echo json_encode( $parsed['charts']['referrers']['labels'] ); ?>,
          datasets: [{
            data: [<?php echo implode( ',', $parsed['charts']['referrers']['data'] ); ?>],
            backgroundColor: <?php echo json_encode( $parsed['charts']['referrers']['colors'] ); ?>,
            borderWidth: 2,
            borderColor: '#f1f1f1'
          }],
        },
        options: {
          legend: {
            position: 'left',
            fullWidth: false
          }
        }
      });

      // Types
      var types = document.getElementById('referrer-analytics-pie-types');
      var referrerAnalyticsPie = new Chart(types, {
        type: 'pie',
        data: {
          labels: <?php echo json_encode( $parsed['charts']['type']['labels'] ); ?>,
          datasets: [{
            data: [<?php echo implode( ',', $parsed['charts']['type']['data'] ); ?>],
            backgroundColor: <?php echo json_encode( $parsed['charts']['type']['colors'] ); ?>,
            borderWidth: 2,
            borderColor: '#f1f1f1'
          }],
        },
        options: {
          legend: {
            position: 'left',
            fullWidth: false
          }
        }
      });
      </script>
    <?php endif; ?>

    <h2><?php _e( 'Referrer Log', 'referrer-analytics' ); ?> <?php if ( $log_size ): ?><span style="font-size: 0.8rem; font-weight: normal; color: #666;">(<?php echo $log_size; ?>)</span><?php endif ?></h2>

    <div class="referrer-analytics-log-table-header">
      <div class="referrer-analytics-log-table-header-headline">
        <?php _e( 'Showing', 'referrer-analytics' ); ?> <?php echo $starting_index; ?> - <?php echo $ending_index; ?> <?php _e( 'of', 'referrer-analytics' ); ?> <?php echo count( $log  ); ?>
      </div>
      <div class="referrer-analytics-log-table-header-actions">
        <a href="<?php echo admin_url( 'options-general.php?page=referrer-analytics-log&delete=log' ); ?>" class="button button-primary"><?php _e( 'Delete Log', 'referrer-analytics' ); ?></a>
      </div>
    </div>

    <table class="widefat fixed referrer-analytics-log-table">
      <tr>
        <thead>
          <th><?php _e( 'Date', 'referrer-analytics' ); ?></th>
          <th>IP</th>
          <th><?php _e( 'User', 'referrer-analytics' ); ?></th>
          <th><?php _e( 'Referring URL', 'referrer-analytics' ); ?></th>
          <th><?php _e( 'Destination', 'referrer-analytics' ); ?></th>
          <th><?php _e( 'Raw Host', 'referrer-analytics' ); ?></th>
          <th><?php _e( 'Host', 'referrer-analytics' ); ?></th>
          <th><?php _e( 'Type', 'referrer-analytics' ); ?></th>
          <th><?php _e( 'Name', 'referrer-analytics' ); ?></th>
        </thead>
      </tr>
      <?php
      $cnt = 0;
      foreach( $log as $key => $entry ):
        $cnt++;

        if ( $cnt < $starting_index ) { continue; }
        if ( $cnt > $ending_index ) { break; }
        ?>
        <tr>
          <td><?php echo date( 'm/j/y g:i:s', strtotime( $entry['date' ] ) ); ?></td>
          <td>
            <?php if ( ! empty( $entry['ip'] ) ): ?>
              <a href="https://whatismyipaddress.com/ip/<?php echo $entry['ip']; ?>" target="_blank" rel="noopener noreferrer"><?php echo $entry['ip']; ?></a>
            <?php else: ?>
              N/A
            <?php endif; ?>
          </td>
          <td>
            <?php if ( ! empty( $entry['userid'] ) ): ?>
              <?php $user = get_user_by( 'ID', $entry['userid'] ); ?>
              <a href="<?php echo get_edit_user_link( $user->ID ); ?>"><?php echo $user->display_name; ?> (<?php echo $user->ID; ?>)</a>
            <?php else: ?>
              N/A
            <?php endif; ?>
          </td>
          <td>
            <?php if ( ! empty( $entry['url'] ) ): ?>
              <a href="<?php echo esc_url( $entry['url' ] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo $entry['url']; ?></a>
            <?php else: ?>
              N/A
            <?php endif; ?>
          </td>
          <td>
            <?php if ( ! empty( $entry['destination'] ) ): ?>
              <a href="<?php echo esc_url( $entry['destination' ] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo $entry['destination']; ?></a>
            <?php else: ?>
              N/A
            <?php endif; ?>
          </td>
          <td>
            <?php if ( ! empty( $entry['raw'] ) ): ?>
              <?php echo $entry['raw']; ?>
            <?php else: ?>
              N/A
            <?php endif; ?>
          </td>
          <td>
            <?php if ( ! empty( $entry['host'] ) ): ?>
              <?php echo $entry['host']; ?>
            <?php else: ?>
              N/A
            <?php endif; ?>
          </td>
          <td>
            <?php if ( ! empty( $entry['type'] ) ): ?>
              <?php echo $entry['type']; ?>
            <?php else: ?>
              N/A
            <?php endif; ?>
          </td>
          <td>
            <?php if ( ! empty( $entry['name'] ) ): ?>
              <?php echo $entry['name']; ?>
            <?php else: ?>
              N/A
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>

    <div class="referrer-analytics-pagination">
      <?php echo paginate_links([
        'base'      => add_query_arg( 'pagenum', '%#%' ),
        'total'     => $total_pages,
        'current'   => $current_page,
        'next_text' => '»',
        'prev_text' => '«'
      ]); ?>
    </div>
  <?php
 }

 function referrer_analytics_admin_init() {
  register_setting( 'referrer_analytics', 'referrer_analytics_options' );

  add_settings_section( 'referrer_analytics_general_settings', __( 'General Settings', 'referrer_analytics' ), 'referrer_analytics_general_settings_cb', 'referrer_analytics' );
  add_settings_section( 'referrer_analytics_referrer_host_settings', __( 'Referrer Host Settings', 'referrer_analytics' ), 'referrer_analytics_referrer_host_settings_cb', 'referrer_analytics' );
  add_settings_section( 'referrer_analytics_ga_settings', __( 'Google Analytics Settings', 'referrer_analytics' ), 'referrer_analytics_ga_settings_cb', 'referrer_analytics' );

  // Redirect with UTM
  add_settings_field( 'redirect_with_utm', __( 'Redirect with UTM Data', 'referrer_analytics' ), 'referrer_analytics_field_cb', 'referrer_analytics', 'referrer_analytics_ga_settings', [
    'label_for' => 'redirect_with_utm',
    'type'      => 'checkbox',
    'multi'     => false,
    'class'     => 'regular-text',
    'desc'      => 'When users come from another site, redirect & append the page their visting with the referring <a href="https://support.google.com/analytics/answer/1033863?hl=en" target="_blank" rel="noopener noreferrer">UTM data</a>.',
    'options'   => [
      'enabled' => __( 'Enabled', 'referrer_analytics' )
    ]
  ]);

  // Defined Hosts
  add_settings_field( 'hosts', __( 'Defined Referrer Hosts', 'referrer_analytics' ), 'referrer_analytics_referrers_cb', 'referrer_analytics', 'referrer_analytics_referrer_host_settings' );

  // Track all Referrers
  add_settings_field( 'track_all_referrers', __( 'Track all Referrers', 'referrer_analytics' ), 'referrer_analytics_field_cb', 'referrer_analytics', 'referrer_analytics_referrer_host_settings', [
    'label_for' => 'track_all_referrers',
    'type'      => 'checkbox',
    'multi'     => false,
    'class'     => 'regular-text',
    'desc'      => 'If a user comes from a host that\'s not defined above, track it using the raw data.<br />Referrer Analytics will attempt to set \'Host\', \'Name\' & \'Type\' from it\'s list of known hosts.<br />If unable to locate, \'Host\' and \'Name\' will be the hostname of the referrer and \'Type\' will default to "backlink".',
    'options'   => [
      'enabled' => __( 'Enabled', 'referrer_analytics' )
    ]
  ]);

  // Store Cookies
  add_settings_field( 'store_cookies', __( 'Store Cookies', 'referrer_analytics' ), 'referrer_analytics_field_cb', 'referrer_analytics', 'referrer_analytics_general_settings', [
    'label_for' => 'store_cookies',
    'type'      => 'checkbox',
    'multi'     => false,
    'class'     => 'regular-text',
    'desc'      => 'Stores referrer host data in cookies that can be used for 3rd-party applications.',
    'options'   => [
      'enabled' => __( 'Enabled', 'referrer_analytics' )
    ]
  ]);

  // Cookie Expiration
  add_settings_field( 'cookie_expiration', __( 'Cookie Expiration', 'referrer_analytics' ), 'referrer_analytics_field_cb', 'referrer_analytics', 'referrer_analytics_general_settings', [
    'label_for' => 'cookie_expiration',
    'type'      => 'number',
    'class'     => 'small-text',
    'suffix'    => 'days',
    'desc'      => 'Number of days the cookie will be stored on the user\'s computer.',
  ]);

  // Store Cookies
  add_settings_field( 'logging', __( 'Logging & Statistics', 'referrer_analytics' ), 'referrer_analytics_field_cb', 'referrer_analytics', 'referrer_analytics_general_settings', [
    'label_for' => 'logging',
    'type'      => 'checkbox',
    'multi'     => false,
    'class'     => 'regular-text',
    'desc'      => 'Enables logging of user referrers and provides an admin interface to view statistics.',
    'options'   => [
      'enabled' => __( 'Enabled', 'referrer_analytics' )
    ]
  ]);

  // UTM Source
  add_settings_field( 'utm_source', __( 'UTM Source', 'referrer_analytics' ), 'referrer_analytics_field_cb', 'referrer_analytics', 'referrer_analytics_ga_settings', [
    'label_for' => 'utm_source',
    'type'      => 'select',
    'desc'      => 'Select the value that should be used for the <code>utm_source</code>.',
    'options'   => [
      'ignore' => __( 'Ignore', 'referrer_analytics' ),
      'host'   => __( 'Host', 'referrer_analytics' ),
      'type'   => __( 'Type', 'referrer_analytics' ),
      'name'   => __( 'Name', 'referrer_analytics' )
    ]
  ]);

  // UTM Medium
  add_settings_field( 'utm_medium', __( 'UTM Medium', 'referrer_analytics' ), 'referrer_analytics_field_cb', 'referrer_analytics', 'referrer_analytics_ga_settings', [
    'label_for' => 'utm_medium',
    'type'      => 'select',
    'desc'      => 'Select the value that should be used for the <code>utm_medium</code>.',
    'options'   => [
      'ignore' => __( 'Ignore', 'referrer_analytics' ),
      'host'   => __( 'Host', 'referrer_analytics' ),
      'type'   => __( 'Type', 'referrer_analytics' ),
      'name'   => __( 'Name', 'referrer_analytics' )
    ]
  ]);

  // UTM Campaign
  add_settings_field( 'utm_campaign', __( 'UTM Campaign', 'referrer_analytics' ), 'referrer_analytics_field_cb', 'referrer_analytics', 'referrer_analytics_ga_settings', [
    'label_for' => 'utm_campaign',
    'type'      => 'select',
    'desc'      => 'Select the value that should be used for the <code>utm_campaign</code>.',
    'options'   => [
      'ignore' => __( 'Ignore', 'referrer_analytics' ),
      'host'   => __( 'Host', 'referrer_analytics' ),
      'type'   => __( 'Type', 'referrer_analytics' ),
      'name'   => __( 'Name', 'referrer_analytics' )
    ]
  ]);
}
add_action( 'admin_init', 'referrer_analytics_admin_init' );

function referrer_analytics_general_settings_cb() {
}

function referrer_analytics_referrer_host_settings_cb() {
}

function referrer_analytics_ga_settings_cb() {
}

function referrer_analytics_referrers_cb( $args ) {
  $options = get_option( 'referrer_analytics_options' );
  $key     = 0;
  ?>
  <div class="referrer-ananlytics-referrer-header">
    <div>
      <label><?php _e( 'Host', 'referrer_analytics' ); ?></label>
      <small><?php _e( 'The host name of the referrer (i.e. google.com, bing.com, etc.).', 'referrer_analytics' ); ?></small>
    </div>
    <div>
      <label><?php _e( 'Type', 'referrer_analytics' ); ?></label>
      <small><?php _e( 'Define a type for the referrer (i.e. organic, backlink, ppc, etc.).', 'referrer_analytics' ); ?></small>
    </div>
    <div>
      <label><?php _e( 'Name', 'referrer_analytics' ); ?></label>
      <small><?php _e( 'Human-readable name for the referrer (i.e. Google, Bing, Yahoo, etc.).', 'referrer_analytics' ); ?></small>
    </div>
  </div>
  <?php

  if ( $options['hosts'] ):
    foreach( $options['hosts'] as $key => $host ):
      if ( ! $host['host'] || ! $host['type'] || ! $host['name'] ) {
        unset( $options['referrers'][ $key ] );
        continue;
      }
      ?>
      <div class="referrer-analytics-referrer-option">
        <input
          type="text"
          name="referrer_analytics_options[hosts][<?php echo $key; ?>][host]"
          value="<?php echo trim( $host['host'] ); ?>"
          placeholder="<?php _e( 'Host (i.e. google.com)', 'referrer_analytics' ); ?>"
          class="referrer-analytics-input"
        />

        <input
          type="text"
          name="referrer_analytics_options[hosts][<?php echo $key; ?>][type]"
          value="<?php echo trim( $host['type'] ); ?>"
          placeholder="<?php _e( 'Type (i.e. organic)', 'referrer_analytics' ); ?>"
          class="referrer-analytics-input"
        />

        <input
          type="text"
          name="referrer_analytics_options[hosts][<?php echo $key; ?>][name]"
          value="<?php echo trim( $host['name'] ); ?>"
          placeholder="<?php _e( 'Name (i.e. Google)', 'referrer_analytics' ); ?>"
          class="referrer-analytics-input"
        />
      </div>
      <?php
    endforeach;
  endif;

  $key++;
  ?>
  <div class="referrer-analytics-referrer-option">
    <input
      type="text"
      name="referrer_analytics_options[hosts][<?php echo $key; ?>][host]"
      value=""
      placeholder="<?php _e( 'Host (i.e. google.com)', 'referrer_analytics' ); ?>"
      class="referrer-analytics-input"
    />

    <input
      type="text"
      name="referrer_analytics_options[hosts][<?php echo $key; ?>][type]"
      value=""
      placeholder="<?php _e( 'Type (i.e. organic)', 'referrer_analytics' ); ?>"
      class="referrer-analytics-input"
    />

    <input
      type="text"
      name="referrer_analytics_options[hosts][<?php echo $key; ?>][name]"
      value=""
      placeholder="<?php _e( 'Name (i.e. Google)', 'referrer_analytics' ); ?>"
      class="referrer-analytics-input"
    />
  </div>
  <?php
}

function referrer_analytics_field_cb( $args ) {
  $options = referrer_analytics_options();

  switch( $args['type'] ) {
    case 'url':
    case 'text':
    case 'password':
    case 'number':
    case 'email':
      ?>
      <input class="<?php echo $args['class']; ?>" type="<?php echo $args['type']; ?>" value="<?php if ( ! empty( $options[ $args['label_for'] ] ) ): echo esc_attr( $options[ $args['label_for'] ] ); endif; ?>" placeholder="" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="referrer_analytics_options[<?php echo esc_attr( $args['label_for'] ); ?>]"><?php if ( ! empty( $args['suffix'] ) ): echo ' ' . $args['suffix']; endif; ?>
      <p class="description"><?php echo $args['desc'] ?></p>
      <?php
    break;
    case 'textarea':
      ?>
      <textarea rows="10" class="<?php echo $args['class']; ?>" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="referrer_analytics_options[<?php echo esc_attr( $args['label_for'] ); ?>]"><?php if ( ! empty( $options[ $args['label_for'] ] ) ): echo esc_attr( $options[ $args['label_for'] ] ); endif; ?></textarea>
      <p class="description"><?php echo $args['desc'] ?></p>
      <?php
    break;
    case 'select':
      ?>
      <select name="referrer_analytics_options[<?php echo esc_attr( $args['label_for'] ); ?>]" id="<?php echo esc_attr( $args['label_for'] ); ?>">
        <?php foreach( $args['options'] as $key => $label ): ?>
          <option value="<?php echo $key; ?>"<?php if ( $key === $options[ $args['label_for'] ] ): ?> selected="selected"<?php endif; ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
      </select>
      <p class="description"><?php echo $args['desc'] ?></p>
      <?php
    break;
    case 'checkbox':
      ?>
      <?php foreach( $args['options'] as $key => $label ): ?>
        <label for="<?php echo esc_attr( $args['label_for'] . $key ); ?>">
          <input
            type="checkbox"
            class="<?php echo $args['class']; ?>"
            id="<?php echo esc_attr( $args['label_for'] . $key ); ?>"
            name="referrer_analytics_options[<?php echo esc_attr( $args['label_for'] ); ?>]<?php if( $args['multi'] ): ?>[<?php echo $key; ?>]<?php endif; ?>" value="<?php echo $key; ?>"
            <?php if( $mutli && $key === $options[ $args['label_for'] ][ $key ] || ! $multi && $key === $options[ $args['label_for'] ] ): ?> checked="checked"<?php endif; ?> /> <?php echo $label; ?>
        </label>
      <?php endforeach; ?>
      <p class="description"><?php echo $args['desc'] ?></p>
      <?php
    break;
  }
  ?>
  <?php
}
