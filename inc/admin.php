<?php
/**
 * Admin functionality
 *
 * @package ReferrerAnalytics
 * @since 1.0.0
 */

function referrer_analytics_admin_menu() {
  $options = referrer_analytics_options();

  $main_page = add_menu_page(
    __( 'Referrer Analytics Dashboard', 'wpzerospam' ),
    __( 'Referrer Analytics', 'wpzerospam' ),
    'manage_options',
    'referrer-analytics',
    'referrer_analytics_options_page',
    'dashicons-chart-area'
  );

  if ( 'enabled' === $options['logging'] ) {
    add_submenu_page(
      'referrer-analytics',
      __( 'Referrer Analytics Log', 'wpzerospam' ),
      __( 'Referrer Log &amp; Stats', 'wpzerospam' ),
      'manage_options',
      'referrer-analytics-log',
      'referrer_analytics_log_page'
    );
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

  referrer_analytics_sync_log();

  // Get referrers data
  $log             = referrer_analytics_get_log();
  $parsed_log      = referrer_analytics_parse_log( $log );
  $known_referrers = referrer_analytics_referrers();

  // Paging variables
  $chart_limit        = 15;
  $current_page       = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
  $referrer_totals    = $parsed_log['referrers'];
  $type_totals        = $parsed_log['types'];
  $destination_totals = $parsed_log['destinations'];

  // Sort the results
  if ( $referrer_totals ) {
    usort($referrer_totals, function($a, $b) {
      return $b['count'] <=> $a['count'];
    });
  }

  if ( $type_totals ) {
    arsort( $type_totals );
  }

  if ( $destination_totals ) {
    arsort( $destination_totals );
  }

  $predefined_colors = [
    '#1d3557', '#457b9d', '#a8dadc', '#f1faee', '#e63946', '#e76f51', '#f4a261', '#e9c46a', '#2a9d8f', '#264653', '#e29578', '#ffddd2', '#8d99ae', '#6d597a', '#dddf00', '#f15bb5'
  ];
  ?>
  <div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <?php if ( $current_page === 1 ): ?>
      <h2><?php _e( 'Statistics', 'referrer-analytics' ); ?></h2>
      <div class="referrer-analytics-boxes">
        <div class="referrer-analytics-box referrer-analytics-box-top-referrers">
          <h3><?php _e( 'Top 10 Referrers', 'referrer-analytics' ); ?></h3>
          <div class="inside">
            <?php if ( $referrer_totals ): ?>
              <ol>
                <?php
                $cnt = 0;
                foreach( $referrer_totals as $key => $entry ):
                  $cnt++;
                  if ( $cnt > 10 ) { break; }
                  ?>
                  <li>
                    <?php if ( ! empty( $entry['primary_url'] ) ): ?><a href="<?php echo esc_url( $entry['primary_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php endif; ?>
                      <strong><?php echo $entry['name']; ?></strong>
                    <?php if ( ! empty( $entry['primary_url'] ) ): ?></a><?php endif; ?> &mdash; <?php echo $entry['count']; ?>
                    <?php if ( ! empty( $entry['type'] ) ): ?>
                      (<?php echo $entry['type']; ?>)
                    <?php endif; ?>
                    <?php if ( ! empty( $entry['flag'] ) && $entry['flag'] ): ?>
                      <span style="color: #ca4a1f;"><?php _e( 'potentially malicious, consider blocking', 'referrer-analytics' ); ?></span>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ol>
            <?php else: ?>
              <?php _e( 'No data to report yet.', 'referrer-analytics' ); ?>
            <?php endif; ?>
          </div>
        </div>
        <div class="referrer-analytics-box referrer-analytics-box-top-types">
          <h3><?php _e( 'Top 10 Referrer Types', 'referrer-analytics' ); ?></h3>
          <div class="inside">
            <?php if ( $type_totals ): ?>
              <ol>
                <?php
                $cnt = 0;
                foreach( $type_totals as $type => $count ):
                  $cnt++;
                  if ( $cnt > 10 ) { break; }
                  ?>
                  <li><strong><?php echo $type; ?></strong> &mdash; <?php echo $count; ?></li>
                <?php endforeach; ?>
              </ol>
            <?php else: ?>
              <?php _e( 'No data to report yet.', 'referrer-analytics' ); ?>
            <?php endif; ?>
          </div>
        </div>
        <div class="referrer-analytics-box referrer-analytics-box-top-types">
          <h3><?php _e( 'Top 10 Referred Destinations', 'referrer-analytics' ); ?></h3>
          <div class="inside">
            <?php if ( $destination_totals ): ?>
              <ol>
                <?php
                $cnt = 0;
                foreach( $destination_totals as $url => $count ):
                  $cnt++;
                  if ( $cnt > 10 ) { break; }
                  ?>
                  <li><strong><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo str_replace( site_url(), '', $url ); ?></a></strong> &mdash; <?php echo $count; ?></li>
                <?php endforeach; ?>
              </ol>
            <?php else: ?>
              <?php _e( 'No data to report yet.', 'referrer-analytics' ); ?>
            <?php endif; ?>
          </div>
        </div>
        <div class="referrer-analytics-box referrer-analytics-box-referrers-pie">
          <h3><?php _e( 'Top ' . $chart_limit . ' Referrers', 'referrer-analytics' ); ?></h3>
          <div class="inside">
            <?php if ( $referrer_totals ): ?>
              <canvas id="referrer-analytics-pie-referrers"></canvas>
              <script>
              <?php
              $labels = [];
              $data   = [];
              $count  = 0;
              foreach( $referrer_totals as $key => $value ):
                $count++;
                if ( $count > $chart_limit ): break; endif;

                $labels[] = $value['name'];
                $data[]   = $value['count'];
                $colors[] = $predefined_colors[ $count ];
              endforeach;
              ?>
              var referrers = document.getElementById('referrer-analytics-pie-referrers');
              var referrerAnalyticsPie = new Chart(referrers, {
                type: 'pie',
                data: {
                  labels: <?php echo json_encode( $labels ); ?>,
                  datasets: [{
                    data: <?php echo json_encode( $data ); ?>,
                    backgroundColor: <?php echo json_encode( $colors ); ?>,
                    borderWidth: 0,
                    borderColor: '#f1f1f1'
                  }],
                },
                options: {
                  legend: {
                    position: 'right',
                    fullWidth: false
                  }
                }
              });
              </script>
            <?php else: ?>
              <?php _e( 'No data to report yet.', 'referrer-analytics' ); ?>
            <?php endif; ?>
          </div>
        </div>
        <div class="referrer-analytics-box referrer-analytics-box-type-pie">
          <h3><?php _e( 'Top ' . $chart_limit . ' Referrer Types', 'referrer-analytics' ); ?></h3>
          <div class="inside">
            <?php if ( $type_totals ): ?>
              <canvas id="referrer-analytics-pie-types"></canvas>
              <script>
              <?php
              $labels = [];
              $data   = [];
              $count  = 0;
              foreach( $type_totals as $key => $value ):
                $count++;
                if ( $count > $chart_limit ): break; endif;

                $labels[] = $key;
                $data[]   = $value;
                $colors[] = $predefined_colors[ $count ];
              endforeach;
              ?>
              var types = document.getElementById('referrer-analytics-pie-types');
              var referrerAnalyticsPie = new Chart(types, {
                type: 'pie',
                data: {
                  labels: <?php echo json_encode( $labels ); ?>,
                  datasets: [{
                    data: <?php echo json_encode( $data ); ?>,
                    backgroundColor: <?php echo json_encode( $colors ); ?>,
                    borderWidth: 0,
                    borderColor: '#f1f1f1'
                  }],
                },
                options: {
                  legend: {
                    position: 'right',
                    fullWidth: false
                  }
                }
              });
              </script>
            <?php else: ?>
              <?php _e( 'No data to report yet.', 'referrer-analytics' ); ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <h2><?php _e( 'Referrer Log', 'referrer-analytics' ); ?></h2>

    <a href="<?php echo admin_url( 'options-general.php?page=referrer-analytics-log&delete=log' ); ?>" style="float: right" class="button button-primary"><?php _e( 'Delete All Log Entries', 'referrer-analytics' ); ?></a>

    <?php
    /**
     * Log table
     */
    require plugin_dir_path( REFERRER_ANALYTICS ) . '/classes/class-referrer-analytics-log-table.php';

    $table_data = new ReferrerAnalytics_Log_Table();

    // Setup page parameters
    $current_page = $table_data->get_pagenum();
    $current_page = (isset($current_page)) ? $current_page : 1;
    $paged        = (isset($_GET['page'])) ? $_GET['page'] : $current_page;
    $paged        = (isset($_GET['paged'])) ? $_GET['paged'] : $current_page;
    $paged        = (isset($args['paged'])) ? $args['paged'] : $paged;

    // Fetch, prepare, sort, and filter our data...
    $table_data->prepare_items();
    ?>
    <form id="log-table" method="post">
      <?php wp_nonce_field( 'referreranalytics_nonce', 'referreranalytics_nonce' ); ?>

      <?php # Current page ?>
      <input type="hidden" name="paged" value="<?php echo $paged; ?>" />

      <?php $table_data->display(); ?>
    </form>
  <?php
 }

 function referrer_analytics_validate_options( $input ) {
  if ( empty( $input['logging'] ) ) { $input['logging'] = 'disabled'; }
  if ( empty( $input['redirect_with_utm'] ) ) { $input['redirect_with_utm'] = 'disabled'; }
  if ( empty( $input['track_all_referrers'] ) ) { $input['track_all_referrers'] = 'disabled'; }
  if ( empty( $input['url_referrer_fallback'] ) ) { $input['url_referrer_fallback'] = 'disabled'; }
  if ( empty( $input['store_cookies'] ) ) { $input['store_cookies'] = 'disabled'; }
  if ( empty( $input['disable_noreferrer'] ) ) { $input['disable_noreferrer'] = 'disabled'; }

  return $input;
 }

 function referrer_analytics_admin_init() {
  if ( ! empty( $_REQUEST['delete'] ) && 'log' === $_REQUEST['delete'] ) {
    referrer_analytics_delete_log();
    wp_redirect( admin_url( 'options-general.php?page=referrer-analytics-log' ) );
    exit();
  }

  $options = referrer_analytics_options();

  register_setting( 'referrer_analytics', 'referrer_analytics_options', 'referrer_analytics_validate_options' );

  add_settings_section( 'referrer_analytics_general_settings', __( 'General Settings', 'referrer_analytics' ), 'referrer_analytics_general_settings_cb', 'referrer_analytics' );
  add_settings_section( 'referrer_analytics_referrer_host_settings', __( 'Referrer Host Settings', 'referrer_analytics' ), 'referrer_analytics_referrer_host_settings_cb', 'referrer_analytics' );
  add_settings_section( 'referrer_analytics_ga_settings', __( 'Google Analytics Settings', 'referrer_analytics' ), 'referrer_analytics_ga_settings_cb', 'referrer_analytics' );

  // Redirect with UTM
  add_settings_field( 'redirect_with_utm', __( 'Redirect with UTM Data', 'referrer_analytics' ), 'referrer_analytics_field_cb', 'referrer_analytics', 'referrer_analytics_ga_settings', [
    'label_for' => 'redirect_with_utm',
    'type'      => 'checkbox',
    'multi'     => false,
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
    'desc'      => 'If a user comes from a host that\'s not defined above, track it using the raw data.<br />Referrer Analytics will attempt to set \'Host\', \'Name\' & \'Type\' from it\'s list of known hosts.<br />If unable to locate, \'Host\' and \'Name\' will be the hostname of the referrer and \'Type\' will default to "referral".',
    'options'   => [
      'enabled' => __( 'Enabled', 'referrer_analytics' )
    ]
  ]);

  // Attempt to get referrer from URL
  add_settings_field( 'url_referrer_fallback', __( 'Use URL Referrer Fallback', 'referrer_analytics' ), 'referrer_analytics_field_cb', 'referrer_analytics', 'referrer_analytics_referrer_host_settings', [
    'label_for' => 'url_referrer_fallback',
    'type'      => 'checkbox',
    'multi'     => false,
    'desc'      => 'If <code>$_SERVER[\'HTTP_REFERER\']</code> is unavailable (see the <a href="https://wordpress.org/plugins/referrer-analytics/" target="_blank" rel="noopener noreferrer">plugin FAQ</a> for more information), attempt to get the referrer from URL parameters.',
    'options'   => [
      'enabled' => __( 'Enabled', 'referrer_analytics' )
    ]
  ]);

  if ( 'enabled' == $options['url_referrer_fallback'] ) {
    // URL parameter for referrer fallack
    add_settings_field( 'referrer_fallback_param', __( 'Referrer Fallback Parameter', 'referrer_analytics' ), 'referrer_analytics_field_cb', 'referrer_analytics', 'referrer_analytics_referrer_host_settings', [
      'label_for'   => 'referrer_fallback_param',
      'type'        => 'text',
      'class'       => 'regular-text',
      'desc'        => 'The URL parameter that should be used as the referrer fallback if unable to retrieve with <code>$_SERVER[\'HTTP_REFERER\']</code>.',
      'placeholder' => 'e.g. utm_source'
    ]);
  }

  // Store Cookies
  add_settings_field( 'store_cookies', __( 'Store Cookies', 'referrer_analytics' ), 'referrer_analytics_field_cb', 'referrer_analytics', 'referrer_analytics_general_settings', [
    'label_for' => 'store_cookies',
    'type'      => 'checkbox',
    'multi'     => false,
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
    'desc'      => 'Enables logging of user referrers and provides an admin interface to view statistics.',
    'options'   => [
      'enabled' => __( 'Enabled', 'referrer_analytics' )
    ]
  ]);

  // Disable rel=noreferrer
  add_settings_field( 'disable_noreferrer', __( 'Disable <code>rel="noreferrer"</code>', 'referrer_analytics' ), 'referrer_analytics_field_cb', 'referrer_analytics', 'referrer_analytics_general_settings', [
    'label_for' => 'disable_noreferrer',
    'type'      => 'checkbox',
    'multi'     => false,
    'desc'      => 'Allows external link destinations to retrieve the <code>$_SERVER[\'HTTP_REFERER\']</code> by disabling WordPress from automatcially adding the <code>rel="noreferrer"</code> tag when enabled.',
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
  $options = referrer_analytics_options();
  $key     = 0;
  ?>
  <div class="referrer-analytics-referrer-header">
    <div>
      <label><?php _e( 'Host', 'referrer_analytics' ); ?></label>
      <small><?php _e( 'The host name of the referrer (i.e. www.google.com).', 'referrer_analytics' ); ?></small>
    </div>
    <div>
      <label><?php _e( 'Type', 'referrer_analytics' ); ?></label>
      <small><?php _e( 'Define a type for the referrer (i.e. organic, referral, etc.).', 'referrer_analytics' ); ?></small>
    </div>
    <div>
      <label><?php _e( 'Name', 'referrer_analytics' ); ?></label>
      <small><?php _e( 'Readable name for the referrer (i.e. Google, Bing, etc.).', 'referrer_analytics' ); ?></small>
    </div>
    <div>
      <label><?php _e( 'Primary URL', 'referrer_analytics' ); ?></label>
      <small><?php _e( 'Primary URL for the referrer', 'referrer_analytics' ); ?></small>
    </div>
  </div>
  <?php
  $cnt = 0;
  if ( $options['hosts'] ):
    foreach( $options['hosts'] as $key => $host ):
      if ( empty( $host['host'] ) ) {
        continue;
      }
      ?>
      <div class="referrer-analytics-referrer-option">
        <input
          type="text"
          name="referrer_analytics_options[hosts][<?php echo $cnt; ?>][host]"
          value="<?php echo trim( $host['host'] ); ?>"
          placeholder="<?php _e( 'Host (i.e. www.google.com)', 'referrer_analytics' ); ?>"
          class="referrer-analytics-input"
        />

        <input
          type="text"
          name="referrer_analytics_options[hosts][<?php echo $cnt; ?>][type]"
          value="<?php echo trim( $host['type'] ); ?>"
          placeholder="<?php _e( 'Type (i.e. organic)', 'referrer_analytics' ); ?>"
          class="referrer-analytics-input"
        />

        <input
          type="text"
          name="referrer_analytics_options[hosts][<?php echo $cnt; ?>][name]"
          value="<?php echo trim( $host['name'] ); ?>"
          placeholder="<?php _e( 'Name (i.e. Google)', 'referrer_analytics' ); ?>"
          class="referrer-analytics-input"
        />

        <input
          type="url"
          name="referrer_analytics_options[hosts][<?php echo $cnt; ?>][primary_url]"
          value="<?php echo trim( $host['primary_url'] ); ?>"
          placeholder="<?php _e( 'Name (i.e. https://www.google.com)', 'referrer_analytics' ); ?>"
          class="referrer-analytics-input"
        />
      </div>
      <?php
      $cnt++;
    endforeach;
  endif;
  ?>
  <div class="referrer-analytics-referrer-option">
    <input
      type="text"
      name="referrer_analytics_options[hosts][<?php echo $cnt; ?>][host]"
      value=""
      placeholder="<?php _e( 'Host (i.e. www.google.com)', 'referrer_analytics' ); ?>"
      class="referrer-analytics-input"
    />

    <input
      type="text"
      name="referrer_analytics_options[hosts][<?php echo $cnt; ?>][type]"
      value=""
      placeholder="<?php _e( 'Type (i.e. organic)', 'referrer_analytics' ); ?>"
      class="referrer-analytics-input"
    />

    <input
      type="text"
      name="referrer_analytics_options[hosts][<?php echo $cnt; ?>][name]"
      value=""
      placeholder="<?php _e( 'Name (i.e. Google)', 'referrer_analytics' ); ?>"
      class="referrer-analytics-input"
    />

    <input
      type="url"
      name="referrer_analytics_options[hosts][<?php echo $cnt; ?>][primary_url]"
      value=""
      placeholder="<?php _e( 'Name (i.e. https://www.google.com)', 'referrer_analytics' ); ?>"
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
      <input class="<?php echo $args['class']; ?>" type="<?php echo $args['type']; ?>" value="<?php if ( ! empty( $options[ $args['label_for'] ] ) ): echo esc_attr( $options[ $args['label_for'] ] ); endif; ?>" placeholder="<?php if( ! empty( $args['placeholder'] ) ): ?><?php echo $args['placeholder']; ?><?php endif; ?>" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="referrer_analytics_options[<?php echo esc_attr( $args['label_for'] ); ?>]"><?php if ( ! empty( $args['suffix'] ) ): echo ' ' . $args['suffix']; endif; ?>
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
            <?php if ( ! empty( $args['class'] ) ): ?>class="<?php echo $args['class']; ?>"<?php endif; ?>
            id="<?php echo esc_attr( $args['label_for'] . $key ); ?>"
            name="referrer_analytics_options[<?php echo esc_attr( $args['label_for'] ); ?>]<?php if( $args['multi'] ): ?>[<?php echo $key; ?>]<?php endif; ?>" value="<?php echo $key; ?>"
            <?php if( $args['multi'] && $key === $options[ $args['label_for'] ][ $key ] || ! $args['multi'] && $key === $options[ $args['label_for'] ] ): ?> checked="checked"<?php endif; ?> /> <?php echo $label; ?>
        </label>
      <?php endforeach; ?>
      <p class="description"><?php echo $args['desc'] ?></p>
      <?php
    break;
  }
  ?>
  <?php
}
